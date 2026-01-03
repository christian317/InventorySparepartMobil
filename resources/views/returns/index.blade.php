@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark">Manajemen Retur Produk</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('returns.create') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-plus-circle"></i> Buat Retur
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('returns.index') }}">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted">Cari Retur</label>
                        <input type="text" class="form-control" name="search"
                            placeholder="Nomor retur, pesanan, atau pelanggan..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Status</label>
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="PENDING" {{ request('status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-secondary w-100">
                            <i class="bi bi-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No Retur</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($returns as $r)
                            @php
                                $statusBadge = $r->status === 'APPROVED'
                                    ? 'bg-success'
                                    : ($r->status === 'REJECTED'
                                        ? 'bg-danger'
                                        : 'bg-warning text-dark');
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <strong>{{ $r->nomor_retur }}</strong><br>
                                    <small class="text-muted">Ref: {{ $r->nomor_pesanan }}</small>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $r->nama_pelanggan }}</div>
                                    <small class="text-muted">{{ $r->telepo_pelanggan }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ $statusBadge }}">{{ $r->status }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-info text-white"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalRetur{{ $r->id }}">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    @if ($r->status === 'PENDING')
                                        <form action="{{ route('returns.approve', $r->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Setujui retur? Stok produk akan bertambah kembali.');">
                                            @csrf
                                            <button class="btn btn-sm btn-success">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('returns.reject', $r->id) }}"
                                            method="POST" class="d-inline"
                                            onsubmit="return confirm('Tolak retur ini?');">
                                            @csrf
                                            <button class="btn btn-sm btn-danger">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    Tidak ada data retur ditemukan.
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
@foreach ($returns as $r)
@php
    $statusBadge = $r->status === 'APPROVED'
        ? 'bg-success'
        : ($r->status === 'REJECTED'
            ? 'bg-danger'
            : 'bg-warning text-dark');
@endphp

<div class="modal fade" id="modalRetur{{ $r->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold">
                    Detail Retur #{{ $r->nomor_retur }}
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-4">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="text-muted small d-block">Pelanggan:</label>
                        <h6 class="fw-bold mb-0">{{ $r->nama_pelanggan }}</h6>
                        <span class="text-muted small">{{ $r->telepo_pelanggan }}</span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <label class="text-muted small d-block">Referensi Pesanan:</label>
                        <h6 class="fw-bold text-primary mb-0">#{{ $r->nomor_pesanan }}</h6>
                        <span class="badge {{ $statusBadge }} mt-1">{{ $r->status }}</span>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produk yang Diretur</th>
                                <th class="text-center" style="width:100px;">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $r->produk->nama_produk ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $r->produk->kode_produk ?? '-' }}</small>
                                </td>
                                <td class="text-center fs-5 fw-bold">{{ $r->jumlah }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border-start border-warning border-4">
                            <label class="text-muted small d-block fw-bold">Alasan Retur:</label>
                            <p class="mb-0">{{ $r->alasan }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded border-start border-secondary border-4 h-100">
                            <label class="text-muted small d-block fw-bold">Catatan Admin:</label>
                            <p class="mb-0 fst-italic">{{ $r->catatan ?? 'Tidak ada catatan.' }}</p>
                        </div>
                    </div>
                </div>
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
