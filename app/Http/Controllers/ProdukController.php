<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Brand;
use Carbon\Carbon;
use App\Models\PergerakanStok;
use Illuminate\Support\Facades\DB;


use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $kategori = Kategori::all();
        $brand = Brand::all();

        // Query dasar dengan Eager Loading
        $query = Produk::with(['kategori', 'brand']);

        // --- Logika Filter Server-Side ---
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_produk', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_produk', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status == 'low') {
                $query->whereRaw('stok_produk <= min_stok AND stok_produk > 0');
            } elseif ($request->stock_status == 'empty') {
                $query->where('stok_produk', 0);
            }
        }

        $produk = $query->get();

        return view('produk.index', compact('produk', 'kategori', 'brand'));
    }

    // Kategori
    public function createKategori()
    {
        return view('produk.kategori.create');
    }

    public function storeKategori(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
        ]);

        Kategori::create([
            'nama' => $validatedData['nama'],
            'deskripsi' => $validatedData['deskripsi'],
        ]);

        return redirect()->route('produk.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function destroyKategori($id)
    {
        try {
            $item = Kategori::findOrFail($id);
            $item->delete();
            return redirect()->route('produk.index')->with('success', 'Kategori berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal menghapus! Kategori mungkin masih digunakan oleh produk.');
        }
    }

    // Brand
    public function createBrand()
    {
        return view('produk.brand.create');
    }
    public function storeBrand(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:brand,nama',
            'deskripsi' => 'nullable|string',
        ]);

        Brand::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('produk.index')->with('success', 'Brand berhasil ditambahkan!');
    }

    public function destroyBrand($id)
    {
        try {
            $item = Brand::findOrFail($id);
            $item->delete();
            return redirect()->route('produk.index')->with('success', 'Brand berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->with('danger', 'Gagal menghapus! Brand mungkin masih digunakan oleh produk.');
        }
    }

    // Produk
    function create()
    {
        $kategori = Kategori::all();
        $brand = Brand::all();
        return view('produk.create', compact('kategori', 'brand'));
    }

    function store(Request $request)
    {
        $request->validate([
            'kode_produk' => 'required|unique:produk,kode_produk|max:45',
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|integer|exists:kategori,id',
            'brand_id' => 'required|integer|exists:brand,id',
            'harga' => 'required|numeric|min:0',
            'stok_produk' => 'required|integer|min:0',
            'min_stok' => 'required|integer|min:0',
            'deskripsi_produk' => 'nullable|string',
        ]);

        Produk::create([
            'kode_produk' => $request->kode_produk,
            'nama_produk' => $request->nama_produk,
            'kategori_id' => $request->kategori_id,
            'brand_id' => $request->brand_id,
            'harga' => $request->harga,
            'stok_produk' => $request->stok_produk,
            'min_stok' => $request->min_stok,
            'deskripsi_produk' => $request->deskripsi_produk,
            'tanggal_masuk' => Carbon::now(),
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan!');
    }


    function edit(Produk $produk)
    {

        $kategori = Kategori::all();
        $brand = Brand::all();

        return view('produk.edit', compact('produk', 'kategori', 'brand'));
    }

    function update(Request $request, Produk $produk)
    {
        $validatedData = $request->validate([
            'kode_produk' => 'required|max:45|unique:produk,kode_produk,' . $produk->id,
            'nama_produk' => 'required|string|max:255',
            'kategori_id' => 'required|integer|exists:kategori,id',
            'brand_id' => 'required|integer|exists:brand,id',
            'harga' => 'required|numeric|min:0',
            'min_stok' => 'required|integer|min:0',
            'deskripsi_produk' => 'nullable|string',
        ]);

        $dataToUpdate = $validatedData;
        unset($dataToUpdate['stok_produk']);

        $produk->update($dataToUpdate);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diupdate!');
    }

    function editStok(Request $request, Produk $produk)
    {
        return view('produk.editStok', compact('produk'));
    }

    public function saveStock(Request $request, Produk $produk)
    {
        $request->validate([
            'tipe_pergerakan' => 'required|in:MASUK,KELUAR',
            'jumlah' => 'required|integer|min:1',
            'catatan' => 'nullable|string',
        ]);

        $jumlah = (int) $request->jumlah;
        $tipe = $request->tipe_pergerakan;
        $newStock = $produk->stok_produk;

        DB::beginTransaction();
        try {
            if ($tipe === 'MASUK') {
                $newStock += $jumlah;
            } else { // KELUAR
                $newStock -= $jumlah;
                if ($newStock < 0) {
                    throw new \Exception('Stok tidak cukup!');
                }
            }

            $produk->stok_produk = $newStock;
            $produk->save();

            PergerakanStok::create([
                'produk_id' => $produk->id,
                'tipe_pergerakan' => $tipe,
                'jumlah' => $jumlah,
                'tipe_referensi' => 'ADJUSTMENT',
                'catatan' => $request->catatan,
            ]);

            DB::commit();
            return redirect()->route('produk.index')->with('success', 'Stok berhasil diupdate!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('danger', 'Error: ' . $e->getMessage());
        }
    }
}
