<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nomor_pesanan', 'nama_pelanggan', 'telepon_pelanggan', 'alamat_pelanggan',
        'tanggal_pemesanan', 'total_pembayaran', 'status', 'catatan'
    ];

    public $timestamps = false;

    protected $casts = [
        'tanggal_pemesanan' => 'datetime', // Mengonversi string DATETIME dari DB menjadi objek Carbon
    ];

    public function details()
    {
        return $this->hasMany(DetailPesanan::class, 'nomor_pesanan_fk', 'nomor_pesanan');
    }

    public function returs()
    {
        return $this->hasMany(Retur::class, 'nomor_pesanan', 'nomor_pesanan');
    }
    
}
