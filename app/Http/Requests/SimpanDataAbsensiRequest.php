<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Absensi;
use App\Models\Pegawai;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SimpanDataAbsensiRequest extends RequestAdmin
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'jam_lembur' =>
            $this->input('jam_lembur') ?: 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'pegawai_id' => [
                'required',
                'integer',
                'exists:pegawais,id',
            ],

            'tanggal_absensi' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'status' => [
                'required',
                Rule::in([
                    Absensi::STATUS_HADIR,
                    Absensi::STATUS_SAKIT,
                    Absensi::STATUS_IZIN,
                    Absensi::STATUS_CUTI,
                    Absensi::STATUS_ALPA,
                ]),
            ],

            'jam_lembur' => [
                'required',
                'numeric',
                'min:0',
                'max:12',
                'multiple_of:0.5',
            ],

            'catatan_lembur' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'catatan' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $validator->errors()->has('pegawai_id')
                    || $validator->errors()->has(
                        'tanggal_absensi'
                    )
                ) {
                    return;
                }

                $absensiSekarang =
                    $this->route('absensi');

                $query = Absensi::query()
                    ->where(
                        'pegawai_id',
                        $this->integer('pegawai_id')
                    )
                    ->whereDate(
                        'tanggal_absensi',
                        (string) $this->input(
                            'tanggal_absensi'
                        )
                    );

                if (
                    $absensiSekarang instanceof Absensi
                ) {
                    $query->where(
                        'id',
                        '!=',
                        $absensiSekarang->id
                    );
                }

                if ($query->exists()) {
                    $validator->errors()->add(
                        'tanggal_absensi',
                        'Absensi pegawai pada tanggal tersebut sudah tercatat.'
                    );
                }

                $pegawai = Pegawai::query()->find(
                    $this->integer('pegawai_id')
                );

                if (
                    $pegawai !== null
                    && $pegawai->tanggal_masuk
                    ->isAfter(
                        (string) $this->input(
                            'tanggal_absensi'
                        )
                    )
                ) {
                    $validator->errors()->add(
                        'tanggal_absensi',
                        'Tanggal absensi tidak boleh lebih awal dari tanggal masuk pegawai.'
                    );
                }
            },

            function (Validator $validator): void {
                if (
                    $validator->errors()->has('status')
                    || $validator->errors()->has(
                        'jam_lembur'
                    )
                ) {
                    return;
                }

                $status = (string) $this->input(
                    'status'
                );

                $jamLembur = (float) $this->input(
                    'jam_lembur',
                    0
                );

                if (
                    $status !== Absensi::STATUS_HADIR
                    && $jamLembur > 0
                ) {
                    $validator->errors()->add(
                        'jam_lembur',
                        'Jam lembur hanya dapat diisi untuk pegawai yang hadir.'
                    );
                }

                if (
                    $jamLembur > 0
                    && blank(
                        $this->input(
                            'catatan_lembur'
                        )
                    )
                ) {
                    $validator->errors()->add(
                        'catatan_lembur',
                        'Keterangan lembur wajib diisi jika terdapat lembur.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required' =>
            'Pegawai wajib dipilih.',

            'pegawai_id.exists' =>
            'Pegawai tidak ditemukan.',

            'tanggal_absensi.required' =>
            'Tanggal absensi wajib diisi.',

            'tanggal_absensi.date' =>
            'Tanggal absensi tidak valid.',

            'tanggal_absensi.before_or_equal' =>
            'Tanggal absensi tidak boleh melebihi hari ini.',

            'status.required' =>
            'Status absensi wajib dipilih.',

            'status.in' =>
            'Status absensi tidak valid.',

            'jam_lembur.numeric' =>
            'Jam lembur harus berupa angka.',

            'jam_lembur.min' =>
            'Jam lembur tidak boleh negatif.',

            'jam_lembur.max' =>
            'Jam lembur maksimal 12 jam per hari.',

            'jam_lembur.multiple_of' =>
            'Jam lembur harus menggunakan kelipatan 0,5 jam.',

            'catatan_lembur.max' =>
            'Keterangan lembur maksimal 1.000 karakter.',

            'catatan.max' =>
            'Catatan absensi maksimal 1.000 karakter.',
        ];
    }
}
