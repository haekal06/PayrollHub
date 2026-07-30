<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\KalenderKerja;
use Illuminate\Validation\Rule;

final class PerbaruiKalenderKerjaRequest extends RequestAdmin
{
    public function rules(): array
    {
        return [
            'bulan' => [
                'required',
                'integer',
                'between:1,12',
            ],

            'tahun' => [
                'required',
                'integer',
                'between:2000,2100',
            ],

            'kalender' => [
                'required',
                'array',
                'min:1',
            ],

            'kalender.*.id' => [
                'required',
                'integer',
                'exists:kalender_kerjas,id',
            ],

            'kalender.*.jenis_hari' => [
                'required',
                Rule::in([
                    KalenderKerja::JENIS_HARI_KERJA,
                    KalenderKerja::JENIS_AKHIR_PEKAN,
                    KalenderKerja::JENIS_LIBUR_NASIONAL,
                    KalenderKerja::JENIS_LIBUR_PERUSAHAAN,
                ]),
            ],

            'kalender.*.keterangan' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kalender.required' =>
            'Data kalender wajib tersedia.',

            'kalender.*.id.exists' =>
            'Tanggal kalender tidak ditemukan.',

            'kalender.*.jenis_hari.required' =>
            'Jenis hari wajib dipilih.',

            'kalender.*.jenis_hari.in' =>
            'Jenis hari tidak valid.',

            'kalender.*.keterangan.max' =>
            'Keterangan maksimal 255 karakter.',
        ];
    }
}
