<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Caliber extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $table = 'calibers';
    protected $fillable = ['name', 'full_name'  ,'caliber_price', 'transfarmed'];
    
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'full_name', 'caliber_price', 'transfarmed']);
        // Chain fluent methods for configuration options
    }

}
