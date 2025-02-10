<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedInvantoryDate extends Model
{
    use HasFactory;
    protected $fillable = [ 
        'type_id',
        'caliber_id',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
    public function caliber()
    {
        return $this->belongsTo(Caliber::class, 'caliber_id');
    }
}
