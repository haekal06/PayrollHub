<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class BuatKalenderKerjaRequest extends RequestAdmin
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
        ];
    }

    public function messages(): array
    {
        return [
            'bulan.required' =>
            'Bulan wajib dipilih.',

            'bulan.between' =>
            'Bulan tidak valid.',

            'tahun.required' =>
            'Tahun wajib diisi.',

            'tahun.between' =>
            'Tahun tidak valid.',
        ];
    }
}
