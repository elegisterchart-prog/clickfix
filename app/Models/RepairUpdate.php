<?php

namespace App\Models;

use App\Models\RepairRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'repair_request_id','user_id','status','message','attachments'
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function repair()
    {
        return $this->belongsTo(RepairRequest::class, 'repair_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
