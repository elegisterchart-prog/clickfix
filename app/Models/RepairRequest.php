<?php

namespace App\Models;

use App\Models\RepairUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','name','email','phone','device_type','device_model','serial_number',
        'problem_description','details','address','preferred_contact_time','priority','status','assigned_to','attachments',
        'quote_price','quote_message','quote_status'
    ];

    protected $casts = [
        'attachments' => 'array',
        'quote_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updates()
    {
        return $this->hasMany(RepairUpdate::class, 'repair_request_id')->orderBy('created_at');
    }
}

