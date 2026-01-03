@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2>Buat Pesanan Baru</h2>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form id="orderForm" method="POST" action="{{ route('orders.store') }}">
            @csrf
            
            <div class="row">
                <div class="col-md-6 border-end">
                    <h4>Detail Pelanggan</h4>
                    
                    {{-- INPUT NAMA PELANGGAN --}}
                    <div class="mb-3">
                        <label for="customerName" class="form-label">Nama Pelanggan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customerName" name="customer_name" value="{{ old('customer_name') }}" required>
                    </div>
                    
                    {{-- INPUT TELEPON PELANGGAN (DILENGKAPI) --}}
                    <div class="mb-3">
                        <label for="customerPhone" class="form-label">Telepon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="customerPhone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                    </div>
                    
                    {{-- INPUT ALAMAT (DILENGKAPI) --}}
                    <div class="mb-3">
                        <label for="customerAddress" class="form-label">Alamat</label>
                        <textarea class="form-control" id="customerAddress" name="customer_address">{{ old('customer_address') }}</textarea>
                    </div>
                    
                    {{-- INPUT CATATAN (DILENGKAPI) --}}
                    <div class="mb-3">
                        <label for="orderNotes" class="form-label">Catatan</label>
                        <textarea class="form-control" id="orderNotes" name="notes">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4>Item Pesanan</h4>
                    
                    {{-- FORM TAMBAH ITEM (DILENGKAPI) --}}
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="orderProductSelect" class="form-label">Produk</label>
                            <select class="form-select" id="orderProductSelect">
                                <option value="">-- Pilih Produk --</option>
                                {{-- Produk datang dari OrderController@create --}}
                                @foreach ($products as $p)
                                    <option value="{{ $p->id }}"
                                        data-code="{{ $p->kode_produk }}"
                                        data-name="{{ $p->nama_produk }}"
                                        data-price="{{ $p->harga }}"
                                        data-stock="{{ $p->stok_produk }}">
                                        {{ $p->kode_produk }} - {{ $p->nama_produk }} (Stok: {{ $p->stok_produk }}) - {{ number_format($p->harga, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3">
                            <label for="orderQuantity" class="form-label">Qty</label>
                            <input type="number" class="form-control" id="orderQuantity" value="1" min="1">
                        </div>
                        <div class="col-3 d-flex align-items-end">
                            <button type="button" class="btn btn-success w-100" id="addOrderItem">Tambah</button>
                        </div>
                    </div>
                    
                    {{-- TABEL ITEM --}}
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="orderItemsTable">
                                <tr><td colspan="6" class="text-center text-muted">Belum ada item</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <h5 class="text-end mt-3">Total: <span id="orderTotal">Rp 0</span></h5>

                    {{-- HIDDEN INPUT --}}
                    <input type="hidden" name="items" id="hiddenOrderItems" required>

                    <div class="text-end mt-4">
                        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" id="submitOrderBtn">Simpan Pesanan</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let orderItems = [];

    // --- UTILS (WAJIB ADA) ---
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
    }
    
    function showAlert(message, type) {
        // Implementasi alert sederhana atau toast Bootstrap
        alert(`[${type.toUpperCase()}] ${message}`);
    }

    // --- ITEM LOGIC ---
    
    function updateOrderItemsTable() {
        const tbody = document.getElementById('orderItemsTable');
        const totalEl = document.getElementById('orderTotal');

        if (orderItems.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Belum ada item</td></tr>';
            totalEl.textContent = formatCurrency(0);
        } else {
            tbody.innerHTML = orderItems.map((item, index) => `
                <tr>
                    <td>${item.product_code}</td>
                    <td>${item.product_name}</td>
                    <td>${formatCurrency(item.price)}</td>
                    <td>${item.quantity}</td>
                    <td>${formatCurrency(item.subtotal)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="window.removeOrderItem(${index})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');

            const total = orderItems.reduce((sum, item) => sum + item.subtotal, 0);
            totalEl.textContent = formatCurrency(total);
        }

        // Setelah merender, update hidden input untuk dikirim ke Controller
        document.getElementById('hiddenOrderItems').value = JSON.stringify(orderItems);
    }

    window.removeOrderItem = function(index) {
        orderItems.splice(index, 1);
        updateOrderItemsTable();
    };

    function addOrderItem() {
        const select = document.getElementById('orderProductSelect');
        const quantityEl = document.getElementById('orderQuantity');
        const quantity = parseInt(quantityEl.value);

        if (!select.value) {
            showAlert('Pilih produk terlebih dahulu!', 'warning');
            return;
        }

        const option = select.options[select.selectedIndex];
        const productId = select.value;
        const code = option.dataset.code;
        const name = option.dataset.name;
        const price = parseFloat(option.dataset.price);
        const stock = parseInt(option.dataset.stock);

        if (quantity < 1 || isNaN(quantity)) {
            showAlert('Jumlah harus minimal 1!', 'warning');
            return;
        }
        
        // Cek total stok setelah penambahan
        const existingItem = orderItems.find(item => item.product_id == productId);
        const newQuantity = (existingItem ? existingItem.quantity : 0) + quantity;

        if (newQuantity > stock) {
            showAlert(`Total Qty (${newQuantity}) melebihi stok tersedia (${stock})!`, 'danger');
            return;
        }

        if (existingItem) {
            existingItem.quantity = newQuantity;
            existingItem.subtotal = existingItem.quantity * existingItem.price;
        } else {
            orderItems.push({
                product_id: productId,
                product_code: code,
                product_name: name,
                quantity: quantity,
                price: price,
                subtotal: quantity * price,
            });
        }

        updateOrderItemsTable();
        // Reset input item
        select.value = '';
        quantityEl.value = 1;
    }

    // --- SUBMIT FINAL ---
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        if (orderItems.length === 0) {
            e.preventDefault();
            showAlert('Tambahkan minimal 1 item!', 'warning');
        }
    });

    // --- SETUP EVENT LISTENERS ---
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('addOrderItem').addEventListener('click', addOrderItem);
        updateOrderItemsTable(); // Initial load
    });
</script>
@endpush