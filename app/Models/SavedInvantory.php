<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedInvantory extends Model
{
    use HasFactory;

    protected $fillable = [ 
        'saved_invantory_date_id',
        'product_id',
        'type_id',
        'caliber_id',
        // 'weight',
        // 'selled_price',
        'status',
        'caliber_filter',
        'type_filter',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
    public function caliber()
    {
        return $this->belongsTo(Caliber::class, 'caliber_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
