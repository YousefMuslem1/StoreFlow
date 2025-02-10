<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory; 

    protected $fillable = ['name', 'phone'];

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, Installment::class, 'customer_id', 'id', 'id', 'product_id');
    }
}
