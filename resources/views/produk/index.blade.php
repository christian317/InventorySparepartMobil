@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        @if ($errors->any())
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif


        @if (session('danger'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('danger') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="alert alert-success alert-dismissible fade show d-none" role="alert" id="successAlert">
            Produk berhasil dioperasikan!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <h2>Manajemen Produk</h2>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('kategori.create') }}" class="btn btn-info text-white" id="addCategoryBtn">
                    <i class="bi bi-plus-circle"></i> Tambah Kategori
                </a>
                <a href="{{ route('brand.create') }}" class="btn btn-warning text-dark" id="addBrandBtn">
                    <i class="bi bi-plus-circle"></i> Tambah Brand
                </a>
                <a href="{{ route('produk.create') }}" class="btn btn-primary" id="addProductBtn">
                    <i class="bi bi-plus-circle"></i> Tambah Produk
                </a>
                <button class="btn btn-success" id="exportPdfBtn">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                {{-- FORM FILTER --}}
                <form action="{{ route('produk.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cari</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama/Kode..."
                                value="{{ request('search') }}">
                        </div>

                        {{-- Kategori --}}
                        <div class="col-md-3">
                            <label class="form-label">Kategori</label>
                            <div class="input-group">
                                <select id="selectKategori" name="kategori_id" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($kategori as $k)
                                        <option value="{{ $k->id }}"
                                            {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Tombol Hapus: memanggil fungsi JS di bawah --}}
                                <button type="button" class="btn btn-outline-danger" onclick="hapusMaster('kategori')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Brand --}}
                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <div class="input-group">
                                <select id="selectBrand" name="brand_id" class="form-select">
                                    <option value="">Semua Brand</option>
                                    @foreach ($brand as $b)
                                        <option value="{{ $b->id }}"
                                            {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-outline-danger" onclick="hapusMaster('brand')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filter</button>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <a href="{{ route('produk.index') }}" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- FORM HAPUS TERSEMBUNYI --}}
        <form id="formHapusMaster" action="" method="POST" style="display:none;">
            @csrf
            @method('DELETE')
        </form>

        {{-- FORM TERSEMBUNYI UNTUK PENGHAPUSAN MASTER --}}
        @if (request('kategori_id'))
            <form id="formDeleteKategori" action="{{ route('kategori.destroy', request('kategori_id')) }}" method="POST"
                onsubmit="return confirm('Hapus kategori ini?')">
                @csrf
                @method('DELETE')
            </form>
        @endif

        @if (request('brand_id'))
            <form id="formDeleteBrand" action="{{ route('brand.destroy', request('brand_id')) }}" method="POST"
                onsubmit="return confirm('Hapus brand ini?')">
                @csrf
                @method('DELETE')
            </form>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Brand</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Min Stok</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="productsTable">
                            @foreach ($produk as $item)
                                <tr>
                                    <td>{{ $item->kode_produk }}</td>
                                    <td>{{ $item->nama_produk }}</td>
                                    <td>{{ $item->kategori->nama }}</td>
                                    <td>{{ $item->brand->nama }}</td>
                                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td>
                                        @if ($item->stok_produk > $item->min_stok)
                                            <span class="badge bg-success">{{ $item->stok_produk }}</span>
                                        @elseif($item->stok_produk <= $item->min_stok && $item->stok_produk > 0)
                                            <span class="badge bg-warning text-dark">{{ $item->stok_produk }}</span>
                                        @else
                                            <span class="badge bg-danger">{{ $item->stok_produk }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->min_stok }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-info" href="{{ route('produk.edit', $item->id) }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ route('produk.stokEdit', $item->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="8" class="text-center text-muted d-none">Tidak ada produk</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

    <script>
        const allProducts = @json($produk);

        function formatCurrency(amount) {
            if (amount === undefined || amount === null) return 'Rp 0';
            return 'Rp ' + parseFloat(amount).toLocaleString('id-ID');
        }

        document.getElementById('exportPdfBtn').addEventListener('click', exportToPDF);

        function exportToPDF() {
            const dataToExport = allProducts;

            if (dataToExport.length === 0) {
                alert('Tidak ada data produk untuk diexport.');
                return;
            }

            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            doc.setFontSize(18);
            doc.text('Laporan Stok Produk', 14, 22);

            doc.setFontSize(11);
            doc.text(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`, 14, 30);

            const tableData = dataToExport.map(p => [
                p.kode_produk,
                p.nama_produk,
                p.kategori_id,
                p.brand_id,
                formatCurrency(p.harga),
                p.stok_produk.toString(),
                p.min_stok.toString(),
            ]);

            doc.autoTable({
                head: [
                    ['Kode', 'Nama', 'Kategori ID', 'Brand ID', 'Harga', 'Stok', 'Min Stok']
                ],
                body: tableData,
                startY: 35,
                theme: 'striped',
                headStyles: {
                    fillColor: [52, 58, 64]
                }
            });

            doc.save(`stok-produk-${new Date().toISOString().split('T')[0]}.pdf`);
            alert('PDF berhasil diexport!');
        }

        function hapusMaster(tipe) {
            // Ambil ID dari select yang sesuai
            const selectId = tipe === 'kategori' ? 'selectKategori' : 'selectBrand';
            const id = document.getElementById(selectId).value;

            if (!id) {
                alert('Silakan pilih ' + tipe + ' yang ingin dihapus terlebih dahulu!');
                return;
            }

            if (confirm('Apakah Anda yakin ingin menghapus ' + tipe + ' ini?')) {
                const form = document.getElementById('formHapusMaster');
                // Set action form secara dinamis ke route destroy
                form.action = '/' + tipe + '/' + id;
                form.submit();
            }
        }
    </script>
@endpush
