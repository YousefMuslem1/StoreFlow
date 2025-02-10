<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cost extends Model
{
    use HasFactory;
    protected $table = 'costs';
    protected $fillable = [ 'user_id', 'type_id', 'cost_value', 'note'];
    
    public function costType()
    {
        return $this->belongsTo(CostType::class, 'type_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
