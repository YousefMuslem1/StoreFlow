<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'Purchases';

    protected $fillable = [
        'weight',
        'price',
        'caliber_id',
        'type_id',
    ];
}
