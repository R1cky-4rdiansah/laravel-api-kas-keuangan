<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GambarKas extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_kas', 'gambar'
    ];
}
