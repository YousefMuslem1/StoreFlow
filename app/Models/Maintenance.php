<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasFactory;
    protected $table = 'maintenances';
    protected $fillable = [
        'weight',
        'cost',
        'recevieved_date',
        'status',
        'customer_id',
        'user_id',
        'product_images',
        'notice',
        'last_cost',
    ];
    protected $casts = [
        'product_images' => 'array', // Cast th e product_images field to array
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
