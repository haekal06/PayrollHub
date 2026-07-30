<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Absensi;
use App\Models\Pegawai;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class SimpanAbsensiMassalRequest extends RequestAdmin
{
    protected function prepareForValidation(): void
    {
        $data = collect(
            (array) $this->input('data', [])
        )
            ->map(function (array $baris): array {
                $baris['jam_lembur'] =
                    $baris['jam_lembur'] ?? 0;

                return $baris;
            })
            ->all();

        $this->merge([
            'data' => $data,
        ]);
    }

    public function rules(): array
    {
        return [
            'tanggal_absensi' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'data' => [
                'required',
                'array',
                'min:1',
            ],

            'data.*.pegawai_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('pegawais', 'id')
                    ->where(
                        'status_kepegawaian',
                        Pegawai::STATUS_AKTIF
                    ),
            ],

            'data.*.status' => [
                'required',
                Rule::in([
                    Absensi::STATUS_HADIR,
                    Absensi::STATUS_SAKIT,
                    Absensi::STATUS_IZIN,
                    Absensi::STATUS_CUTI,
                    Absensi::STATUS_ALPA,
                ]),
            ],

            'data.*.jam_lembur' => [
                'required',
                'numeric',
                'min:0',
                'max:12',
                'multiple_of:0.5',
            ],

            'data.*.catatan_lembur' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'data.*.catatan' => [
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
                foreach (
                    (array) $this->input(
                        'data',
                        []
                    ) as $index => $baris
                ) {
                    $status = (string) (
                        $baris['status'] ?? ''
                    );

                    $jamLembur = (float) (
                        $baris['jam_lembur'] ?? 0
                    );

                    $catatanLembur = trim(
                        (string) (
                            $baris['catatan_lembur']
                            ?? ''
                        )
                    );

                    if (
                        $status !==
                        Absensi::STATUS_HADIR
                        && $jamLembur > 0
                    ) {
                        $validator->errors()->add(
                            "data.$index.jam_lembur",
                            'Lembur hanya dapat diisi untuk pegawai yang hadir.'
                        );
                    }

                    if (
                        $jamLembur > 0
                        && $catatanLembur === ''
                    ) {
                        $validator->errors()->add(
                            "data.$index.catatan_lembur",
                            'Keterangan lembur wajib diisi.'
                        );
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_absensi.required' =>
            'Tanggal absensi wajib dipilih.',

            'tanggal_absensi.date' =>
            'Tanggal absensi tidak valid.',

            'tanggal_absensi.before_or_equal' =>
            'Tanggal absensi tidak boleh melebihi hari ini.',

            'data.required' =>
            'Data pegawai wajib tersedia.',

            'data.min' =>
            'Tidak ada data pegawai yang dapat disimpan.',

            'data.*.pegawai_id.distinct' =>
            'Pegawai tercatat lebih dari satu kali.',

            'data.*.pegawai_id.exists' =>
            'Pegawai tidak ditemukan atau sudah tidak aktif.',

            'data.*.status.required' =>
            'Status absensi wajib dipilih.',

            'data.*.status.in' =>
            'Status absensi tidak valid.',

            'data.*.jam_lembur.numeric' =>
            'Jam lembur harus berupa angka.',

            'data.*.jam_lembur.min' =>
            'Jam lembur tidak boleh negatif.',

            'data.*.jam_lembur.max' =>
            'Jam lembur maksimal 12 jam.',

            'data.*.jam_lembur.multiple_of' =>
            'Jam lembur harus menggunakan kelipatan 0,5 jam.',

            'data.*.catatan_lembur.max' =>
            'Keterangan lembur maksimal 1.000 karakter.',

            'data.*.catatan.max' =>
            'Catatan maksimal 1.000 karakter.',
        ];
    }
}
