@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Dashboard</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Produk</h6>
                            <h2 class="mb-0">{{ $totalProducts }}</h2>
                        </div>
                        <i class="bi bi-box-seam fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Total Pesanan</h6>
                            <h2 class="mb-0">{{ $totalOrders }}</h2>
                        </div>
                        <i class="bi bi-cart-check fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Stok Rendah</h6>
                            <h2 class="mb-0">{{ $lowStock }}</h2>
                        </div>
                        <i class="bi bi-exclamation-triangle fs-1"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Pending Retur</h6>
                            <h2 class="mb-0">{{ $pendingReturns }}</h2>
                        </div>
                        <i class="bi bi-arrow-return-left fs-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Tabel -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Stok Rendah</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Stok</th>
                                    <th>Min</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lowStockProducts as $p)
                                    <tr>
                                        <td>{{ $p->kode_produk }}</td>
                                        <td>{{ $p->nama_produk }}</td>
                                        <td><span class="badge bg-warning">{{ $p->stok_produk }}</span></td>
                                        <td>{{ $p->min_stok }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Semua stok aman</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pesanan Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>No Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingOrders as $order)
                                    <tr>
                                        <td><strong>{{ $order->nomor_pesanan }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($order->tanggal_pemesanan)->format('d M Y') }}</td>
                                        <td>{{ $order->nama_pelanggan }}</td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-hourglass-split"></i> {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                                                data-bs-target="#modalDetail{{ $order->id }}">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle fs-2 d-block mb-2"></i>
                                            Tidak ada pesanan tertunda saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MODAL DIPINDAHKAN KE SINI ================= --}}
    @foreach ($orders as $o)
        <div class="modal fade" id="modalDetail{{ $o->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 bg-light">
                        <h5 class="modal-title fw-bold">
                            Detail Pesanan #{{ $o->nomor_pesanan }}
                        </h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-6">
                                <label class="text-muted small d-block">Pelanggan</label>
                                <h6 class="fw-bold mb-0">{{ $o->nama_pelanggan }}</h6>
                                <span class="text-muted">{{ $o->telepon_pelanggan }}</span>
                            </div>
                            <div class="col-6 text-end">
                                <label class="text-muted small d-block">Tanggal Transaksi</label>
                                <h6 class="fw-bold">
                                    {{ $o->tanggal_pemesanan->format('d F Y H:i') }}
                                </h6>
                            </div>
                        </div>

                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Produk</th>
                                    <th class="text-center" style="width:80px;">Qty</th>
                                    <th class="text-end">Harga</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($o->details as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                {{ $item->produk->nama_produk ?? 'Produk Tidak Ditemukan' }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $item->produk->kode_produk ?? '-' }}
                                            </small>
                                        </td>
                                        <td class="text-center">{{ $item->jumlah }}</td>
                                        <td class="text-end">
                                            Rp {{ number_format($item->harga, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            Rp {{ number_format($item->sub_total, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-end fw-bold py-3">
                                        Total Pembayaran
                                    </td>
                                    <td class="text-end fw-bold text-primary py-3 fs-5">
                                        Rp {{ number_format($o->total_pembayaran, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        @if ($o->catatan)
                            <div class="mt-3 p-3 bg-light rounded border-start border-primary border-4">
                                <label class="text-muted small d-block">Catatan Pesanan:</label>
                                <p class="mb-0 italic">{{ $o->catatan }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer border-0">
                        <button class="btn btn-secondary px-4" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
