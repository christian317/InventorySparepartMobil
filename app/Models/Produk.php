<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'id',
        'kode_produk',
        'nama_produk',
        'kategori_id',
        'brand_id',
        'harga',
        'stok_produk',
        'min_stok',
        'deskripsi_produk',
        'tanggal_masuk',
    ];

    public $timestamps = false;

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'kategori_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }
}
