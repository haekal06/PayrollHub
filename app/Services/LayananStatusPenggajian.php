<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Penggajian;
use App\Models\RiwayatStatusPenggajian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use LogicException;

final class LayananStatusPenggajian
{
    public function finalisasi(
        Penggajian $penggajian,
        User $pelaku
    ): Penggajian {
        return DB::transaction(
            function () use (
                $penggajian,
                $pelaku
            ): Penggajian {
                $this->pastikanAdmin($pelaku);

                $data = $this->kunciPenggajian(
                    $penggajian
                );

                if (
                    ! in_array(
                        $data->status,
                        [
                            Penggajian::STATUS_DRAF,
                            Penggajian::STATUS_REVISI,
                        ],
                        true
                    )
                ) {
                    throw new LogicException(
                        'Hanya penggajian draf atau revisi yang dapat difinalisasi.'
                    );
                }

                $statusAsal = $data->status;
                $snapshot =
                    $data->attributesToArray();

                $data->update([
                    'status' =>
                    Penggajian::STATUS_FINAL,

                    'difinalisasi_oleh' =>
                    $pelaku->id,

                    'difinalisasi_pada' => now(),
                    'dibayar_oleh' => null,
                    'dibayar_pada' => null,
                ]);

                $this->catatRiwayat(
                    penggajian: $data,
                    statusAsal: $statusAsal,
                    statusTujuan: Penggajian::STATUS_FINAL,

                    alasan: $statusAsal ===
                        Penggajian::STATUS_REVISI
                        ? 'Penggajian selesai direvisi dan difinalisasi kembali.'
                        : 'Penggajian difinalisasi.',

                    snapshot: $snapshot,
                    pelaku: $pelaku,
                );

                return $data->refresh();
            }
        );
    }

    public function bukaUntukRevisi(
        Penggajian $penggajian,
        User $pelaku,
        string $alasan
    ): Penggajian {
        return DB::transaction(
            function () use (
                $penggajian,
                $pelaku,
                $alasan
            ): Penggajian {
                $this->pastikanAdmin($pelaku);

                $data = $this->kunciPenggajian(
                    $penggajian
                );

                if (
                    $data->status !==
                    Penggajian::STATUS_FINAL
                ) {
                    throw new LogicException(
                        'Hanya penggajian final yang dapat dibuka untuk revisi.'
                    );
                }

                $alasan = trim($alasan);

                if (mb_strlen($alasan) < 10) {
                    throw new LogicException(
                        'Alasan revisi minimal 10 karakter.'
                    );
                }

                $snapshot =
                    $data->attributesToArray();

                $data->update([
                    'status' =>
                    Penggajian::STATUS_REVISI,

                    'difinalisasi_oleh' => null,
                    'difinalisasi_pada' => null,
                ]);

                $this->catatRiwayat(
                    penggajian: $data,
                    statusAsal: Penggajian::STATUS_FINAL,

                    statusTujuan: Penggajian::STATUS_REVISI,

                    alasan: $alasan,
                    snapshot: $snapshot,
                    pelaku: $pelaku,
                );

                return $data->refresh();
            }
        );
    }

    public function tandaiDibayar(
        Penggajian $penggajian,
        User $pelaku,
        ?string $catatan = null
    ): Penggajian {
        return DB::transaction(
            function () use (
                $penggajian,
                $pelaku,
                $catatan
            ): Penggajian {
                $this->pastikanAdmin($pelaku);

                $data = $this->kunciPenggajian(
                    $penggajian
                );

                if (
                    $data->status !==
                    Penggajian::STATUS_FINAL
                ) {
                    throw new LogicException(
                        'Hanya penggajian final yang dapat ditandai sudah dibayar.'
                    );
                }

                $snapshot =
                    $data->attributesToArray();

                $data->update([
                    'status' =>
                    Penggajian::STATUS_DIBAYAR,

                    'dibayar_oleh' =>
                    $pelaku->id,

                    'dibayar_pada' => now(),
                ]);

                $catatan = trim(
                    (string) $catatan
                );

                $this->catatRiwayat(
                    penggajian: $data,
                    statusAsal: Penggajian::STATUS_FINAL,

                    statusTujuan: Penggajian::STATUS_DIBAYAR,

                    alasan: $catatan !== ''
                        ? $catatan
                        : 'Penggajian ditandai sudah dibayar.',

                    snapshot: $snapshot,
                    pelaku: $pelaku,
                );

                return $data->refresh();
            }
        );
    }

    private function kunciPenggajian(
        Penggajian $penggajian
    ): Penggajian {
        return Penggajian::query()
            ->lockForUpdate()
            ->findOrFail($penggajian->id);
    }

    private function pastikanAdmin(
        User $pelaku
    ): void {
        if (! $pelaku->adalahAdmin()) {
            throw new LogicException(
                'Perubahan status penggajian hanya dapat dilakukan oleh Admin HRD.'
            );
        }
    }

    /**
     * @param array<string, mixed> $snapshot
     */
    private function catatRiwayat(
        Penggajian $penggajian,
        ?string $statusAsal,
        string $statusTujuan,
        ?string $alasan,
        array $snapshot,
        User $pelaku
    ): void {
        RiwayatStatusPenggajian::query()
            ->create([
                'penggajian_id' =>
                $penggajian->id,

                'status_asal' => $statusAsal,
                'status_tujuan' => $statusTujuan,
                'alasan' => $alasan,
                'snapshot' => $snapshot,
                'diubah_oleh' => $pelaku->id,
                'diubah_pada' => now(),
            ]);
    }
}
