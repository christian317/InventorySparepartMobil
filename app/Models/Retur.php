<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $table = 'retur';

    protected $fillable = [
        'nomor_retur', 'nomor_pesanan', 'id_produk', 'jumlah', 'alasan', 'catatan',
        'status', 'nama_pelanggan', 'telepo_pelanggan', 'created_by'
    ];

    public $timestamps = false;

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'nomor_pesanan', 'nomor_pesanan');
    }
}
