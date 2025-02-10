<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;
    protected $table = 'types';

    protected $fillable = ['name', 'is_quantity'];

    public function products()
    {
        return $this->hasMany(Product::class, 'type_id');
    }

    // Define an accessor to get the sum of weights
    public function getWeightSumAttribute()
    {
        return $this->products->sum('weight');
    }

    public function calibers()
    {
        return $this->hasMany(Caliber::class);
    }
}
