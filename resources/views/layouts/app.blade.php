<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', config('app.name', 'PayrollHub'))
    </title>

    <style>
        :root {
            --utama: #b91c1c;
            --utama-gelap: #991b1b;
            --utama-sangat-gelap: #7f1d1d;
            --utama-muda: #fee2e2;
            --utama-lembut: #fff1f2;
            --utama-border: #fecaca;

            --putih: #ffffff;
            --latar: #fffafa;
            --teks: #1f2937;
            --redup: #6b7280;
            --border: #e5e7eb;

            --sukses: #166534;
            --sukses-muda: #dcfce7;
            --peringatan: #854d0e;
            --peringatan-muda: #fef9c3;
            --informasi: #1e40af;
            --informasi-muda: #dbeafe;

            --lebar-sidebar: 280px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--teks);
            background: var(--latar);
            font-family: Arial, Helvetica, sans-serif;
        }

        a {
            color: inherit;
        }

        /*
        |--------------------------------------------------------------------------
        | Sidebar
        |--------------------------------------------------------------------------
        */

        .sidebar {
            position: fixed;
            z-index: 1000;
            top: 0;
            bottom: 0;
            left: 0;

            display: flex;
            width: var(--lebar-sidebar);
            overflow-y: auto;
            flex-direction: column;

            color: var(--putih);
            background:
                linear-gradient(180deg,
                    var(--utama) 0%,
                    var(--utama-sangat-gelap) 100%);

            box-shadow: 4px 0 18px rgb(127 29 29 / 18%);
            transition: transform 0.25s ease;
        }

        .sidebar-brand {
            padding: 28px 24px 24px;
            border-bottom: 1px solid rgb(255 255 255 / 18%);
        }

        .sidebar-brand h1 {
            margin: 0;
            font-size: 30px;
            letter-spacing: 0.3px;
        }

        .sidebar-brand p {
            margin: 8px 0 0;
            color: rgb(255 255 255 / 75%);
            font-size: 14px;
        }

        .sidebar-navigation {
            display: flex;
            flex-direction: column;
            gap: 7px;
            padding: 22px 16px;
        }

        .sidebar-label {
            margin: 4px 10px 8px;
            color: rgb(255 255 255 / 62%);
            font-size: 12px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            gap: 12px;
            align-items: center;

            padding: 13px 14px;
            border-radius: 9px;

            color: rgb(255 255 255 / 90%);
            font-weight: 500;
            text-decoration: none;

            transition:
                color 0.2s ease,
                background 0.2s ease,
                transform 0.2s ease;
        }

        .nav-link:hover {
            color: var(--putih);
            background: rgb(255 255 255 / 14%);
            transform: translateX(3px);
        }

        .nav-link-active {
            color: var(--utama-sangat-gelap);
            background: var(--putih);
            font-weight: bold;
            box-shadow: 0 4px 12px rgb(0 0 0 / 12%);
        }

        .nav-link-active:hover {
            color: var(--utama-sangat-gelap);
            background: var(--putih);
        }

        .nav-icon {
            display: inline-flex;
            width: 28px;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 18px 16px 22px;
            border-top: 1px solid rgb(255 255 255 / 18%);
        }

        .sidebar-user {
            margin-bottom: 14px;
            padding: 14px;
            border-radius: 9px;
            background: rgb(255 255 255 / 12%);
        }

        .sidebar-user strong {
            display: block;
            margin-bottom: 5px;
        }

        .sidebar-user span {
            color: rgb(255 255 255 / 76%);
            font-size: 13px;
        }

        .logout-form {
            margin: 0;
        }

        .logout-button {
            width: 100%;
            color: var(--utama-gelap);
            background: var(--putih);
            font-weight: bold;
        }

        .logout-button:hover {
            color: var(--putih);
            background: #dc2626;
        }

        /*
        |--------------------------------------------------------------------------
        | Tombol Sidebar
        |--------------------------------------------------------------------------
        */

        .sidebar-toggle {
            position: fixed;
            z-index: 1100;
            top: 20px;
            left: calc(var(--lebar-sidebar) + 16px);

            width: 48px;
            height: 48px;
            border: 0;
            border-radius: 10px;

            color: var(--putih);
            background: var(--utama);
            font-size: 25px;
            cursor: pointer;

            box-shadow: 0 4px 12px rgb(0 0 0 / 18%);

            transition:
                left 0.25s ease,
                background 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: var(--utama-gelap);
        }

        .sidebar-overlay {
            position: fixed;
            z-index: 900;
            inset: 0;
            display: none;
            background: rgb(0 0 0 / 48%);
        }

        .sidebar-overlay-active {
            display: block;
        }

        body.sidebar-hidden .sidebar {
            transform: translateX(-100%);
        }

        body.sidebar-hidden .app-content {
            margin-left: 0;
        }

        body.sidebar-hidden .sidebar-toggle {
            left: 16px;
        }

        /*
        |--------------------------------------------------------------------------
        | Konten Utama
        |--------------------------------------------------------------------------
        */

        .app-content {
            min-height: 100vh;
            margin-left: var(--lebar-sidebar);
            transition: margin-left 0.25s ease;
        }

        .container {
            width: min(1200px, 92%);
            margin: 0 auto;
            padding: 40px 0;
        }

        .page-header {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1,
        .page-header h2 {
            margin-top: 0;
            color: var(--utama-sangat-gelap);
        }

        .muted {
            color: var(--redup);
        }

        /*
        |--------------------------------------------------------------------------
        | Card
        |--------------------------------------------------------------------------
        */

        .card {
            padding: 24px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--putih);
            box-shadow: 0 4px 14px rgb(0 0 0 / 7%);
        }

        .login-card {
            max-width: 440px;
            margin: 70px auto;
            border-top: 6px solid var(--utama);
        }

        .login-card h1,
        .login-card h2 {
            color: var(--utama-sangat-gelap);
        }

        /*
        |--------------------------------------------------------------------------
        | Form
        |--------------------------------------------------------------------------
        */

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="month"],
        input[type="file"],
        select,
        textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            color: var(--teks);
            background: var(--putih);
            font-family: inherit;
            font-size: 15px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--utama);
            outline: 3px solid rgb(185 28 28 / 12%);
        }

        input[readonly],
        input[disabled],
        select[disabled],
        textarea[disabled] {
            color: #4b5563;
            background: #f3f4f6;
            cursor: not-allowed;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 24px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(190px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .error {
            margin-top: 6px;
            color: var(--utama);
            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | Tombol
        |--------------------------------------------------------------------------
        */

        .button {
            display: inline-block;
            padding: 10px 18px;
            border: 0;
            border-radius: 7px;

            color: var(--putih);
            background: var(--utama);

            font-family: inherit;
            font-size: 15px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .button:hover {
            background: var(--utama-gelap);
        }

        .button:active {
            transform: translateY(1px);
        }

        .button-secondary {
            color: var(--teks);
            background: #e5e7eb;
        }

        .button-secondary:hover {
            color: var(--teks);
            background: #d1d5db;
        }

        .button-danger {
            background: #dc2626;
        }

        .button-danger:hover {
            background: var(--utama-gelap);
        }

        .button-success {
            background: #15803d;
        }

        .button-success:hover {
            background: #166534;
        }

        .button-warning {
            color: #422006;
            background: #facc15;
        }

        .button-warning:hover {
            color: #422006;
            background: #eab308;
        }

        .button-small {
            padding: 7px 12px;
            font-size: 14px;
        }

        /*
        |--------------------------------------------------------------------------
        | Alert
        |--------------------------------------------------------------------------
        */

        .alert {
            margin-bottom: 20px;
            padding: 13px 16px;
            border-radius: 7px;
        }

        .alert-success {
            color: var(--sukses);
            background: var(--sukses-muda);
        }

        .alert-error {
            color: var(--utama-gelap);
            background: var(--utama-muda);
        }

        .alert-warning {
            color: var(--peringatan);
            background: var(--peringatan-muda);
        }

        .alert-info {
            color: var(--informasi);
            background: var(--informasi-muda);
        }

        /*
        |--------------------------------------------------------------------------
        | Tabel
        |--------------------------------------------------------------------------
        */

        .table-wrapper {
            margin-bottom: 24px;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: var(--putih);
            box-shadow: 0 3px 10px rgb(0 0 0 / 5%);
        }

        table,
        .table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td,
        .table th,
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        table th,
        .table th {
            color: var(--utama-sangat-gelap);
            background: var(--utama-lembut);
            white-space: nowrap;
        }

        table tbody tr:hover,
        .table tbody tr:hover {
            background: #fff7f7;
        }

        table tbody tr:last-child td,
        .table tbody tr:last-child td {
            border-bottom: 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-active,
        .status-aktif {
            color: var(--sukses);
            font-weight: bold;
        }

        .status-inactive,
        .status-tidak-aktif {
            color: var(--utama-gelap);
            font-weight: bold;
        }

        .status-hadir {
            color: var(--sukses);
            background: var(--sukses-muda);
        }

        .status-sakit {
            color: var(--peringatan);
            background: var(--peringatan-muda);
        }

        .status-izin {
            color: var(--informasi);
            background: var(--informasi-muda);
        }

        .status-cuti {
            color: #6b21a8;
            background: #f3e8ff;
        }

        .status-alpa {
            color: var(--utama-gelap);
            background: var(--utama-muda);
        }

        .status-draf,
        .status-revisi {
            color: var(--peringatan);
            background: var(--peringatan-muda);
        }

        .status-final {
            color: var(--informasi);
            background: var(--informasi-muda);
        }

        .status-dibayar {
            color: var(--sukses);
            background: var(--sukses-muda);
        }

        .status-kosong,
        .status-terkunci {
            color: #374151;
            background: #e5e7eb;
        }

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        .dashboard-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(230px, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .dashboard-card {
            display: block;
            padding: 22px;

            border: 1px solid var(--utama-border);
            border-left: 5px solid var(--utama);
            border-radius: 11px;

            color: var(--utama-sangat-gelap);
            background: var(--putih);
            text-decoration: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;
        }

        .dashboard-card:hover {
            border-color: var(--utama);
            box-shadow: 0 5px 14px rgb(127 29 29 / 12%);
            transform: translateY(-2px);
        }

        .dashboard-card h3 {
            margin: 0 0 10px;
        }

        .dashboard-card p {
            margin-bottom: 0;
            color: var(--redup);
            line-height: 1.5;
        }

        /*
        |--------------------------------------------------------------------------
        | Absensi dan Ringkasan
        |--------------------------------------------------------------------------
        */

        .attendance-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 24px;
        }

        .tab-link {
            padding: 10px 16px;
            border: 1px solid transparent;
            border-radius: 7px;
            color: var(--teks);
            background: #e5e7eb;
            text-decoration: none;
        }

        .tab-link:hover {
            color: var(--utama-gelap);
            border-color: var(--utama-border);
            background: var(--utama-lembut);
        }

        .tab-link-active {
            color: var(--putih);
            border-color: var(--utama);
            background: var(--utama);
        }

        .tab-link-active:hover {
            color: var(--putih);
            background: var(--utama-gelap);
        }

        .summary-strip {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(150px, 1fr));
            gap: 14px;
            margin: 20px 0;
        }

        .summary-item {
            padding: 18px;
            border: 1px solid var(--utama-border);
            border-radius: 9px;
            background: var(--putih);
        }

        .summary-item strong {
            display: block;
            margin-top: 8px;
            color: var(--utama-sangat-gelap);
            font-size: 26px;
        }

        /*
        |--------------------------------------------------------------------------
        | Cetak
        |--------------------------------------------------------------------------
        */

        .print-only {
            display: none;
        }

        .report-header {
            margin-bottom: 24px;
            text-align: center;
        }

        .report-header h1,
        .report-header h2,
        .report-header p {
            margin: 5px 0;
        }

        .signature-section {
            width: 280px;
            margin-top: 50px;
            margin-left: auto;
            text-align: center;
        }

        .signature-space {
            height: 70px;
        }

        /*
        |--------------------------------------------------------------------------
        | Responsif
        |--------------------------------------------------------------------------
        */

        @media (max-width: 900px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.sidebar-open {
                transform: translateX(0);
            }

            .sidebar-toggle,
            body.sidebar-hidden .sidebar-toggle {
                top: 14px;
                left: 14px;
            }

            .app-content {
                margin-left: 0;
            }

            .container {
                width: 94%;
                padding-top: 78px;
            }
        }

        @media (max-width: 600px) {
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .page-header .button {
                width: 100%;
            }

            .card {
                padding: 18px;
            }

            .login-card {
                margin: 30px auto;
            }

            .attendance-tabs {
                display: grid;
                grid-template-columns: 1fr;
            }

            .tab-link {
                text-align: center;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Print
        |--------------------------------------------------------------------------
        */

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm;
            }

            body {
                color: #000000;
                background: var(--putih);
            }

            .sidebar,
            .sidebar-overlay,
            .sidebar-toggle,
            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .app-content {
                margin-left: 0;
            }

            .container {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            .card,
            .table-wrapper {
                overflow: visible;
                border: 0;
                box-shadow: none;
            }

            table {
                font-size: 12px;
            }

            table th,
            table td {
                padding: 7px;
            }

            .status-badge {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        .pagination-nav {
            display: flex;
            gap: 16px;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 14px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: white;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }

        .pagination-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }

        .pagination-button {
            display: inline-flex;
            min-width: 38px;
            min-height: 38px;
            justify-content: center;
            align-items: center;
            padding: 7px 11px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            color: #991b1b;
            background: white;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
        }

        .pagination-button:hover {
            border-color: #b91c1c;
            color: white;
            background: #b91c1c;
        }

        .pagination-active {
            border-color: #b91c1c;
            color: white;
            background: #b91c1c;
        }

        .pagination-disabled {
            color: #9ca3af;
            background: #f3f4f6;
            cursor: not-allowed;
        }

        .pagination-disabled:hover {
            border-color: #d1d5db;
            color: #9ca3af;
            background: #f3f4f6;
        }

        @media (max-width: 700px) {
            .pagination-nav {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    @auth
    <button
        class="sidebar-toggle no-print"
        id="sidebarToggle"
        type="button"
        aria-label="Tampilkan atau sembunyikan menu"
        aria-controls="sidebar"
        aria-expanded="true">
        <span id="sidebarToggleIcon">‹</span>
    </button>

    <aside
        class="sidebar no-print"
        id="sidebar">
        <div class="sidebar-brand">
            <h1>
                {{ config('app.name', 'PayrollHub') }}
            </h1>

            <p>Sistem Pengelolaan Payroll</p>
        </div>

        <nav class="sidebar-navigation">
            <div class="sidebar-label">
                Menu Utama
            </div>

            <a
                href="{{ route('dashboard') }}"
                class="nav-link
                        {{ request()->routeIs('dashboard')
                            ? 'nav-link-active'
                            : '' }}">
                <span class="nav-icon">⌂</span>
                <span>Dashboard</span>
            </a>

            @if (auth()->user()->adalahAdmin())
            <a
                href="{{ route('admin.jabatan.index') }}"
                class="nav-link
                            {{ request()->routeIs('admin.jabatan.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">▣</span>
                <span>Jabatan</span>
            </a>

            <a
                href="{{ route('admin.pegawai.index') }}"
                class="nav-link
                            {{ request()->routeIs('admin.pegawai.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">♟</span>
                <span>Pegawai</span>
            </a>

            <a
                href="{{ route('admin.kalender-kerja.index') }}"
                class="nav-link
                            {{ request()->routeIs('admin.kalender-kerja.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">▦</span>
                <span>Kalender Kerja</span>
            </a>

            <a
                href="{{ route('admin.absensi.index') }}"
                class="nav-link
                            {{
                                request()->routeIs('admin.absensi.*')
                                || request()->routeIs('admin.import-absensi.*')
                                    ? 'nav-link-active'
                                    : ''
                            }}">
                <span class="nav-icon">✓</span>
                <span>Absensi</span>
            </a>

            <a
                href="{{ route('admin.penggajian.index') }}"
                class="nav-link
                            {{ request()->routeIs('admin.penggajian.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">Rp</span>
                <span>Penggajian</span>
            </a>

            <a
                href="{{ route('admin.pengguna.index') }}"
                class="nav-link
                            {{ request()->routeIs('admin.pengguna.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">⚙</span>
                <span>Pengguna</span>
            </a>
            @else
            <a
                href="{{ route('pegawai.slip-gaji.index') }}"
                class="nav-link
                            {{ request()->routeIs('pegawai.slip-gaji.*')
                                ? 'nav-link-active'
                                : '' }}">
                <span class="nav-icon">Rp</span>
                <span>Slip Gaji Saya</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <strong>
                    {{ auth()->user()->nama }}
                </strong>

                <span>
                    {{ auth()->user()->adalahAdmin()
                            ? 'Admin HRD'
                            : 'Pegawai' }}
                </span>
            </div>

            <form
                class="logout-form"
                method="POST"
                action="{{ route('logout') }}">
                @csrf

                <button
                    class="button logout-button"
                    type="submit">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <div
        class="sidebar-overlay no-print"
        id="sidebarOverlay"></div>
    @endauth

    <main class="@auth app-content @endauth">
        <div class="container">
            @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
            @endif

            @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
            @endif

            @if (session('warning'))
            <div class="alert alert-warning">
                {{ session('warning') }}
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    @auth
    <script>
        const sidebar =
            document.getElementById('sidebar');

        const sidebarOverlay =
            document.getElementById('sidebarOverlay');

        const sidebarToggle =
            document.getElementById('sidebarToggle');

        const sidebarToggleIcon =
            document.getElementById('sidebarToggleIcon');

        const mobileBreakpoint = 900;

        function layarKecil() {
            return window.innerWidth <= mobileBreakpoint;
        }

        function perbaruiTombolDesktop() {
            const sidebarDisembunyikan =
                document.body.classList.contains(
                    'sidebar-hidden'
                );

            sidebarToggle.setAttribute(
                'aria-expanded',
                sidebarDisembunyikan ?
                'false' :
                'true'
            );

            sidebarToggleIcon.textContent =
                sidebarDisembunyikan ?
                '›' :
                '‹';
        }

        function bukaSidebarMobile() {
            document.body.classList.remove(
                'sidebar-hidden'
            );

            sidebar.classList.add('sidebar-open');

            sidebarOverlay.classList.add(
                'sidebar-overlay-active'
            );

            sidebarToggle.setAttribute(
                'aria-expanded',
                'true'
            );

            sidebarToggleIcon.textContent = '×';
        }

        function tutupSidebarMobile() {
            sidebar.classList.remove('sidebar-open');

            sidebarOverlay.classList.remove(
                'sidebar-overlay-active'
            );

            sidebarToggle.setAttribute(
                'aria-expanded',
                'false'
            );

            sidebarToggleIcon.textContent = '☰';
        }

        function ubahSidebarDesktop() {
            document.body.classList.toggle(
                'sidebar-hidden'
            );

            const sidebarDisembunyikan =
                document.body.classList.contains(
                    'sidebar-hidden'
                );

            localStorage.setItem(
                'payrollhub-sidebar-hidden',
                sidebarDisembunyikan ?
                'true' :
                'false'
            );

            perbaruiTombolDesktop();
        }

        function pulihkanSidebarDesktop() {
            const sebelumnyaDisembunyikan =
                localStorage.getItem(
                    'payrollhub-sidebar-hidden'
                ) === 'true';

            document.body.classList.toggle(
                'sidebar-hidden',
                sebelumnyaDisembunyikan
            );

            perbaruiTombolDesktop();
        }

        sidebarToggle.addEventListener(
            'click',
            function() {
                if (layarKecil()) {
                    if (
                        sidebar.classList.contains(
                            'sidebar-open'
                        )
                    ) {
                        tutupSidebarMobile();
                    } else {
                        bukaSidebarMobile();
                    }

                    return;
                }

                ubahSidebarDesktop();
            }
        );

        sidebarOverlay.addEventListener(
            'click',
            tutupSidebarMobile
        );

        window.addEventListener(
            'resize',
            function() {
                if (layarKecil()) {
                    document.body.classList.remove(
                        'sidebar-hidden'
                    );

                    tutupSidebarMobile();
                } else {
                    tutupSidebarMobile();
                    pulihkanSidebarDesktop();
                }
            }
        );

        if (layarKecil()) {
            tutupSidebarMobile();
        } else {
            pulihkanSidebarDesktop();
        }
    </script>
    @endauth

    @stack('scripts')
</body>

</html>