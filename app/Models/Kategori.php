<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class kategori extends Model
{
    protected $table = 'kategori';

    protected $fillable = [
        'id',
        'nama',
        'deskripsi',
    ];

    public $timestamps = false;
}
