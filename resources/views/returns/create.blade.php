@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2>Buat Retur Berdasarkan Pesanan</h2>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- =============================================== --}}
{{-- 1. FORM PENCARIAN PESANAN --}}
{{-- =============================================== --}}
<div class="card mb-4">
    <div class="card-header">
        <h5>Cari Nomor Pesanan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('returns.search') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" 
                               name="nomor_pesanan" 
                               class="form-control" 
                               placeholder="Masukkan Nomor Pesanan (e.g., ORD-20231201...)" 
                               value="{{ $order->nomor_pesanan ?? old('nomor_pesanan') }}"
                               required>
                        <button class="btn btn-primary" type="submit">Cari Pesanan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- =============================================== --}}
{{-- 2. FORM RETUR ITEM (Hanya Tampil Jika Pesanan Ditemukan) --}}
{{-- =============================================== --}}
@isset($order)
<div class="card">
    <div class="card-header bg-info text-white">
        <h5>Detail Pesanan Ditemukan: {{ $order->nomor_pesanan }}</h5>
        <p class="mb-0">Pelanggan: {{ $order->nama_pelanggan }} | Status: {{ $order->status }}</p>
    </div>
    <div class="card-body">
        <form id="returnForm" action="{{ route('returns.store') }}" method="POST">
            @csrf
            {{-- Hidden Field --}}
            <input type="hidden" name="nomor_pesanan" value="{{ $order->nomor_pesanan }}">
            <input type="hidden" name="customer_name" value="{{ $order->nama_pelanggan }}">
            <input type="hidden" name="customer_phone" value="{{ $order->telepon_pelanggan }}">

            <div class="row">
                <div class="col-md-6 border-end">
                    
                    {{-- DROPDOWN ITEM PESANAN --}}
                    <div class="mb-3">
                        <label for="returnProductSelect" class="form-label">Pilih Item untuk Retur <span class="text-danger">*</span></label>
                        <select class="form-select" id="returnProductSelect" name="product_id" required>
                            <option value="">-- Pilih Produk dari Pesanan --</option>
                            @foreach ($returnableItems as $item)
                                <option value="{{ $item['id_produk'] }}" data-max="{{ $item['max_qty'] }}">
                                    {{ $item['kode_produk'] }} - {{ $item['nama_produk'] }} (Max: {{ $item['max_qty'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- JUMLAH RETUR --}}
                    <div class="mb-3">
                        <label for="returnQuantity" class="form-label">Jumlah Retur <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="returnQuantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" required>
                        <small class="text-muted" id="maxQtyHint"></small>
                    </div>
                    
                    {{-- ALASAN RETUR --}}
                    <div class="mb-3">
                        <label for="returnReason" class="form-label">Alasan Retur <span class="text-danger">*</span></label>
                        <select class="form-select" id="returnReason" name="alasan" required>
                            <option value="">-- Pilih Alasan --</option>
                            <option value="Rusak" {{ old('alasan') == 'Rusak' ? 'selected' : '' }}>Rusak</option>
                            <option value="Salah Kirim" {{ old('alasan') == 'Salah Kirim' ? 'selected' : '' }}>Salah Kirim</option>
                            <option value="Tidak Sesuai" {{ old('alasan') == 'Tidak Sesuai' ? 'selected' : '' }}>Tidak Sesuai</option>
                            <option value="Lainnya" {{ old('alasan') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    {{-- CATATAN --}}
                    <div class="mb-3">
                        <label for="returnDescription" class="form-label">Deskripsi / Catatan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="returnDescription" name="catatan" rows="5" required>{{ old('catatan') }}</textarea>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('returns.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Permintaan Retur</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endisset
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectProduct = document.getElementById('returnProductSelect');
        const inputQuantity = document.getElementById('returnQuantity');
        const maxQtyHint = document.getElementById('maxQtyHint');

        // Fungsi untuk menampilkan max Qty dan membatasi input
        function updateMaxQuantity() {
            const selectedOption = selectProduct.options[selectProduct.selectedIndex];
            
            if (selectedOption && selectedOption.value) {
                const maxQty = parseInt(selectedOption.dataset.max);
                maxQtyHint.textContent = `Maksimum kuantitas retur: ${maxQty}`;
                inputQuantity.max = maxQty;
                
                // Pastikan input quantity tidak melebihi maxQty saat produk dipilih
                if (parseInt(inputQuantity.value) > maxQty) {
                    inputQuantity.value = maxQty;
                }
            } else {
                maxQtyHint.textContent = '';
                inputQuantity.max = ''; 
            }
        }

        // Event listener untuk saat produk dipilih
        if (selectProduct) {
            selectProduct.addEventListener('change', updateMaxQuantity);
            // Panggil saat load pertama kali jika ada data pesanan lama (old data)
            updateMaxQuantity(); 
        }

        // Event listener untuk memvalidasi input saat diketik/diubah
        if (inputQuantity) {
            inputQuantity.addEventListener('input', function() {
                const max = parseInt(inputQuantity.max);
                const current = parseInt(inputQuantity.value);
                
                if (max && current > max) {
                    inputQuantity.value = max;
                    showAlert(`Kuantitas tidak boleh melebihi batas maksimum retur (${max})!`, 'warning');
                }
            });
        }

        // Implementasi showAlert (untuk user feedback di JS)
        function showAlert(message, type) {
            // Anda bisa menggunakan console.log atau alert sederhana
            console.log(`[${type.toUpperCase()}] ${message}`);
            // alert(`[${type.toUpperCase()}] ${message}`);
        }
    });
</script>
@endpush