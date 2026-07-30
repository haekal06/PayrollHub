<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class PratinjauImportAbsensiRequest extends RequestAdmin
{
    public function rules(): array
    {
        return [
            'file_absensi' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file_absensi.required' =>
            'File absensi wajib dipilih.',

            'file_absensi.file' =>
            'Upload absensi tidak valid.',

            'file_absensi.mimes' =>
            'File harus berformat XLSX, XLS, atau CSV.',

            'file_absensi.max' =>
            'Ukuran file maksimal 10 MB.',
        ];
    }
}
