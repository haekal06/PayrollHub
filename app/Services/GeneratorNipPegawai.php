<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use LogicException;

final class GeneratorNipPegawai
{
    public function berikutnya(): string
    {
        return DB::transaction(
            function (): string {
                $urutan = DB::table(
                    'urutan_nip_pegawais'
                )
                    ->where(
                        'nama',
                        'nip_pegawai'
                    )
                    ->lockForUpdate()
                    ->first();

                if ($urutan === null) {
                    throw new LogicException(
                        'Urutan NIP pegawai belum tersedia.'
                    );
                }

                $nomorBerikutnya =
                    (int) $urutan->nomor_terakhir + 1;

                DB::table('urutan_nip_pegawais')
                    ->where(
                        'nama',
                        'nip_pegawai'
                    )
                    ->update([
                        'nomor_terakhir' =>
                        $nomorBerikutnya,

                        'updated_at' => now(),
                    ]);

                return 'KRY-' . str_pad(
                    (string) $nomorBerikutnya,
                    3,
                    '0',
                    STR_PAD_LEFT
                );
            }
        );
    }
}
