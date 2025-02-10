<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quantity extends Model
{
    use HasFactory;

    protected $fillable = [
        'ident',
        'short_ident',
        'caliber_id',
        'type_id',
        'total_weight',
        'remaining_weight',
        'user_id',
    ];
    public function caliber()
    {
        return $this->belongsTo(Caliber::class);
    }

    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function weightQuantities()
    {
        return $this->hasMany(WeightQuantity::class); 
    }
}
