<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan Sultan Water</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #333333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18pt;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 9pt;
            color: #64748b;
        }
        .doc-title {
            text-align: center;
            margin-bottom: 20px;
        }
        .doc-title h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
        }
        .doc-title .period {
            font-size: 10pt;
            color: #475569;
            margin-top: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px 10px;
            font-size: 10pt;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            text-align: left;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
        }
        td {
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .highlight-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .profit-badge {
            color: #15803d;
            font-weight: bold;
        }
        .loss-badge {
            color: #b91c1c;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 9pt;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sultan Water POS</h1>
        <p>Depot Air Minum Isi Ulang Higienis & Berkualitas</p>
        <p>Layanan Cepat Galon Bersih Antar & Ambil Sendiri</p>
    </div>

    <div class="doc-title">
        <h2>LAPORAN KEUANGAN & LABA BERSIH</h2>
        <div class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}</div>
    </div>

    <!-- TABEL 1: RINGKASAN OMZET & LABA BERSIH -->
    <table>
        <thead>
            <tr>
                <th>Komponen Laporan</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Total Omzet Penjualan (Revenue / R)</td>
                <td class="text-right font-bold" style="color: #0369a1;">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">- Beban Kemasan & Produksi</td>
                <td class="text-right">Rp {{ number_format($summary['breakdown_expenses']['kemasan_produksi'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">- Beban Operasional & Logistik (Filter/UV & Servis)</td>
                <td class="text-right">Rp {{ number_format($summary['breakdown_expenses']['operasional_logistik'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">- Beban Tetap (Air Baku, Listrik, Sewa)</td>
                <td class="text-right">Rp {{ number_format($summary['breakdown_expenses']['beban_tetap'], 0, ',', '.') }}</td>
            </tr>
            <tr class="highlight-row">
                <td>Total Beban Operasional (Expenses / E)</td>
                <td class="text-right" style="color: #b91c1c;">Rp {{ number_format($summary['expenses'], 0, ',', '.') }}</td>
            </tr>
            <tr class="highlight-row" style="font-size: 11pt; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                <td>LABA BERSIH (NET PROFIT: NP = R - E)</td>
                <td class="text-right {{ $summary['is_profit'] ? 'profit-badge' : 'loss-badge' }}">
                    Rp {{ number_format($summary['net_profit'], 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- RINGKASAN STATISTIK FISIK -->
    <table style="width: 60%; margin-top: 10px;">
        <tr>
            <td style="border: none;">Total Volume Terjual</td>
            <td style="border: none;" class="font-bold text-right">{{ number_format($summary['total_gallons']) }} Galon</td>
        </tr>
        <tr>
            <td style="border: none;">Total Transaksi Lunas</td>
            <td style="border: none;" class="font-bold text-right">{{ number_format($summary['total_orders']) }} Transaksi</td>
        </tr>
    </table>

    <!-- TABEL 2: RINCIAN PENGELUARAN (JIKA ADA) -->
    <h3 style="font-size: 11pt; margin-top: 25px; margin-bottom: 8px; color: #0f172a;">Rincian Pengeluaran Operasional</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Tanggal</th>
                <th style="width: 25%;">Kategori</th>
                <th>Keterangan / Judul</th>
                <th class="text-right" style="width: 25%;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($expenses as $expense)
                <tr>
                    <td>{{ $expense->expense_date->format('d/m/Y') }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $expense->category)) }}</td>
                    <td>
                        <span class="font-bold">{{ $expense->title }}</span>
                        @if ($expense->description)
                            <div style="font-size: 8.5pt; color: #64748b;">{{ $expense->description }}</div>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="color: #94a3b8; font-style: italic;">
                        Tidak ada catatan pengeluaran pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ $printedAt }} | Sistem POS Depot Sultan Water
    </div>

</body>
</html> 