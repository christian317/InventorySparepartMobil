<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Pesanan;
use App\Models\Retur;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Produk::count();

        $totalOrders = Pesanan::count();

        $lowStock = Produk::whereRaw('stok_produk <= min_stok')->count();

        $pendingReturns = Retur::where('status', 'Pending')->count();

        $lowStockProducts = Produk::whereRaw('stok_produk <= min_stok')
            ->orderBy('stok_produk', 'asc')
            ->take(5)
            ->get();

        $recentOrders = Pesanan::orderBy('tanggal_pemesanan', 'desc')
            ->take(5)
            ->get();

        $pendingOrders = Pesanan::where('status', 'Pending')
            ->orderBy('tanggal_pemesanan', 'desc')
            ->get();

        $query = Pesanan::with(['details.produk'])->orderBy('tanggal_pemesanan', 'desc');
        $orders = $query->paginate(20)->withQueryString(); 


        return view('dashboard', compact(
            'totalProducts',
            'totalOrders',
            'lowStock',
            'pendingReturns',
            'lowStockProducts',
            'recentOrders',
            'pendingOrders',
            'orders'
        ));
    }
}
