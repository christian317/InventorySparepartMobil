@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Tambah Produk Baru</h2>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            {{-- Action URL akan diisi oleh backend Anda: POST /produk --}}
            <form action="{{ route('produk.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="kode_produk" class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" id="kode_produk" name="kode_produk" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                    </div>
                    <div class="col-md-6">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select" id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $item)
                                <option value="{{ $item->id }}" {{ old('kategori_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">Pilih Brand</option>
                            @foreach ($brand as $item)
                                <option value="{{ $item->id }}" {{ old('brand_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="harga" class="form-label">Harga Beli (Rp)</label>
                        <input type="number" class="form-control" id="harga" name="harga" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="stok_produk" class="form-label">Stok Awal</label>
                        <input type="number" class="form-control" id="stok_produk" name="stok_produk" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="min_stok" class="form-label">Minimal Stok</label>
                        <input type="number" class="form-control" id="min_stok" name="min_stok" min="0" required>
                    </div>
                    <div class="col-12">
                        <label for="deskripsi_produk" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi_produk" name="deskripsi_produk" rows="3"></textarea>
                    </div>
                    
                    <div class="col-12 text-end">
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Produk
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection