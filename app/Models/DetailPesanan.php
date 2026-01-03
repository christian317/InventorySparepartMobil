<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $fillable = [
        'nomor_pesanan_fk', 'id_produk', 'jumlah', 'harga', 'sub_total',
    ];

    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'nomor_pesanan_fk', 'nomor_pesanan');
    }
}
