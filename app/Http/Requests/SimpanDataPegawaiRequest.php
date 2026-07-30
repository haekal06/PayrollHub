<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Pegawai;
use Illuminate\Validation\Rule;

final class SimpanDataPegawaiRequest extends RequestAdmin
{
    public function rules(): array
    {
        $aturanJabatan = Rule::exists(
            'jabatans',
            'id'
        );

        /*
         * Pegawai baru hanya boleh memakai jabatan aktif.
         * Saat mengubah data, jabatan lama tetap dapat dibaca.
         */
        if (
            ! $this->route('pegawai')
                instanceof Pegawai
        ) {
            $aturanJabatan->where(
                'aktif',
                true
            );
        }

        return [
            'jabatan_id' => [
                'required',
                'integer',
                $aturanJabatan,
            ],

            'nama' => [
                'required',
                'string',
                'max:100',
            ],

            'jenis_kelamin' => [
                'required',
                Rule::in([
                    Pegawai::JENIS_KELAMIN_LAKI_LAKI,
                    Pegawai::JENIS_KELAMIN_PEREMPUAN,
                ]),
            ],

            'telepon' => [
                'nullable',
                'string',
                'max:20',
            ],

            'alamat' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'tanggal_masuk' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'status_kepegawaian' => [
                'required',
                Rule::in([
                    Pegawai::STATUS_AKTIF,
                    Pegawai::STATUS_TIDAK_AKTIF,
                    Pegawai::STATUS_MENGUNDURKAN_DIRI,
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'jabatan_id.required' =>
            'Jabatan wajib dipilih.',

            'jabatan_id.exists' =>
            'Jabatan tidak ditemukan atau sudah tidak aktif.',

            'nama.required' =>
            'Nama pegawai wajib diisi.',

            'nama.max' =>
            'Nama pegawai maksimal 100 karakter.',

            'jenis_kelamin.required' =>
            'Jenis kelamin wajib dipilih.',

            'jenis_kelamin.in' =>
            'Jenis kelamin tidak valid.',

            'telepon.max' =>
            'Nomor telepon maksimal 20 karakter.',

            'alamat.max' =>
            'Alamat maksimal 2.000 karakter.',

            'tanggal_masuk.required' =>
            'Tanggal masuk wajib diisi.',

            'tanggal_masuk.date' =>
            'Tanggal masuk tidak valid.',

            'tanggal_masuk.before_or_equal' =>
            'Tanggal masuk tidak boleh melebihi hari ini.',

            'status_kepegawaian.required' =>
            'Status pegawai wajib dipilih.',

            'status_kepegawaian.in' =>
            'Status pegawai tidak valid.',
        ];
    }
}
