<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Calibri, Arial, sans-serif;
            font-size: 11pt;
            color: #1e293b;
        }
        table {
            border-collapse: collapse;
        }
        .title-main {
            font-size: 16pt;
            font-weight: bold;
            color: #0369a1;
        }
        .title-sub {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
        }
        .meta {
            font-size: 10pt;
            color: #475569;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 8px;
            border: 1px solid #94a3b8;
        }
        th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border: 1px solid #64748b;
        }
        td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
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
        .bg-subtotal {
            background-color: #f1f5f9;
            font-weight: bold;
        }
        .bg-profit {
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
        }
        .bg-loss {
            background-color: #fee2e2;
            color: #991b1b;
            font-weight: bold;
        }
        .num-rp {
            mso-number-format: "\#\,\#\#0";
        }
    </style>
</head>
<body>

    <!-- KOP DOKUMEN -->
    <table>
        <tr>
            <td colspan="6" class="title-main" style="border: none;">DEPOT AIR MINUM SULTHAN AIR REBUS</td>
        </tr>
        <tr>
            <td colspan="6" class="meta" style="border: none;">Perumahan Griya Savana Suka Makmur Blok AD No 5, Pekanbaru, Riau</td>
        </tr>
        <tr>
            <td colspan="6" class="title-sub" style="border: none; padding-top: 8px;">LAPORAN KEUANGAN &amp; REKAPITULASI OPERASIONAL</td>
        </tr>
        <tr>
            <td colspan="6" class="meta" style="border: none;">Periode: {{ $periodeLabel }}</td>
        </tr>
        <tr>
            <td colspan="6" class="meta" style="border: none;">Waktu Cetak: {{ $printedAt }}</td>
        </tr>
        <tr><td colspan="6" style="border: none;"></td></tr>
    </table>

    <!-- 1. RINGKASAN KEUANGAN -->
    <table>
        <tr>
            <td colspan="4" class="section-title">I. RINGKASAN KEUANGAN DEPOT</td>
        </tr>
        <tr>
            <th style="width: 260px;">Komponen Keuangan</th>
            <th style="width: 160px; text-align: right;">Nominal (Rp)</th>
            <th colspan="2" style="width: 340px;">Keterangan</th>
        </tr>
        <tr>
            <td class="font-bold">Total Omzet Penjualan</td>
            <td class="text-right font-bold num-rp" style="color: #0369a1;">{{ $summary['revenue'] }}</td>
            <td colspan="2">Total pemasukan transaksi lunas</td>
        </tr>
        <tr>
            <td style="padding-left: 24px;">• Kemasan &amp; Produksi</td>
            <td class="text-right num-rp">{{ $summary['breakdown_expenses']['kemasan_produksi'] ?? 0 }}</td>
            <td colspan="2">Tutup galon segel, galon baru, tisu</td>
        </tr>
        <tr>
            <td style="padding-left: 24px;">• Operasional &amp; Logistik</td>
            <td class="text-right num-rp">{{ $summary['breakdown_expenses']['operasional_logistik'] ?? 0 }}</td>
            <td colspan="2">Bensin kurir, servis, ganti filter &amp; lampu UV</td>
        </tr>
        <tr>
            <td style="padding-left: 24px;">• Beban Tetap</td>
            <td class="text-right num-rp">{{ $summary['breakdown_expenses']['beban_tetap'] ?? 0 }}</td>
            <td colspan="2">Listrik depot, air baku tangki, sewa tempat</td>
        </tr>
        <tr class="bg-subtotal">
            <td>Total Pengeluaran Operasional</td>
            <td class="text-right num-rp" style="color: #b91c1c;">{{ $summary['expenses'] }}</td>
            <td colspan="2">Total seluruh pengeluaran operasional</td>
        </tr>
        <tr class="{{ $summary['is_profit'] ? 'bg-profit' : 'bg-loss' }}">
            <td style="font-size: 12pt;">LABA BERSIH DEPOT</td>
            <td class="text-right num-rp" style="font-size: 12pt;">{{ $summary['net_profit'] }}</td>
            <td colspan="2">{{ $summary['is_profit'] ? 'UNTUNG / LABA' : 'RUGI / DEFISIT' }}</td>
        </tr>
        <tr>
            <td class="font-bold">Total Galon Terjual</td>
            <td class="text-right font-bold">{{ number_format($summary['total_gallons'], 0, ',', '.') }} Galon</td>
            <td colspan="2">Total transaksi lunas: {{ number_format($summary['total_orders'], 0, ',', '.') }} transaksi</td>
        </tr>
        <tr><td colspan="6" style="border: none;"></td></tr>
    </table>

    <!-- 2. REKAPITULASI PENJUALAN HARIAN -->
    <table>
        <tr>
            <td colspan="4" class="section-title">II. REKAPITULASI PENJUALAN HARIAN</td>
        </tr>
        <tr>
            <th style="width: 180px;">Tanggal</th>
            <th style="width: 150px; text-align: center;">Jumlah Transaksi</th>
            <th style="width: 150px; text-align: center;">Volume Galon</th>
            <th style="width: 180px; text-align: right;">Omzet Harian (Rp)</th>
        </tr>
        @forelse ($summary['daily_trend'] as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}</td>
                <td class="text-center">{{ $day['orders_count'] }} Transaksi</td>
                <td class="text-center">{{ $day['total_gallons'] }} Galon</td>
                <td class="text-right font-bold num-rp">{{ $day['revenue'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Belum ada data transaksi pada rentang tanggal ini.</td>
            </tr>
        @endforelse
        <tr><td colspan="6" style="border: none;"></td></tr>
    </table>

    <!-- 3. RINCIAN PENGELUARAN OPERASIONAL -->
    <table>
        <tr>
            <td colspan="5" class="section-title">III. RINCIAN PENGELUARAN OPERASIONAL</td>
        </tr>
        <tr>
            <th style="width: 120px;">Tanggal</th>
            <th style="width: 180px;">Kategori</th>
            <th style="width: 220px;">Judul Pengeluaran</th>
            <th style="width: 240px;">Catatan / Keterangan</th>
            <th style="width: 150px; text-align: right;">Nominal (Rp)</th>
        </tr>
        @forelse ($expenses as $exp)
            <tr>
                <td>{{ $exp->expense_date->format('d/m/Y') }}</td>
                <td>
                    @if ($exp->category === 'kemasan_produksi')
                        Kemasan &amp; Produksi
                    @elseif ($exp->category === 'operasional_logistik')
                        Operasional &amp; Logistik
                    @else
                        Beban Tetap
                    @endif
                </td>
                <td class="font-bold">{{ $exp->title }}</td>
                <td>{{ $exp->description ?: '-' }}</td>
                <td class="text-right font-bold num-rp">{{ $exp->amount }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada catatan pengeluaran pada periode ini.</td>
            </tr>
        @endforelse
        <tr class="bg-subtotal">
            <td colspan="4" class="text-right">TOTAL PENGELUARAN:</td>
            <td class="text-right num-rp">{{ $summary['expenses'] }}</td>
        </tr>
        <tr><td colspan="6" style="border: none;"></td></tr>
    </table>

    <!-- 4. DAFTAR TRANSAKSI PENJUALAN (LUNAS) -->
    <table>
        <tr>
            <td colspan="6" class="section-title">IV. DAFTAR TRANSAKSI PENJUALAN (LUNAS)</td>
        </tr>
        <tr>
            <th style="width: 110px;">No. Order</th>
            <th style="width: 150px;">Waktu Transaksi</th>
            <th style="width: 140px;">Tipe Layanan</th>
            <th style="width: 200px;">Nama Pelanggan</th>
            <th style="width: 120px; text-align: center;">Metode Bayar</th>
            <th style="width: 150px; text-align: right;">Total Tagihan (Rp)</th>
        </tr>
        @forelse ($orders as $ord)
            <tr>
                <td class="font-bold">SW-{{ str_pad($ord->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $ord->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $ord->order_type === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Pesan Antar' }}</td>
                <td>{{ $ord->customer->name ?? 'Pelanggan Umum' }}</td>
                <td class="text-center">{{ strtoupper($ord->payment_method) }}</td>
                <td class="text-right font-bold num-rp">{{ $ord->total_amount }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada transaksi penjualan lunas pada periode ini.</td>
            </tr>
        @endforelse
        <tr class="bg-subtotal">
            <td colspan="5" class="text-right">TOTAL OMZET PENJUALAN:</td>
            <td class="text-right num-rp">{{ $summary['revenue'] }}</td>
        </tr>
    </table>

</body>
</html>