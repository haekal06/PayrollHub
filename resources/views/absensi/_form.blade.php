@php
$pegawaiTerpilih = old(
'pegawai_id',
$absensi->pegawai_id
?? request('pegawai_id', '')
);

$tanggalTerpilih = old(
'tanggal_absensi',
isset($absensi)
? $absensi->tanggal_absensi->format('Y-m-d')
: request(
'tanggal_absensi',
request('tanggal', now()->format('Y-m-d'))
)
);

$statusTerpilih = old(
'status',
$absensi->status ?? 'hadir'
);

$jamLemburTerpilih = old(
'jam_lembur',
$absensi->jam_lembur ?? 0
);
@endphp

<div class="form-group">
    <label for="pegawai_id">Pegawai</label>

    <select
        id="pegawai_id"
        name="pegawai_id"
        required>
        <option value="">Pilih Pegawai</option>

        @foreach ($daftarPegawai as $pegawai)
        <option
            value="{{ $pegawai->id }}"
            @selected(
            $pegawaiTerpilih==$pegawai->id
            )>
            {{ $pegawai->nip }} -
            {{ $pegawai->nama }}
            ({{ $pegawai->jabatan->nama }})
        </option>
        @endforeach
    </select>

    @error('pegawai_id')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="tanggal_absensi">
        Tanggal Absensi
    </label>

    <input
        id="tanggal_absensi"
        name="tanggal_absensi"
        type="date"
        value="{{ $tanggalTerpilih }}"
        max="{{ now()->format('Y-m-d') }}"
        required>

    <p class="muted">
        Tanggal harus tersedia sebagai hari kerja
        pada Kalender Kerja.
    </p>

    @error('tanggal_absensi')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="status">Status Absensi</label>

    <select
        id="status"
        name="status"
        required>
        <option
            value="hadir"
            @selected($statusTerpilih==='hadir' )>
            Hadir
        </option>

        <option
            value="sakit"
            @selected($statusTerpilih==='sakit' )>
            Sakit
        </option>

        <option
            value="izin"
            @selected($statusTerpilih==='izin' )>
            Izin
        </option>

        <option
            value="cuti"
            @selected($statusTerpilih==='cuti' )>
            Cuti
        </option>

        <option
            value="alpa"
            @selected($statusTerpilih==='alpa' )>
            Alpa
        </option>
    </select>

    @error('status')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div
    id="bagianLembur"
    style="
        padding: 18px;
        margin-bottom: 18px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fffafa;
    ">
    <h3 style="margin-top: 0;">Data Lembur</h3>

    <div class="form-group">
        <label for="jam_lembur">
            Jumlah Jam Lembur
        </label>

        <input
            id="jam_lembur"
            name="jam_lembur"
            type="number"
            min="0"
            max="12"
            step="0.5"
            value="{{ $jamLemburTerpilih }}"
            required>

        <p class="muted">
            Isi menggunakan kelipatan 0,5 jam.
            Contoh: 1, 1.5, atau 2.
        </p>

        @error('jam_lembur')
        <div class="error">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="catatan_lembur">
            Keterangan Lembur
        </label>

        <textarea
            id="catatan_lembur"
            name="catatan_lembur"
            maxlength="1000"
            rows="3"
            placeholder="Contoh: Menyelesaikan laporan bulanan">{{ old(
                'catatan_lembur',
                $absensi->catatan_lembur ?? ''
            ) }}</textarea>

        <p class="muted">
            Wajib diisi jika terdapat jam lembur.
        </p>

        @error('catatan_lembur')
        <div class="error">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="form-group">
    <label for="catatan">Catatan Absensi</label>

    <textarea
        id="catatan"
        name="catatan"
        maxlength="1000"
        rows="4"
        placeholder="Keterangan tambahan, opsional">{{ old(
            'catatan',
            $absensi->catatan ?? ''
        ) }}</textarea>

    @error('catatan')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

@push('scripts')
<script>
    const pilihanStatus =
        document.getElementById('status');

    const inputJamLembur =
        document.getElementById('jam_lembur');

    const inputCatatanLembur =
        document.getElementById('catatan_lembur');

    const bagianLembur =
        document.getElementById('bagianLembur');

    function perbaruiBagianLembur() {
        const hadir =
            pilihanStatus.value === 'hadir';

        bagianLembur.style.opacity =
            hadir ? '1' : '0.55';

        inputJamLembur.readOnly = !hadir;
        inputCatatanLembur.readOnly = !hadir;

        if (!hadir) {
            inputJamLembur.value = '0';
            inputCatatanLembur.value = '';
        }
    }

    pilihanStatus.addEventListener(
        'change',
        perbaruiBagianLembur
    );

    perbaruiBagianLembur();
</script>
@endpush