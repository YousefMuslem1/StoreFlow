<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightQuantity extends Model
{
    use HasFactory;
    protected $fillable = [
        'quantity_id',
        'product_id',
        'weight',
        'price',
        'status',
        'ounce_price',
        'notice',
        'user_id',
        'created_at',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quantity()
    {
        return $this->belongsTo(Quantity::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class); 
    }
}
