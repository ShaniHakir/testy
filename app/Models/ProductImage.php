<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'product_id', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}