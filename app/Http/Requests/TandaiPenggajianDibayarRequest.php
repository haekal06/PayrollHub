<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class TandaiPenggajianDibayarRequest extends RequestAdmin
{
    public function rules(): array
    {
        return [
            'catatan_pembayaran' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'catatan_pembayaran.max' =>
            'Catatan pembayaran maksimal 500 karakter.',
        ];
    }
}
