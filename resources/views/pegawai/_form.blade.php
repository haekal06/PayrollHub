@if (isset($pegawai))
<div class="form-group">
    <label for="nip">NIP</label>

    <input
        id="nip"
        type="text"
        value="{{ $pegawai->nip }}"
        readonly>

    <p class="muted">
        NIP tidak dapat diubah.
    </p>
</div>
@else
<div class="alert alert-info">
    NIP akan dibuat otomatis oleh sistem
    setelah data pegawai disimpan.
</div>
@endif

<div class="form-group">
    <label for="jabatan_id">Jabatan</label>

    <select
        id="jabatan_id"
        name="jabatan_id"
        required>
        <option value="">Pilih Jabatan</option>

        @foreach ($daftarJabatan as $jabatan)
        <option
            value="{{ $jabatan->id }}"
            @selected(
            old( 'jabatan_id' ,
            $pegawai->jabatan_id ?? ''
            ) == $jabatan->id
            )>
            {{ $jabatan->kode }} -
            {{ $jabatan->nama }}

            @if (! $jabatan->aktif)
            (Tidak Aktif)
            @endif
        </option>
        @endforeach
    </select>

    @error('jabatan_id')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="nama">Nama Pegawai</label>

    <input
        id="nama"
        name="nama"
        type="text"
        maxlength="100"
        value="{{ old(
            'nama',
            $pegawai->nama ?? ''
        ) }}"
        required>

    @error('nama')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="jenis_kelamin">
        Jenis Kelamin
    </label>

    <select
        id="jenis_kelamin"
        name="jenis_kelamin"
        required>
        <option value="">
            Pilih Jenis Kelamin
        </option>

        <option
            value="laki_laki"
            @selected(
            old( 'jenis_kelamin' ,
            $pegawai->jenis_kelamin ?? ''
            ) === 'laki_laki'
            )>
            Laki-laki
        </option>

        <option
            value="perempuan"
            @selected(
            old( 'jenis_kelamin' ,
            $pegawai->jenis_kelamin ?? ''
            ) === 'perempuan'
            )>
            Perempuan
        </option>
    </select>

    @error('jenis_kelamin')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="telepon">Nomor Telepon</label>

    <input
        id="telepon"
        name="telepon"
        type="text"
        maxlength="20"
        value="{{ old(
            'telepon',
            $pegawai->telepon ?? ''
        ) }}"
        placeholder="Opsional">

    @error('telepon')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="alamat">Alamat</label>

    <textarea
        id="alamat"
        name="alamat"
        maxlength="2000"
        rows="4"
        placeholder="Opsional">{{ old(
            'alamat',
            $pegawai->alamat ?? ''
        ) }}</textarea>

    @error('alamat')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="tanggal_masuk">
        Tanggal Masuk
    </label>

    <input
        id="tanggal_masuk"
        name="tanggal_masuk"
        type="date"
        value="{{ old(
            'tanggal_masuk',
            isset($pegawai)
                ? $pegawai->tanggal_masuk->format('Y-m-d')
                : ''
        ) }}"
        max="{{ now()->format('Y-m-d') }}"
        required>

    @error('tanggal_masuk')
    <div class="error">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="status_kepegawaian">
        Status Pegawai
    </label>

    <select
        id="status_kepegawaian"
        name="status_kepegawaian"
        required>
        <option value="">Pilih Status</option>

        <option
            value="aktif"
            @selected(
            old( 'status_kepegawaian' ,
            $pegawai->status_kepegawaian ?? 'aktif'
            ) === 'aktif'
            )>
            Aktif
        </option>

        <option
            value="tidak_aktif"
            @selected(
            old( 'status_kepegawaian' ,
            $pegawai->status_kepegawaian ?? ''
            ) === 'tidak_aktif'
            )>
            Tidak Aktif
        </option>

        <option
            value="mengundurkan_diri"
            @selected(
            old( 'status_kepegawaian' ,
            $pegawai->status_kepegawaian ?? ''
            ) === 'mengundurkan_diri'
            )>
            Mengundurkan Diri
        </option>
    </select>

    @error('status_kepegawaian')
    <div class="error">{{ $message }}</div>
    @enderror
</div>