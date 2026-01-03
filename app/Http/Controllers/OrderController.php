<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\DetailPesanan;
use App\Models\PergerakanStok;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $defaultStatus = 'PENDING';
        $filterStatus = $request->status ?? $defaultStatus;

        // Load relasi details dan produk sekaligus (Eager Loading)
        $query = Pesanan::with(['details.produk'])->orderBy('tanggal_pemesanan', 'desc');

        if ($request->filled('search')) {
            $query->where('nomor_pesanan', 'like', '%' . $request->search . '%')
                ->orWhere('nama_pelanggan', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('tanggal_pemesanan', $request->date);
        }
        
        if ($filterStatus !== 'all') {
            $query->where('status', $filterStatus);
        }
        
        $orders = $query->paginate(20)->withQueryString(); 

        return view('orders.index', compact('orders', 'filterStatus'));
    }

    public function create()
    {
        $products = Produk::select('id', 'kode_produk', 'nama_produk', 'harga', 'stok_produk')->get();
        return view('orders.create', compact('products'));
    }

    // app/Http/Controllers/OrderController.php

    public function store(Request $request)
    {
        $items = json_decode($request->input('items'), true);
        $request->merge(['items' => $items]);
        $request->validate([
            'customer_name' => 'required',
            'customer_phone' => 'required',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:produk,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_address' => 'nullable',
            'notes' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $total = 0;
                $orderNumber = 'ORD-' . Carbon::now()->format('YmdHis');

                // 1. Buat Pesanan
                $order = Pesanan::create([
                    'nomor_pesanan' => $orderNumber,
                    'nama_pelanggan' => $request->customer_name,
                    'telepon_pelanggan' => $request->customer_phone,
                    'alamat_pelanggan' => $request->customer_address,
                    'tanggal_pemesanan' => Carbon::now(),
                    'status' => 'PENDING',
                    'catatan' => $request->notes,
                    'total_pembayaran' => 0,
                ]);

                $orderItemsData = [];
                foreach ($request->items as $item) { // $request->items sekarang adalah array
                    $product = Produk::findOrFail($item['product_id']);

                    if ($product->stok_produk < $item['quantity']) {
                        throw new \Exception("Stok produk {$product->nama_produk} tidak mencukupi!");
                    }
                    // ... (Logika subtotal, total, pergerakan stok, dan insert DetailPesanan tetap sama)

                    $subtotal = $item['quantity'] * $product->harga;
                    $total += $subtotal;

                    $orderItemsData[] = [
                        'nomor_pesanan_fk' => $orderNumber,
                        'id_produk' => $product->id,
                        'jumlah' => $item['quantity'],
                        'harga' => $product->harga,
                        'sub_total' => $subtotal,
                    ];

                    // Kurangi Stok & Catat Pergerakan Stok
                    $product->decrement('stok_produk', $item['quantity']);
                    PergerakanStok::create([
                        'produk_id' => $product->id,
                        'tipe_pergerakan' => 'OUT',
                        'jumlah' => $item['quantity'],
                        'tipe_referensi' => 'SALE',
                        'catatan' => "Pesanan {$orderNumber}",
                    ]);
                }

                DetailPesanan::insert($orderItemsData);
                $order->update(['total_pembayaran' => $total]);
            });

            return redirect()->route('orders.index')->with('success', 'Pesanan berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan pesanan: ' . $e->getMessage());
        }
    }

    // Menggantikan complete (Aksi POST dan redirect)
    public function complete(Pesanan $order)
    {
        if ($order->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Pesanan sudah diproses.');
        }

        $order->update(['status' => 'COMPLETED']);
        return redirect()->back()->with('success', 'Pesanan selesai!');
    }

    // Menggantikan cancel (Aksi POST dan redirect)
    public function cancel(Pesanan $order)
    {
        if ($order->status !== 'PENDING') {
            return redirect()->back()->with('error', 'Hanya pesanan PENDING yang bisa dibatalkan.');
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update(['status' => 'CANCELLED']);

                foreach ($order->details as $item) {
                    // Kembalikan Stok & Catat Pergerakan Stok
                    $product = $item->produk;
                    $product->increment('stok_produk', $item->jumlah);
                    PergerakanStok::create([
                        'produk_id' => $product->id,
                        'tipe_pergerakan' => 'IN',
                        'jumlah' => $item->jumlah,
                        'tipe_referensi' => 'ADJUSTMENT',
                        'catatan' => 'Pembatalan pesanan ' . $order->nomor_pesanan,
                    ]);
                }
            });
            return redirect()->back()->with('success', 'Pesanan dibatalkan, stok dikembalikan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan pesanan: ' . $e->getMessage());
        }
    }

    public function invoice(Pesanan $order)
    {
        $order->load('details.produk');

        // Pastikan lokalisasi Carbon disetel jika belum disetel global
        \Carbon\Carbon::setLocale('id');

        // Render Blade template ke HTML
        $pdf = PDF::loadView('orders.invoice', compact('order'));

        // Unduh PDF
        return $pdf->download('faktur-' . $order->nomor_pesanan . '.pdf');
    }
}
