@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark">Manajemen Pesanan</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('orders.create') }}" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-circle"></i> Buat Pesanan
                </a>
            </div>
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Filter --}}
        {{-- Filter Card --}}
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('orders.index') }}">
                    <div class="row g-3">
                        {{-- Pencarian Nama/Nomor --}}
                        <div class="col-md-4">
                            <label class="form-label small text-muted">Cari Pesanan</label>
                            <input type="text" class="form-control" name="search" placeholder="Nomor atau nama..."
                                value="{{ request('search') }}">
                        </div>

                        {{-- Filter Satu Tanggal --}}
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Tanggal</label>
                            <input type="date" class="form-control" name="date" value="{{ request('date') }}">
                        </div>

                        {{-- Filter Status --}}
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Status</label>
                            <select class="form-select" name="status">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Status
                                </option>
                                <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed
                                </option>
                                <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled
                                </option>
                            </select>
                        </div>

                        {{-- Tombol Filter & Reset --}}
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="bi bi-filter">Filter</i>
                            </button>
                            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">No Pesanan</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $o)
                                @php
                                    $badgeColor =
                                        $o->status === 'COMPLETED'
                                            ? 'bg-success'
                                            : ($o->status === 'PENDING'
                                                ? 'bg-warning text-dark'
                                                : 'bg-danger');
                                @endphp
                                <tr>
                                    <td class="ps-4"><strong>{{ $o->nomor_pesanan }}</strong></td>
                                    <td>{{ $o->tanggal_pemesanan->format('j F Y') }}</td>
                                    <td>{{ $o->nama_pelanggan }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $badgeColor }}">{{ $o->status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal"
                                            data-bs-target="#modalDetail{{ $o->id }}">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <a href="{{ route('orders.invoice', $o->id) }}" target="_blank"
                                            class="btn btn-sm btn-primary">
                                            <i class="bi bi-printer"></i>
                                        </a>

                                        @if ($o->status === 'PENDING')
                                            <form action="{{ route('orders.complete', $o->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Selesaikan pesanan?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success"><i
                                                        class="bi bi-check-circle"></i></button>
                                            </form>
                                            <form action="{{ route('orders.cancel', $o->id) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Batalkan pesanan?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger"><i
                                                        class="bi bi-x-circle"></i></button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                        Tidak ada data pesanan ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>

    {{-- ================= MODAL DIPINDAHKAN KE SINI ================= --}}
    @foreach ($orders as $o)
        @php
            $statusBadge =
                $o->status === 'COMPLETED'
                    ? 'bg-success'
                    : ($o->status === 'CANCELLED'
                        ? 'bg-danger'
                        : ($o->status === 'PENDING'
                            ? 'bg-warning text-dark'
                            : 'bg-secondary text-white'));

        @endphp
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
                                <span class="badge {{ $statusBadge }} mt-1">{{ $o->status }}</span>
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
