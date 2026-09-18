<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sku',
        'category',
        'price',
        'stock',
        'details',
    ];

    public static function categories(): array
    {
        return [
            'cpu' => 'CPU',
            'motherboard' => 'Motherboard',
            'gpu' => 'GPU',
            'ram' => 'RAM',
            'storage' => 'Storage',
            'prebuilt' => 'คอมเซ็ต',
            'psu' => 'PSU',
        ];
    }
}
