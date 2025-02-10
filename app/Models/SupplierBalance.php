<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 
        'fixed_gold', 
        'fixed_money', 
        'unfixed_gold', 
        'unfixed_money'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
