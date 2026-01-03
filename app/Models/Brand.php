<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class brand extends Model
{
    protected $table = 'brand';

    protected $fillable = [
        'id',
        'nama',
        'deskripsi',
    ];

    public $timestamps = false;
}
