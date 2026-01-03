<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PergerakanStok extends Model
{
    protected $table = 'pergerakan_stok';

    protected $fillable = [
        'id',
        'produk_id',
        'tipe_pergerakan',
        'jumlah',
        'tipe_referensi',
        'catatan',
    ];

    public $timestamps = false;
}