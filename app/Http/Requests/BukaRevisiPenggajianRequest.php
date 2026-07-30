<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class BukaRevisiPenggajianRequest extends RequestAdmin
{
    public function rules(): array
    {
        return [
            'alasan_revisi' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'alasan_revisi.required' =>
            'Alasan revisi wajib diisi.',

            'alasan_revisi.min' =>
            'Alasan revisi minimal 10 karakter.',

            'alasan_revisi.max' =>
            'Alasan revisi maksimal 1.000 karakter.',
        ];
    }
}
