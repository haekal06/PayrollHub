<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Pegawai;
use Illuminate\Validation\Rule;

final class ProsesPenggajianRequest extends RequestAdmin
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'bonus' => $this->input('bonus') ?: 0,

            'potongan_lain' =>
            $this->input('potongan_lain') ?: 0,
        ]);
    }

    public function rules(): array
    {
        return [
            'pegawai_id' => [
                'required',
                'integer',
                Rule::exists('pegawais', 'id')
                    ->where(
                        'status_kepegawaian',
                        Pegawai::STATUS_AKTIF
                    ),
            ],

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

            'bonus' => [
                'required',
                'numeric',
                'min:0',
            ],

            'catatan_bonus' => [
                Rule::requiredIf(
                    fn(): bool =>
                    (float) $this->input(
                        'bonus',
                        0
                    ) > 0
                ),
                'nullable',
                'string',
                'max:500',
            ],

            'potongan_lain' => [
                'required',
                'numeric',
                'min:0',
            ],

            'catatan_potongan' => [
                Rule::requiredIf(
                    fn(): bool =>
                    (float) $this->input(
                        'potongan_lain',
                        0
                    ) > 0
                ),
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required' =>
            'Pegawai wajib dipilih.',

            'pegawai_id.exists' =>
            'Pegawai tidak ditemukan atau sudah tidak aktif.',

            'bulan.required' =>
            'Bulan penggajian wajib dipilih.',

            'bulan.between' =>
            'Bulan penggajian tidak valid.',

            'tahun.required' =>
            'Tahun penggajian wajib diisi.',

            'tahun.between' =>
            'Tahun penggajian tidak valid.',

            'bonus.numeric' =>
            'Bonus harus berupa angka.',

            'bonus.min' =>
            'Bonus tidak boleh negatif.',

            'catatan_bonus.required' =>
            'Keterangan bonus wajib diisi jika terdapat bonus.',

            'catatan_bonus.max' =>
            'Keterangan bonus maksimal 500 karakter.',

            'potongan_lain.numeric' =>
            'Potongan lain harus berupa angka.',

            'potongan_lain.min' =>
            'Potongan lain tidak boleh negatif.',

            'catatan_potongan.required' =>
            'Keterangan potongan wajib diisi jika terdapat potongan lain.',

            'catatan_potongan.max' =>
            'Keterangan potongan maksimal 500 karakter.',
        ];
    }
}
