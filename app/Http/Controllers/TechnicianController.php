<?php

namespace App\Http\Controllers;

use App\Models\RepairRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TechnicianController extends Controller
{
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function dashboard()
    {
        $technicianEmail = Auth::user()->email;
        $repairs = RepairRequest::where('assigned_to', $technicianEmail)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // orders awaiting dispatch (status: new or awaiting_dispatch)
        $orders = \App\Models\Order::where('assigned_to', $technicianEmail)
            ->whereIn('status', ['new', 'awaiting_dispatch'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('technician.dashboard', [
            'repairs' => $repairs,
            'orders' => $orders,
            'technician' => Auth::user()->name,
        ]);
    }


    public function showRepair($id)
    {
        $technicianEmail = Auth::user()->email;
        $repair = RepairRequest::where('assigned_to', $technicianEmail)->findOrFail($id);
        return view('technician.repair', ['repair' => $repair, 'technician' => Auth::user()->name]);
    }

    // Show assigned order to technician (cart-based order)
    public function showOrder($id)
    {
        $technicianEmail = Auth::user()->email;
        $order = \App\Models\Order::where('assigned_to', $technicianEmail)->findOrFail($id);
        return view('technician.order', ['order' => $order, 'technician' => Auth::user()->name]);
    }

    public function uploadOrderClip(Request $request, $id)
    {
        $technicianEmail = Auth::user()->email;
        $order = \App\Models\Order::where('assigned_to', $technicianEmail)->findOrFail($id);

        $data = $request->validate([
            'clip' => 'required|file|max:51200', // max 50MB
            'message' => 'nullable|string|max:1000',
        ]);

        $file = $data['clip'];
        $path = $file->store("orders/{$order->id}");

        $attachments = $order->attachments ?? [];
        $attachments[] = $path;
        $order->attachments = $attachments;
        // move status to awaiting_test_review or in_progress
        $order->status = 'awaiting_test';
        if (! empty($data['message'])) {
            $order->notes = trim(($order->notes ?? '') . "\n[ช่าง {$technicianEmail}] " . $data['message']);
        }
        $order->save();

        return redirect()->route('technician.order.show', ['id' => $order->id])->with('status', 'อัปโหลดคลิปเทสเรียบร้อยแล้ว');
    }

    public function dispatchOrder(Request $request, $id)
    {
        $technicianEmail = Auth::user()->email;
        $order = \App\Models\Order::where('assigned_to', $technicianEmail)->findOrFail($id);

        $order->status = 'dispatched';
        $order->save();

        return redirect()->route('technician.dashboard')->with('status', 'ยืนยันการจัดส่งเรียบร้อยแล้ว');
    }
}
