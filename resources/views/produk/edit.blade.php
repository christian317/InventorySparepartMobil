@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Update Produk: {{ $produk->nama_produk }}</h2>
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
            <form action="{{ route('produk.update', $produk->id) }}" method="POST">
                @csrf
                @method('PUT') 
                
                <div class="row g-3">
                    {{-- Kode Produk --}}
                    <div class="col-md-6">
                        <label for="kode_produk" class="form-label">Kode Produk</label>
                        <input type="text" class="form-control @error('kode_produk') is-invalid @enderror" 
                               id="kode_produk" name="kode_produk" 
                               value="{{ old('kode_produk', $produk->kode_produk) }}" required>
                        @error('kode_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- Nama Produk --}}
                    <div class="col-md-6">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control @error('nama_produk') is-invalid @enderror" 
                               id="nama_produk" name="nama_produk" 
                               value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                        @error('nama_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- Kategori (MENGGUNAKAN ID) --}}
                    <div class="col-md-6">
                        <label for="kategori_id" class="form-label">Kategori</label>
                        <select class="form-select @error('kategori_id') is-invalid @enderror" 
                                id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($kategori as $item)
                                <option value="{{ $item->id }}" 
                                        {{ (old('kategori_id') ?? $produk->kategori_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- Brand (MENGGUNAKAN ID) --}}
                    <div class="col-md-6">
                        <label for="brand_id" class="form-label">Brand</label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" 
                                id="brand_id" name="brand_id" required>
                            <option value="">Pilih Brand</option>
                            @foreach ($brand as $item)
                                <option value="{{ $item->id }}" 
                                        {{ (old('brand_id') ?? $produk->brand_id) == $item->id ? 'selected' : '' }}>
                                    {{ $item->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
        
                    {{-- Harga Jual --}}
                    <div class="col-md-4">
                        <label for="harga" class="form-label">Harga Jual (Rp)</label>
                        <input type="number" class="form-control @error('harga') is-invalid @enderror" 
                               id="harga" name="harga" min="0" 
                               value="{{ old('harga', $produk->harga) }}" required>
                        @error('harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    {{-- Stok Saat Ini (Disabled) --}}
                    <div class="col-md-4">
                        <label for="stok_produk" class="form-label">Stok Saat Ini</label>
                        <input type="number" class="form-control" id="stok_produk" 
                               value="{{ $produk->stok_produk }}" disabled>
                        <small class="text-muted">Stok diupdate melalui menu Pergerakan Stok.</small>
                    </div>
                    
                    {{-- Minimal Stok --}}
                    <div class="col-md-4">
                        <label for="min_stok" class="form-label">Minimal Stok</label>
                        <input type="number" class="form-control @error('min_stok') is-invalid @enderror" 
                               id="min_stok" name="min_stok" min="0" 
                               value="{{ old('min_stok', $produk->min_stok) }}" required>
                        @error('min_stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-12">
                        <label for="deskripsi_produk" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('deskripsi_produk') is-invalid @enderror"
                        id="deskripsi_produk" name="deskripsi_produk" rows="3">{{ old('deskripsi_produk', $produk->deskripsi_produk) }}</textarea>
                        @error('deskripsi_produk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-12 text-end">
                        <a href="{{ route('produk.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection