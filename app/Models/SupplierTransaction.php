<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SupplierTransaction extends Model
{
    protected $fillable = [
        'supplier_id', 
        'type',  // 1 for money, 2 for gold
        'amount', 
        'price_per_gram', 
        'user_id', 
        'expected_weight', 
        'received_weight', 
        'status',  // 1 for pending, 2 for completed
        'note', 
        'logs'
    ];

    /**
     * Get the supplier associated with the transaction.
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

   
    /**
     * Get the user who created the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if the transaction is pending.
     */
    public function isPending()
    {
        return $this->status === 1;
    }

    /**
     * Determine if the transaction is completed.
     */
    public function isCompleted()
    {
        return $this->status === 2;
    }
}

