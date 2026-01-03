<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faktur Penjualan - {{ $order->nomor_pesanan }}</title>
    {{-- 
        PENTING: PDF Generator (seperti DomPDF) tidak selalu mendukung link eksternal. 
        CSS Bootstrap sebaiknya di-inline atau di-compile di sini jika PDF Generator mendukungnya.
        Untuk kesederhanaan, kita gunakan CSS minimal yang meniru struktur: 
    --}}
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
        }
        .info-box {
            width: 45%;
            float: left;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .info-box.right {
            float: right;
            text-align: right;
        }
        .info-box p {
            margin: 2px 0;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-items th, .table-items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table-items th {
            background-color: #f2f2f2;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }
        .notes {
            margin-top: 30px;
            font-size: 11px;
            color: #555;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>FAKTUR PENJUALAN</h1>
            <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}</p>
        </div>

        <div class="clearfix">
            {{-- Detail Pelanggan (Kiri) --}}
            <div class="info-box">
                <p><strong>Pelanggan:</strong> {{ $order->nama_pelanggan }}</p>
                <p><strong>Telepon:</strong> {{ $order->telepon_pelanggan }}</p>
                @if ($order->alamat_pelanggan)
                    <p><strong>Alamat:</strong> {{ $order->alamat_pelanggan }}</p>
                @endif
            </div>

            {{-- Detail Pesanan (Kanan) --}}
            <div class="info-box right">
                <p><strong>No Pesanan:</strong> {{ $order->nomor_pesanan }}</p>
                <p><strong>Tanggal:</strong> {{ $order->tanggal_pemesanan->format('d F Y') }}</p>
                <p><strong>Status:</strong> {{ $order->status }}</p>
            </div>
        </div>

        {{-- Tabel Item Pesanan --}}
        <table class="table-items">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->details as $item)
                    <tr>
                        <td>{{ $item->produk->kode_produk }}</td>
                        <td>{{ $item->produk->nama_produk }}</td>
                        <td>{{ $item->jumlah }}</td>
                        <td>{{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->sub_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total Pembayaran --}}
        <div class="total-section">
            TOTAL: IDR {{ number_format($order->total_pembayaran, 0, ',', '.') }}
        </div>

        {{-- Catatan --}}
        @if ($order->catatan)
        <div class="notes">
            <p><strong>Catatan:</strong> {{ $order->catatan }}</p>
        </div>
        @endif

    </div>

</body>
</html>