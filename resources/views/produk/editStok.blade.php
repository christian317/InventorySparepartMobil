@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Pergerakan Stok: {{ $produk->kode_produk }} - {{ $produk->nama_produk }}</h2>
        </div>
    </div>

    {{-- Notifikasi Error/Danger --}}
    @if (session('danger'))
        <div class="alert alert-danger">
            {{ session('danger') }}
        </div>
    @endif
    
    @if ($errors->any())
        <div class="alert alert-danger">
            **Mohon Koreksi Kesalahan Input:**
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('produk.stock.save', $produk->id) }}" method="POST"> 
                @csrf
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="stockCurrent" class="form-label">Stok Saat Ini</label>
                        <input type="number" class="form-control" id="stockCurrent" value="{{ $produk->stok_produk }}" disabled>
                    </div>
                    <div class="col-md-4">
                        <label for="tipe_pergerakan" class="form-label">Jenis Pergerakan</label>
                        <select class="form-select @error('tipe_pergerakan') is-invalid @enderror" id="tipe_pergerakan" name="tipe_pergerakan" required>
                            <option value="MASUK" {{ old('tipe_pergerakan') == 'MASUK' ? 'selected' : '' }}>Stok Masuk (+)</option>
                            <option value="KELUAR" {{ old('tipe_pergerakan') == 'KELUAR' ? 'selected' : '' }}>Stok Keluar (-)</option>
                        </select>
                        @error('tipe_pergerakan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="jumlah" class="form-label">Kuantitas</label>
                        <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" name="jumlah" min="1" value="{{ old('jumlah') }}" required>
                        @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="catatan" class="form-label">Catatan / Referensi Transaksi</label>
                        <textarea class="form-control @error('catatan') is-invalid @enderror" id="catatan" name="catatan" rows="3">{{ old('catatan') }}</textarea>
                        @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                
                    <div class="col-12 text-end mt-4">
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning" id="saveStock">
                            <i class="bi bi-arrow-left-right"></i> Simpan Pergerakan Stok
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection