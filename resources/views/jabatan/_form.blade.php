<div class="form-group">
    <label for="kode">Kode Jabatan</label>

    <input
        id="kode"
        name="kode"
        type="text"
        maxlength="20"
        value="{{ old('kode', $jabatan->kode ?? '') }}"
        placeholder="Contoh: JBT-001"
        required>

    @error('kode')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama">Nama Jabatan</label>

    <input
        id="nama"
        name="nama"
        type="text"
        maxlength="100"
        value="{{ old('nama', $jabatan->nama ?? '') }}"
        placeholder="Contoh: Staff Administrasi"
        required>

    @error('nama')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="gaji_pokok">Gaji Pokok</label>

    <input
        id="gaji_pokok"
        name="gaji_pokok"
        type="number"
        min="0"
        step="0.01"
        value="{{ old(
            'gaji_pokok',
            $jabatan->gaji_pokok ?? 0
        ) }}"
        required>

    @error('gaji_pokok')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="tunjangan">Tunjangan</label>

    <input
        id="tunjangan"
        name="tunjangan"
        type="number"
        min="0"
        step="0.01"
        value="{{ old(
            'tunjangan',
            $jabatan->tunjangan ?? 0
        ) }}"
        required>

    @error('tunjangan')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="tarif_lembur_per_jam">
        Tarif Lembur per Jam
    </label>

    <input
        id="tarif_lembur_per_jam"
        name="tarif_lembur_per_jam"
        type="number"
        min="0"
        step="0.01"
        value="{{ old(
            'tarif_lembur_per_jam',
            $jabatan->tarif_lembur_per_jam ?? 0
        ) }}"
        required>

    <p class="muted">
        Tarif ini digunakan otomatis saat menghitung
        upah lembur pegawai pada jabatan tersebut.
    </p>

    @error('tarif_lembur_per_jam')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <input type="hidden" name="aktif" value="0">

    <label style="
        display: flex;
        gap: 9px;
        align-items: center;
        font-weight: normal;
    ">
        <input
            name="aktif"
            type="checkbox"
            value="1"
            @checked(
            old( 'aktif' ,
            isset($jabatan)
            ? $jabatan->aktif
        : true
        )
        )>

        <span>Jabatan aktif</span>
    </label>

    @error('aktif')
    <div class="error">{{ $message }}</div>
    @enderror
</div>