<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use App\Models\RepairUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class RepairUpdateController extends Controller
{
    public function store(Request $request, $repairId)
    {
        $repair = RepairRequest::findOrFail($repairId);

        if (! auth()->check() || auth()->user()->role !== 'technician') {
            return redirect()->route('login')->with('error', 'กรุณาเข้าสู่ระบบช่างก่อน');
        }

        $data = $request->validate([
            'status' => 'nullable|in:confirmed,in_progress,closed',
            'message' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:51200'
        ]);

        $update = RepairUpdate::create([
            'repair_request_id' => $repair->id,
            'user_id' => Auth::id(),
            'status' => $data['status'] ?? null,
            'message' => $data['message'] ?? null,
            'attachments' => null,
        ]);

        $files = [];
        $allowed = ['jpg','jpeg','png','heic','webp','mp4','mov','avi','mkv','webm'];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file->isValid()) continue;
                $ext = strtolower($file->getClientOriginalExtension());
                if (! in_array($ext, $allowed, true)) {
                    return back()->with('error', "ชนิดไฟล์ไม่รองรับ: .{$ext}");
                }
                $path = $file->store("repairs/{$repair->id}/updates");
                $files[] = $path;
            }
            if (count($files)) {
                $update->attachments = $files;
                $update->save();
            }
        }

        // update repair status if provided
        if (! empty($data['status'])) {
            $repair->status = $data['status'];
            $repair->save();
        }

        // If closed, attempt to notify customer by email (best-effort)
        try {
            if ($data['status'] === 'closed') {
                $email = $repair->email ?? ($repair->user?->email ?? null);
                if ($email) {
                    $subject = "แจ้งเตือน: งานซ่อม #{$repair->id} เสร็จสิ้น";
                    $body = "งานซ่อมของท่าน (หมายเลข #{$repair->id}) ได้ดำเนินการเสร็จสิ้นแล้ว\n\nดูรายละเอียด: " . route('repairs.show', ['id' => $repair->id]);
                    Mail::raw($body, function ($m) use ($email, $subject) {
                        $m->to($email)->subject($subject);
                    });
                }
            }
        } catch (\Exception $e) {
            logger('repair-update-mail-error: '.$e->getMessage());
        }

        return back()->with('status', 'อัปเดตสถานะเรียบร้อยแล้ว');
    }

    public function downloadUpdateAttachment($repairId, $updateId, $index)
    {
        $repair = RepairRequest::findOrFail($repairId);
        $update = $repair->updates()->findOrFail($updateId);

        if (Auth::user()->role !== 'admin' && $repair->user_id !== Auth::id() && $update->user_id !== Auth::id()) {
            abort(403);
        }

        $files = $update->attachments ?? [];
        if (! isset($files[$index])) {
            abort(404);
        }
        $path = $files[$index];
        if (! Storage::exists($path)) abort(404);
        return Storage::download($path);
    }
}
