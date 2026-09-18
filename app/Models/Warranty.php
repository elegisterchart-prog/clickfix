<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warranty extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','order_id','product_name','product_sku','serial_number',
        'purchase_date','warranty_months','warranty_expires_at','warranty_type','notes','attachments'
    ];

    protected $casts = [
        'attachments' => 'array',
        'purchase_date' => 'datetime',
        'warranty_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
