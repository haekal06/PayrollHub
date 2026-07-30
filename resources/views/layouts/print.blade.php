<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'Dokumen PayrollHub')
    </title>

    <style>
        :root {
            --merah: #991b1b;
            --merah-gelap: #7f1d1d;
            --merah-muda: #fee2e2;
            --abu: #4b5563;
            --border: #d1d5db;
            --latar: #f3f4f6;
            --putih: #ffffff;
            --hijau: #166534;
            --hijau-muda: #dcfce7;
        }

        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        body {
            margin: 0;
            color: #111827;
            background: var(--latar);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .print-toolbar {
            position: sticky;
            z-index: 100;
            top: 0;

            display: flex;
            gap: 10px;
            justify-content: center;

            padding: 14px;
            border-bottom: 1px solid var(--border);
            background: var(--putih);
            box-shadow: 0 3px 12px rgb(0 0 0 / 10%);
        }

        .print-button {
            display: inline-block;
            padding: 10px 18px;
            border: 0;
            border-radius: 6px;

            color: var(--putih);
            background: var(--merah);

            font-family: inherit;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
        }

        .print-button:hover {
            background: var(--merah-gelap);
        }

        .print-button-secondary {
            color: #111827;
            background: #e5e7eb;
        }

        .print-button-secondary:hover {
            background: #d1d5db;
        }

        .print-document {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 14mm;

            background: var(--putih);
            box-shadow: 0 5px 22px rgb(0 0 0 / 14%);
        }

        .document-header {
            display: flex;
            gap: 20px;
            justify-content: space-between;
            align-items: flex-start;

            padding-bottom: 14px;
            margin-bottom: 18px;
            border-bottom: 3px solid var(--merah);
        }

        .document-brand h1 {
            margin: 0;
            color: var(--merah);
            font-size: 26px;
            letter-spacing: 0.4px;
        }

        .document-brand p {
            margin: 4px 0 0;
            color: var(--abu);
        }

        .document-title {
            text-align: right;
        }

        .document-title h2 {
            margin: 0;
            color: var(--merah-gelap);
            font-size: 20px;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 5px 0 0;
            color: var(--abu);
        }

        .document-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
            margin-bottom: 18px;
            border: 1px solid var(--border);
        }

        .meta-item {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 8px;
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
        }

        .meta-item:nth-child(odd) {
            border-right: 1px solid var(--border);
        }

        .meta-item:nth-last-child(-n + 2) {
            border-bottom: 0;
        }

        .meta-label {
            color: var(--abu);
            font-weight: bold;
        }

        .document-section {
            margin-bottom: 18px;
        }

        .document-section-avoid {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .section-title {
            padding: 7px 10px;
            margin: 0 0 8px;

            border-left: 4px solid var(--merah);
            color: var(--merah-gelap);
            background: #fff7f7;

            font-size: 14px;
            text-transform: uppercase;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            border: 1px solid var(--border);
        }

        .summary-item {
            padding: 8px 5px;
            border-right: 1px solid var(--border);
            text-align: center;
        }

        .summary-item:last-child {
            border-right: 0;
        }

        .summary-item span {
            display: block;
            margin-bottom: 4px;
            color: var(--abu);
            font-size: 10px;
        }

        .summary-item strong {
            color: var(--merah-gelap);
            font-size: 16px;
        }

        .finance-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        th,
        td {
            padding: 7px 8px;
            border: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--merah-gelap);
            background: #fff1f2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .money-table td:last-child,
        .money-table th:last-child {
            text-align: right;
            white-space: nowrap;
        }

        .table-total {
            font-weight: bold;
            background: #f9fafb;
        }

        .net-salary {
            display: flex;
            justify-content: space-between;
            align-items: center;

            padding: 13px 16px;
            margin-bottom: 18px;

            border: 1px solid #86efac;
            color: var(--hijau);
            background: var(--hijau-muda);
        }

        .net-salary span {
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .net-salary strong {
            font-size: 23px;
        }

        .status {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-hadir {
            color: #166534;
            background: #dcfce7;
        }

        .status-sakit {
            color: #854d0e;
            background: #fef9c3;
        }

        .status-izin {
            color: #1e40af;
            background: #dbeafe;
        }

        .status-cuti {
            color: #6b21a8;
            background: #f3e8ff;
        }

        .status-alpa {
            color: #991b1b;
            background: #fee2e2;
        }

        .status-final {
            color: #1e40af;
            background: #dbeafe;
        }

        .status-dibayar {
            color: #166534;
            background: #dcfce7;
        }

        .document-note {
            color: var(--abu);
            font-size: 10px;
        }

        .signature-area {
            display: grid;
            grid-template-columns: 1fr 250px;
            gap: 30px;
            margin-top: 28px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .signature-box {
            text-align: center;
        }

        .signature-space {
            height: 58px;
        }

        .signature-line {
            padding-top: 5px;
            border-top: 1px solid #111827;
            font-weight: bold;
        }

        .document-footer {
            padding-top: 10px;
            margin-top: 24px;
            border-top: 1px solid var(--border);
            color: var(--abu);
            font-size: 9px;
            text-align: center;
        }

        @media print {
            body {
                background: var(--putih);
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .print-toolbar {
                display: none !important;
            }

            .print-document {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            a {
                color: inherit;
                text-decoration: none;
            }
        }

        @media screen and (max-width: 900px) {
            .print-document {
                width: calc(100% - 24px);
                min-height: auto;
                margin: 12px;
                padding: 18px;
            }

            .document-header,
            .finance-grid {
                grid-template-columns: 1fr;
            }

            .document-header {
                display: block;
            }

            .document-title {
                margin-top: 16px;
                text-align: left;
            }

            .document-meta {
                grid-template-columns: 1fr;
            }

            .meta-item:nth-child(odd) {
                border-right: 0;
            }

            .meta-item:nth-last-child(-n + 2) {
                border-bottom: 1px solid var(--border);
            }

            .meta-item:last-child {
                border-bottom: 0;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .summary-item {
                border-bottom: 1px solid var(--border);
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="print-toolbar">
        <button
            class="print-button"
            type="button"
            onclick="window.print()">
            Cetak Dokumen
        </button>

        <button
            class="print-button print-button-secondary"
            type="button"
            onclick="window.close()">
            Tutup
        </button>
    </div>

    <main class="print-document">
        @yield('content')
    </main>
</body>

</html>