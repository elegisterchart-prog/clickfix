<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\RepairRequest;
use App\Models\Warranty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
        $repairs = RepairRequest::orderBy('created_at', 'desc')->paginate(25);
        $assignedTechnicians = RepairRequest::whereNotNull('assigned_to')
            ->distinct()
            ->pluck('assigned_to')
            ->filter()
            ->values();

        return view('admin.dashboard', compact('repairs', 'assignedTechnicians'));
    }

    public function assign(Request $request, $id)
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'email', 'max:255'],
        ]);

        $repair = RepairRequest::findOrFail($id);
        if ($repair->quote_status !== 'accepted') {
            return redirect()->back()->with('status', 'ลูกค้าต้องยืนยันการซ่อมก่อนจึงจะสามารถมอบหมายให้ช่างได้');
        }

        // Ensure the assigned_to refers to an existing technician account
        $tech = User::where('email', $data['assigned_to'])->where('role', 'technician')->first();
        if (! $tech) {
            return redirect()->back()->with('status', 'ไม่พบผู้ใช้ที่เป็นช่างด้วยอีเมลที่ระบุ');
        }

        // Only set the assigned technician here; technicians are responsible for status updates.
        $repair->assigned_to = $tech->email;
        $repair->save();

        return redirect()->back()->with('status', 'มอบหมายงานเรียบร้อยแล้ว');
    }

    public function sendQuote(Request $request, $id)
    {
        $data = $request->validate([
            'quote_price' => ['required', 'numeric', 'min:0'],
            'quote_message' => ['nullable', 'string'],
        ]);

        $repair = RepairRequest::findOrFail($id);
        $repair->quote_price = $data['quote_price'];
        $repair->quote_message = $data['quote_message'] ?? null;
        $repair->quote_status = 'sent_to_customer';
        $repair->status = 'pending_quote';
        $repair->save();

        return redirect()->back()->with('status', 'ส่งใบเสนอราคากลับไปยังลูกค้าแล้ว');
    }

    public function warrantySearch(Request $request)
    {
        $q = $request->input('q');
        $results = collect();

        if ($q) {
            $results = Warranty::where('serial_number', $q)
                ->orWhereHas('user', function ($query) use ($q) {
                    $query->where('email', $q)->orWhere('phone', $q)->orWhere('name', 'like', "%{$q}%");
                })->get();
        }

        return view('admin.warranty', ['results' => $results, 'q' => $q]);
    }

    public function users()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(50);
        return view('admin.users', compact('users'));
    }

    public function updateUserRole(Request $request, $id)
    {
        $data = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $user = User::findOrFail($id);
        $user->role = $data['role'];
        $user->save();

        return redirect()->back()->with('status', 'อัปเดตบทบาทสมาชิกเรียบร้อย');
    }

    public function products()
    {
        $products = Product::orderBy('category')->orderBy('name')->get();
        $categories = Product::categories();
        return view('admin.products', compact('products', 'categories'));
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at', 'desc')->paginate(25);

        return view('admin.orders.index', compact('orders'));
    }

    public function showOrder($id)
    {
        $order = Order::with('user')->findOrFail($id);
        return view('admin.orders.show', ['order' => $order]);
    }

    public function assignOrder(Request $request, $id)
    {
        $data = $request->validate([
            'assigned_to' => ['required', 'email', 'max:255'],
        ]);

        $order = Order::findOrFail($id);
        $tech = User::where('email', $data['assigned_to'])->where('role', 'technician')->first();
        if (! $tech) {
            return redirect()->back()->with('status', 'ไม่พบผู้ใช้ที่เป็นช่างด้วยอีเมลที่ระบุ');
        }

        $order->assigned_to = $tech->email;
        $order->save();

        return redirect()->back()->with('status', 'มอบหมายงานเรียบร้อยแล้ว');
    }

    public function showRepair($id)
    {
        $repair = RepairRequest::with(['user','updates.user'])->findOrFail($id);
        // admin can view everything the customer sent including attachments
        return view('admin.repairs.show', ['repair' => $repair]);
    }

    public function addProduct(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'sku' => ['nullable', 'string'],
            'category' => ['required', 'string', Rule::in(array_keys(Product::categories()))],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'details' => ['nullable', 'string'],
        ]);

        Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'category' => $data['category'],
            'price' => $data['price'] ?? 0,
            'stock' => $data['stock'] ?? 0,
            'details' => $data['details'] ?? null,
        ]);

        return redirect()->back()->with('status', 'เพิ่มสินค้าเรียบร้อยแล้ว');
    }

    public function removeProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->back()->with('status', 'ลบสินค้าสำเร็จแล้ว');
    }
}
