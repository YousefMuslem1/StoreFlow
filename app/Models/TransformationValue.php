<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransformationValue extends Model
{
    use HasFactory;

    protected $fillable = ['caliber_id', 'value'];

    public function caliber()
    {
        return $this->belongsTo(Caliber::class);
    }
}
