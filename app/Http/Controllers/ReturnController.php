<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Retur;
use App\Models\Produk;
use App\Models\PergerakanStok;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnController extends Controller
{
    // Menggantikan returnsPage (View utama: Tabel Daftar Retur)
    public function index(Request $request)
    {
        $query = Retur::with('produk:id,kode_produk,nama_produk');

        // Implementasi Filter dan Search
        if ($request->search) {
            $search = strtolower($request->search);
            $query->where('nomor_retur', 'LIKE', "%{$search}%")
                ->orWhere('nama_pelanggan', 'LIKE', "%{$search}%")
                ->orWhere('telepo_pelanggan', 'LIKE', "%{$search}%");
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $returns = $query->get();
        return view('returns.index', compact('returns'));
    }

    // Metode untuk menampilkan Form Pembuatan Retur (Tanpa perlu memuat semua produk)
    public function create()
    {
        // Kita tidak perlu memuat semua produk lagi, karena produk akan dimuat berdasarkan pesanan
        return view('returns.create');
    }

    // Metode Baru: Mencari Pesanan dan Memuat Item untuk Retur
    public function searchOrder(Request $request)
    {
        $request->validate([
            'nomor_pesanan' => 'required|string|exists:pesanan,nomor_pesanan',
        ]);

        $order = Pesanan::where('nomor_pesanan', $request->nomor_pesanan)
            ->with(['details.produk', 'returs'])
            ->first();

        if ($order->status !== 'COMPLETED') {
            return redirect()->back()->with('error', 'Retur hanya dapat dilakukan untuk pesanan COMPLETED.');
        }

        $returnableItems = $order->details->map(function ($item) use ($order) {
            // HITUNG TOTAL YANG SUDAH DIRETUR SEBELUMNYA (kecuali yang ditolak)
            $alreadyReturned = Retur::where('nomor_pesanan', $order->nomor_pesanan)
                ->where('id_produk', $item->id_produk)
                ->where('status', '!=', 'REJECTED')
                ->sum('jumlah');

            $remainingQty = $item->jumlah - $alreadyReturned;

            return [
                'id_produk' => $item->id_produk,
                'kode_produk' => $item->produk->kode_produk,
                'nama_produk' => $item->produk->nama_produk,
                'max_qty' => $remainingQty, // Kuota asli dikurangi yang sudah diretur
            ];
        })->filter(fn($item) => $item['max_qty'] > 0); // Hanya tampilkan item yang masih punya sisa kuota

        if ($returnableItems->isEmpty()) {
            return redirect()->back()->with('error', 'Semua item dalam pesanan ini sudah diretur sepenuhnya.');
        }

        return view('returns.create', compact('order', 'returnableItems'));
    }

    // Metode store yang dimodifikasi untuk memproses item retur dari pesanan
    public function store(Request $request)
    {
        $request->validate([
            'nomor_pesanan' => 'required|exists:pesanan,nomor_pesanan',
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'product_id' => 'required|exists:produk,id',
            'quantity' => 'required|integer|min:1',
            'alasan' => 'required',
            'catatan' => 'required',
        ]);

        $alreadyReturned = Retur::where('nomor_pesanan', $request->nomor_pesanan)
            ->where('id_produk', $request->product_id)
            ->where('status', '!=', 'REJECTED')
            ->sum('jumlah');

        $orderItem = DetailPesanan::where('nomor_pesanan_fk', $request->nomor_pesanan)
            ->where('id_produk', $request->product_id)
            ->first();

        if (($alreadyReturned + $request->quantity) > $orderItem->jumlah) {
            return redirect()->back()->withInput()->with('error', 'Gagal! Total retur akan melebihi jumlah pembelian asli.');
        }

        $order = Pesanan::where('nomor_pesanan', $request->nomor_pesanan)->first();
        $orderItem = DetailPesanan::where('nomor_pesanan_fk', $request->nomor_pesanan)
            ->where('id_produk', $request->product_id)
            ->first();

        // Validasi Kuantitas Retur (Tidak boleh melebihi jumlah yang dibeli)
        if (!$orderItem || $request->quantity > $orderItem->jumlah) {
            return redirect()->back()->withInput()->with('error', 'Kuantitas retur melebihi jumlah yang dibeli dalam pesanan ini.');
        }

        $returnNumber = 'RET-' . Carbon::now()->format('YmdHis');

        Retur::create([
            'nomor_retur' => $returnNumber,
            'nomor_pesanan' => $order->nomor_pesanan, // Nomor pesanan valid
            'id_produk' => $request->product_id,
            'jumlah' => $request->quantity,
            'alasan' => $request->alasan,
            'catatan' => $request->catatan,
            'nama_pelanggan' => $request->customer_name,
            'telepo_pelanggan' => $request->customer_phone,
            'status' => 'PENDING',
        ]);

        return redirect()->route('returns.index')->with('success', 'Permintaan retur berhasil dibuat untuk pesanan ' . $order->nomor_pesanan . '!');
    }

    public function approve(Retur $retur)
    {
        if ($retur->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Retur sudah diproses.');
        }

        try {
            DB::transaction(function () use ($retur) {

                $productId = $retur->id_produk;
                $returnQty = $retur->jumlah;
                $orderNumber = $retur->nomor_pesanan;

                if ($orderNumber === 'N/A') {
                    $processSaleAdjustment = false;
                } else {
                    $processSaleAdjustment = true;
                }

                $product = $retur->produk;
                $product->increment('stok_produk', $returnQty);
                PergerakanStok::create([
                    'produk_id' => $product->id,
                    'tipe_pergerakan' => 'IN',
                    'jumlah' => $returnQty,
                    'tipe_referensi' => 'RETURN',
                    'catatan' => 'Retur: ' . $retur->nomor_retur,
                ]);



                if ($processSaleAdjustment) {
                    $order = Pesanan::where('nomor_pesanan', $orderNumber)->firstOrFail();

                    $orderItem = DetailPesanan::where('nomor_pesanan_fk', $orderNumber)
                        ->where('id_produk', $productId)
                        ->first();

                    if (!$orderItem) {
                        throw new \Exception("Item produk tidak ditemukan di detail pesanan {$orderNumber}.");
                    }

                    $itemPrice = $orderItem->harga;
                    $deductionAmount = $itemPrice * $returnQty;

                    $remainingQty = $orderItem->jumlah - $returnQty;

                    if ($remainingQty < 0) {
                        throw new \Exception("Kuantitas retur melebihi yang tersisa di pesanan.");
                    }

                    if ($remainingQty === 0) {
                        $orderItem->delete();
                    } else {
                        $orderItem->update([
                            'jumlah' => $remainingQty,
                            'sub_total' => $remainingQty * $itemPrice
                        ]);
                    }

                    $newTotal = $order->total_pembayaran - $deductionAmount;

                    $order->update([
                        'total_pembayaran' => $newTotal,
                        'catatan' => ($order->catatan ?? '') . "\n [Retur Disetujui]: Jumlah $returnQty untuk produk {$product->kode_produk} dikurangi dari faktur."
                    ]);
                }

                $retur->update(['status' => 'APPROVED']);
            });

            return redirect()->back()->with('success', 'Retur disetujui. Stok dikembalikan, dan faktur penjualan disesuaikan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyetujui retur: ' . $e->getMessage());
        }
    }

    // Menggantikan reject (Aksi POST dan redirect)
    public function reject(Retur $retur)
    {
        if ($retur->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Retur sudah diproses.');
        }

        $retur->update(['status' => 'REJECTED']);
        return redirect()->back()->with('success', 'Retur ditolak!');
    }
}
