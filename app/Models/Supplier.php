<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'contact_info'];

 

    // Relationship with SupplierTransaction
    public function supplierTransactions()
    {
        return $this->hasMany(SupplierTransaction::class);
    }

    // Relationship with SupplierBalance
    public function supplierBalance()
    {
        return $this->hasOne(SupplierBalance::class);
    }
}
