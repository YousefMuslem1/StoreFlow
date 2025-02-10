<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Quantity;
use App\Models\WeightQuantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
        'weight',
        'description',
        'measurement',
        // 'price',
        'selled_price',
        'ounce_price',
        'name',
        'ident',
        'short_ident',
        'caliber_id',
        'type_id',
        'status',
        'selled_date',
        'caliber_selled_price',
        'user_id',
        // 'entered_user_id',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function caliber()
    {
        return $this->belongsTo(Caliber::class);
    }

    public function weightQuantity()
    {
        return $this->hasOne(WeightQuantity::class);
    }

    public function quantity()
    {
        return $this->hasOneThrough(Quantity::class, WeightQuantity::class, 'product_id', 'id', 'id', 'quantity_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function order()
    {
        return  $this->hasOne(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y H:i');
    }


    public function getSelledDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y H:i') : null;
    }

    public function calculateSellingPrice()
    {
        return $this->weight * $this->caliber->caliber_price;
    }
}
