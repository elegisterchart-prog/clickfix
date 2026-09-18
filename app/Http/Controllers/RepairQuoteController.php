<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepairQuoteController extends Controller
{
    public function accept(Request $request, $id)
    {
        $repair = RepairRequest::findOrFail($id);

        $isOwner = $repair->user_id == Auth::id();
        $isEmailOwner = $repair->email && Auth::user()->email && strcasecmp($repair->email, Auth::user()->email) === 0;
        if (! $isOwner && ! $isEmailOwner) {
            abort(403);
        }

        if ($repair->quote_status !== 'sent_to_customer') {
            return back()->with('status', 'ไม่สามารถยืนยันการซ่อมได้ในสถานะปัจจุบัน');
        }

        $repair->quote_status = 'accepted';
        $repair->status = 'open';
        $repair->save();

        return redirect()->route('repairs.show', ['id' => $repair->id])->with('status', 'คุณได้ยืนยันการซ่อมเรียบร้อยแล้ว ระบบจะแจ้งให้ทีมช่างรับงาน');
    }
}
