<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RepairRequestController extends Controller
{
    public function create()
    {
        return view('repairs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'details' => 'required|string|max:255',
            'priority' => 'nullable|in:low,normal,high',
            'attachments.*' => 'nullable|file|max:51200'
        ]);

        $data['user_id'] = Auth::id();
        $data['email'] = Auth::user()->email;
        $data['priority'] = $data['priority'] ?? 'normal';
        $data['status'] = 'pending_quote';
        $data['quote_status'] = 'awaiting_admin';

        $req = RepairRequest::create(collect($data)->except(['attachments','_token'])->toArray());

        // handle attachments
        $files = [];
        $allowed = ['jpg','jpeg','png','heic','webp','mp4','mov','avi','mkv','webm'];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if (! $file->isValid()) continue;
                $ext = strtolower($file->getClientOriginalExtension());
                if (! in_array($ext, $allowed, true)) {
                    return back()->with('error', "ชนิดไฟล์ไม่รองรับ: .{$ext}");
                }
                $path = $file->store("repairs/{$req->id}");
                $files[] = $path;
            }
            if (count($files)) {
                $req->attachments = $files;
                $req->save();
            }
        }

        // TODO: send notification/email to admin (left as future work or configurable)

        // redirect to details page after successful submission (more stable UX)
        return redirect()->route('repairs.show', ['id' => $req->id])->with('status', 'แจ้งซ่อมเรียบร้อยแล้ว');
    }

    public function index(Request $request)
    {
        $query = RepairRequest::with('updates');
        if (Auth::user()->role !== 'admin') {
            $query->where(function ($query) {
                $query->where('user_id', Auth::id());
                if (Auth::user()->email) {
                    $query->orWhere('email', Auth::user()->email);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        $list = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('repairs.index', ['repairs' => $list]);
    }

    public function show($id)
    {
        $r = RepairRequest::with(['updates.user', 'user'])->findOrFail($id);

        if (Auth::user()->role !== 'admin' && ! $this->userCanAccessRepair($r)) {
            abort(403);
        }

        return view('repairs.show', ['repair' => $r]);
    }

    public function updateStatus(Request $request, $id)
    {
        $r = RepairRequest::findOrFail($id);
        // Only technicians may update repair status through this endpoint
        if (Auth::user()->role !== 'technician') {
            abort(403);
        }

        $data = $request->validate([
            'status' => 'required|in:open,in_progress,closed'
        ]);

        if ($r->quote_status !== 'accepted' && in_array($data['status'], ['open', 'in_progress'], true)) {
            return back()->with('status', 'ไม่สามารถเปลี่ยนสถานะเป็นเปิดหรือกำลังดำเนินการได้จนกว่าลูกค้าจะยืนยันราคา');
        }

        $r->status = $data['status'];
        $r->save();
        return back()->with('status', 'อัปเดตสถานะเรียบร้อย');
    }

    public function downloadAttachment($id, $index)
    {
        $r = RepairRequest::findOrFail($id);
        if (Auth::user()->role !== 'admin' && ! $this->userCanAccessRepair($r)) {
            abort(403);
        }

        $files = $r->attachments ?? [];
        if (! isset($files[$index])) {
            abort(404);
        }
        $path = $files[$index];
        if (! Storage::exists($path)) abort(404);
        return Storage::download($path);
    }

    private function userCanAccessRepair(RepairRequest $repair)
    {
        if (! Auth::check()) {
            return false;
        }

        if ($repair->user_id == Auth::id()) {
            return true;
        }

        if ($repair->email && Auth::user()->email && strcasecmp($repair->email, Auth::user()->email) === 0) {
            return true;
        }

        return false;
    }
}
