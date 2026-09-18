<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectAfterLogin();
        }

        $user = User::where('email', strtolower(trim($credentials['email'])))->first();
        $legacyPasswords = ['password123', 'Password123!', 'password'];

        if ($user && in_array($credentials['password'], $legacyPasswords, true)) {
            foreach ($legacyPasswords as $legacyPassword) {
                if (Hash::check($legacyPassword, $user->password)) {
                    $user->password = Hash::make($credentials['password']);
                    $user->save();
                    Auth::login($user, $request->boolean('remember'));
                    $request->session()->regenerate();

                    return $this->redirectAfterLogin();
                }
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function redirectAfterLogin()
    {
        return redirect()->intended(route('home'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }

    public function dashboard()
    {
        return view('dashboard', [
            'sections' => $this->specSections(),
        ]);
    }

    public function showRepairRequest()
    {
        return view('repairs.create');
    }

    public function showProductCategory(string $section)
    {
        $categories = Product::categories();
        if (! isset($categories[$section])) {
            return redirect()->route('dashboard');
        }

        $query = Product::where('category', $section)->orderBy('name');
        $vendor = request()->query('vendor');
        if ($section === 'prebuilt' && $vendor) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($vendor) . '%']);
        }

        $products = $query->get();
        $categoryLabel = $categories[$section];
        $description = 'เลือกสินค้าจากหมวด ' . $categoryLabel . ' เพื่อเพิ่มลงตะกร้า';

        return view('products', compact('products', 'section', 'categoryLabel', 'description', 'vendor'));
    }
 
    public function showProductDetail(string $id)
    {
        $product = Product::where('id', $id)->orWhere('sku', $id)->first();
        if (! $product) {
            return redirect()->back()->with('status', 'ไม่พบสินค้าที่ร้องขอ');
        }

        return view('product_detail', ['item' => $product->toArray()]);
    }

    public function showPrebuiltVendor(string $vendor)
    {
        $vendor = strtolower($vendor);
        if (! in_array($vendor, ['amd', 'intel'])) {
            return redirect()->route('products.category', ['section' => 'prebuilt']);
        }

        $sections = $this->applyCurrentStockToSections($this->specSections());
        $currentSection = collect($sections)->firstWhere('key', 'prebuilt');
        if (! $currentSection) {
            return redirect()->route('dashboard');
        }

        // Pass vendor to view via query param to reuse existing view logic
        return redirect()->route('products.category', ['section' => 'prebuilt', 'vendor' => $vendor]);
    }

    public function showWarranty()
    {
        $userId = Auth::id();
        $warranties = Warranty::where('user_id', $userId)
            ->orderBy('purchase_date', 'desc')
            ->get();

        return view('warranties.index', ['warranties' => $warranties]);
    }

    // Customer: list orders page
    public function ordersIndex()
    {
        $orders = collect();
        if (Auth::check()) {
            $orders = \App\Models\Order::where(function($q) {
                $q->where('user_id', Auth::id());
                if (Auth::user()->email) {
                    $q->orWhere('email', Auth::user()->email);
                }
            })->orderBy('created_at', 'desc')->paginate(20);
        }

        return view('orders.index', ['orders' => $orders]);
    }

    // Customer: view single order (only if belongs to them)
    public function showOrderForUser($id)
    {
        $order = \App\Models\Order::findOrFail($id);
        if (! Auth::check()) abort(403);
        if ($order->user_id != Auth::id() && (! $order->email || strcasecmp($order->email, Auth::user()->email) !== 0)) {
            abort(403);
        }

        return view('orders.show', ['order' => $order]);
    }

    public function cancelOrder(Request $request, $id)
    {
        $order = \App\Models\Order::findOrFail($id);

        if (! Auth::check()) {
            abort(403);
        }

        if ($order->user_id != Auth::id() && (! $order->email || strcasecmp($order->email, Auth::user()->email) !== 0)) {
            abort(403);
        }

        if (($order->status ?? '') === 'cancelled') {
            return redirect()->route('orders.show', ['id' => $order->id])->with('status', 'คำสั่งซื้อนี้ถูกยกเลิกแล้ว');
        }

        $inventory = $this->loadInventory();
        foreach ($order->items ?? [] as $item) {
            foreach ($item['components'] ?? [] as $component) {
                $option = $component['option'] ?? null;
                if (! is_array($option) || empty($option['id'])) {
                    continue;
                }

                $productId = (string) $option['id'];
                $dbProduct = Product::find($productId);
                if ($dbProduct) {
                    $dbProduct->increment('stock');
                    continue;
                }

                $inventory[$productId] = (int) ($inventory[$productId] ?? 0) + 1;
            }
        }

        $this->saveInventory($inventory);

        $order->status = 'cancelled';
        $order->save();

        return redirect()->route('orders.show', ['id' => $order->id])->with('status', 'ยกเลิกคำสั่งซื้อเรียบร้อยแล้ว');
    }

    public function addProductToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required'],
        ]);

        $product = Product::where('id', $validated['product_id'])
            ->orWhere('sku', $validated['product_id'])
            ->first();

        if (! $product) {
            return back()->with('status', 'ไม่พบสินค้าที่ต้องการ');
        }

        if ($product->stock <= 0) {
            return back()->with('status', 'สินค้าหมดสต็อก');
        }

        $items = $request->session()->get('cart.items', []);
        $items[] = [
            'id' => uniqid('cart_', true),
            'name' => $product->name,
            'components' => [
                [
                    'section' => Product::categories()[$product->category] ?? ucfirst($product->category),
                    'option' => $product->toArray(),
                ],
            ],
            'total' => $product->price,
            'created_at' => now()->format('Y-m-d H:i'),
        ];

        $request->session()->put('cart.items', $items);
        return back()->with('status', 'เพิ่มสินค้าในตะกร้าเรียบร้อยแล้ว');
    }

    public function showCart(Request $request)
    {
        $items = $request->session()->get('cart.items', []);
        $total = array_reduce($items, function($carry, $item) { return $carry + ($item['total'] ?? 0); }, 0);
        $sections = $this->applyCurrentStockToSections($this->specSections());

        // load recent orders for current user to show in cart page
        $orders = [];
        if (Auth::check()) {
            $orders = \App\Models\Order::where(function($q) {
                $q->where('user_id', Auth::id());
                if (Auth::user()->email) {
                    $q->orWhere('email', Auth::user()->email);
                }
            })->orderBy('created_at', 'desc')->limit(6)->get();
        }

        return view('cart', ['items' => $items, 'total' => $total, 'sections' => $sections, 'orders' => $orders]);
    }

    public function addToCart(Request $request)
    {
        // Backwards-compatible wrapper for addProductToCart
        return $this->addProductToCart($request);
    }

    public function removeFromCart(Request $request)
    {
        $id = $request->input('id');
        $items = $request->session()->get('cart.items', []);
        $items = array_values(array_filter($items, function($it) use ($id) { return ($it['id'] ?? null) !== $id; }));
        $request->session()->put('cart.items', $items);
        return back()->with('status', 'ลบรายการในตะกร้าเรียบร้อย');
    }

    public function clearCart(Request $request)
    {
        $request->session()->forget('cart.items');
        return redirect()->route('cart')->with('status', 'ล้างตะกร้าเรียบร้อย');
    }

    public function checkoutCart(Request $request)
    {
        $items = $request->session()->get('cart.items', []);
        if (! count($items)) {
            return redirect()->route('cart')->with('status', 'ไม่มีรายการในตะกร้า');
        }

        $required = [];
        foreach ($items as $item) {
            foreach ($item['components'] as $component) {
                $option = $component['option'] ?? null;
                if (! is_array($option) || empty($option['id'])) {
                    continue;
                }

                $required[(string) $option['id']] = ($required[(string) $option['id']] ?? 0) + 1;
            }
        }

        $total = array_reduce($items, function ($carry, $item) {
            return $carry + ($item['total'] ?? 0);
        }, 0);

        $inventory = $this->loadInventory();
        foreach ($required as $productId => $quantity) {
            $dbProduct = Product::find($productId);
            $availableStock = $dbProduct ? (int) $dbProduct->stock : ($inventory[$productId] ?? null);

            if ($availableStock === null || $availableStock < $quantity) {
                return redirect()->route('cart')->with('status', 'สินค้าบางรายการมีจำนวนเกินสต็อก กรุณาตรวจสอบตะกร้า');
            }
        }

        foreach ($required as $productId => $quantity) {
            $dbProduct = Product::find($productId);
            if ($dbProduct) {
                $dbProduct->decrement('stock', $quantity);
                continue;
            }

            if (isset($inventory[$productId])) {
                $inventory[$productId] = max(0, (int) $inventory[$productId] - $quantity);
            }
        }

        $this->saveInventory($inventory);

        // create order record for admin assignment
        $order = Order::create([
            'user_id' => Auth::id(),
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->phone ?? null,
            'items' => $items,
            'total' => $total,
            'status' => 'new',
            'assigned_to' => null,
            'notes' => null,
        ]);

        // create warranty records per purchased component based on section type
        try {
            $sections = $this->specSections();
            $now = now();
            foreach ($items as $item) {
                foreach ($item['components'] as $component) {
                    $option = $component['option'];
                    // find section key for this product id
                    $sectionKey = null;
                    foreach ($sections as $s) {
                        $match = collect($s['options'])->firstWhere('id', $option['id']);
                        if ($match) { $sectionKey = $s['key']; break; }
                    }

                    // determine warranty rules
                    $warrantyMonths = null;
                    $warrantyExpires = null;
                    $warrantyType = null;

                    if ($sectionKey === 'prebuilt') {
                        // assembled PC -> 1 month
                        $warrantyMonths = 1;
                        $warrantyExpires = $now->copy()->addMonths(1);
                        $warrantyType = 'assembled';
                    } elseif ($sectionKey === 'service' || (isset($option['type']) && $option['type']==='service')) {
                        // services -> no warranty
                        $warrantyMonths = null;
                        $warrantyExpires = null;
                        $warrantyType = 'service';
                    } else {
                        // parts -> 7 days
                        $warrantyMonths = null;
                        $warrantyExpires = $now->copy()->addDays(7);
                        $warrantyType = 'part';
                    }

                    if ($warrantyType !== 'service') {
                        \App\Models\Warranty::create([
                            'user_id' => Auth::id(),
                            'order_id' => $order->id,
                            'product_name' => $option['name'] ?? ($item['name'] ?? 'Unknown'),
                            'product_sku' => $option['id'] ?? null,
                            'serial_number' => null,
                            'purchase_date' => $now,
                            'warranty_months' => $warrantyMonths,
                            'warranty_expires_at' => $warrantyExpires,
                            'warranty_type' => $warrantyType,
                            'notes' => null,
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // failed to create warranties — log and continue
            logger('warranty-create-error: '.$e->getMessage());
        }

        $request->session()->forget('cart.items');

        return redirect()->route('cart')->with('status', 'สั่งซื้อและชำระเงินเรียบร้อยแล้ว');
    }

        public function showSpecSummary()
    {
        $sections = $this->applyCurrentStockToSections($this->specSections());
        return view('specs.summary', ['sections' => $sections]);
    }

    public function showSpecs(string $section)
    {
        $sections = $this->applyCurrentStockToSections($this->specSections());
        $current = collect($sections)->firstWhere('key', $section);
        if (! $current) {
            return redirect()->route('specs');
        }

        $builder = session('spec_builder', []);
        $current_total = 0;
        foreach ($builder as $secKey => $optId) {
            foreach ($sections as $s) {
                if ($s['key'] === $secKey) {
                    $match = collect($s['options'])->firstWhere('id', $optId);
                    if ($match && isset($match['price'])) {
                        $current_total += $match['price'];
                    }
                    break;
                }
            }
        }

        return view('specs.section', [
            'sections' => $sections,
            'section' => $current,
            'builder' => $builder,
            'current_total' => $current_total,
        ]);
    }

    public function submitSpecs(Request $request, string $section)
    {
        $validated = $request->validate([
            'option_id' => ['required', 'string'],
        ]);

        $sections = $this->specSections();
        $keys = array_map(function($s) { return $s['key']; }, $sections);
        $index = array_search($section, $keys, true);
        if ($index === false) {
            return redirect()->route('specs');
        }

        $builder = session('spec_builder', []);
        $builder[$section] = $validated['option_id'];
        session(['spec_builder' => $builder]);

        // if not last section, go to next
        if ($index < count($keys) - 1) {
            $next = $keys[$index + 1];
            return redirect()->route('specs.section', ['section' => $next])->with('status', 'เลือกเรียบร้อยแล้ว');
        }

        // last section -> build composite item and add to cart
        $sectionsFull = $this->applyCurrentStockToSections($this->specSections());
        $components = [];
        $total = 0;
        foreach ($builder as $secKey => $optId) {
            $sec = collect($sectionsFull)->firstWhere('key', $secKey);
            $opt = null;
            if ($sec) {
                $opt = collect($sec['options'])->firstWhere('id', $optId);
            }
            if (! $opt) {
                // invalid selection, redirect back to summary
                return redirect()->route('specs.summary')->with('status', 'มีการเลือกที่ไม่ถูกต้อง กรุณาเลือกใหม่');
            }

            $components[] = [
                'section' => $sec['title'] ?? $secKey,
                'option' => $opt,
            ];

            $total += isset($opt['price']) ? $opt['price'] : 0;
        }

        $items = session()->get('cart.items', []);
        $itemName = 'สเปค: ' . implode(', ', array_map(function ($component) {
            return $component['section'] . ' ' . ($component['option']['name'] ?? '');
        }, $components));

        $items[] = [
            'id' => uniqid('cart_', true),
            'name' => $itemName,
            'components' => $components,
            'total' => $total,
            'created_at' => now()->format('Y-m-d H:i'),
        ];

        session()->put('cart.items', $items);
        session()->forget('spec_builder');

        return redirect()->route('cart')->with('status', 'สเปคถูกเพิ่มลงตะกร้าเรียบร้อยแล้ว');
    }

    public function redirectToFirstSpec()
    {
        $sections = $this->specSections();
        $first = collect($sections)->first();
        if (! $first || ! isset($first['key'])) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('specs.section', ['section' => $first['key']]);
    }

    private function specSections()
    {
        $sections = [
            [
                'key' => 'cpu',
                'title' => 'CPU',
                'description' => 'เลือกหน่วยประมวลผล (CPU) ที่เหมาะกับการใช้งานของคุณ — คำนึงถึงจำนวนคอร์/เธรด ความเร็วสัญญาณนาฬิกา และความร้อนเมื่อต้องโอเวอร์คล็อก',
                'options' => [
                    [
                        'id' => 'intel-i5-14600k',
                        'name' => 'Intel Core i5-14600K',
                        'details' => 'ซีพียูระดับกลางที่ให้ความเร็วแรงในงานเกมและงานสร้างคอนเทนต์เบื้องต้น เหมาะสำหรับผู้ที่ต้องการประสิทธิภาพต่อราคาที่ดี',
                        'price' => 8900,
                        'stock' => 8,
                        'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                        'cores' => 14,
                        'threads' => 20,
                        'base_clock' => '3.5 GHz',
                        'boost_clock' => '5.3 GHz',
                        'tdp' => '125W',
                        'socket' => 'LGA1700',
                        'cache' => '24MB',
                    ],
                    [
                        'id' => 'intel-i7-14700f',
                        'name' => 'Intel Core i7-14700F',
                        'details' => 'ซีพียูสำหรับการทำงานหนักและครีเอเตอร์ ให้ประสิทธิภาพหลายเธรดที่ดี เหมาะสำหรับการเรนเดอร์ และงานพร้อมกันหลายแอป',
                        'price' => 11800,
                        'stock' => 4,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'cores' => 20,
                        'threads' => 28,
                        'base_clock' => '2.5 GHz',
                        'boost_clock' => '5.4 GHz',
                        'tdp' => '65W',
                        'socket' => 'LGA1700',
                        'cache' => '30MB',
                    ],
                    [
                        'id' => 'amd-ryzen-5-7600x',
                        'name' => 'AMD Ryzen 5 7600X',
                        'details' => 'ซีพียู Ryzen ที่ให้ความคุ้มค่า เน้นประสิทธิภาพต่อราคาดี เหมาะกับเกมเมอร์ที่ต้องการเฟรมเรตสูง',
                        'price' => 7300,
                        'stock' => 6,
                        'image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                        'cores' => 6,
                        'threads' => 12,
                        'base_clock' => '4.7 GHz',
                        'boost_clock' => '5.3 GHz',
                        'tdp' => '105W',
                        'socket' => 'AM5',
                        'cache' => '32MB',
                    ],
                ],
            ],

            [
                'key' => 'motherboard',
                'title' => 'Motherboard',
                'description' => 'เมนบอร์ดเป็นหัวใจของระบบ — เลือกชิปเซ็ตและสลอตที่รองรับ CPU, RAM และการ์ดจอที่คุณต้องการ รวมถึงพอร์ตเชื่อมต่อและการรองรับ M.2/NVMe',
                'options' => [
                    [
                        'id' => 'b660-gaming',
                        'name' => 'Intel B660 Gaming',
                        'details' => 'เมนบอร์ดงบกลางที่รองรับฟังก์ชันพื้นฐาน มีสล็อต M.2 สำหรับ NVMe และพอร์ต USB ครบถ้วน',
                        'price' => 4200,
                        'stock' => 6,
                        'image' => 'https://images.unsplash.com/photo-1548092372-0a8d6a764a88?auto=format&fit=crop&w=800&q=80',
                        'chipset' => 'B660',
                        'socket' => 'LGA1700',
                        'form_factor' => 'ATX',
                        'mem_slots' => 4,
                        'max_mem' => '128GB',
                        'm2_slots' => 2,
                    ],
                    [
                        'id' => 'z790-pro',
                        'name' => 'Intel Z790 Pro',
                        'details' => 'เมนบอร์ดระดับสูงสำหรับผู้ที่ต้องการฟีเจอร์เพิ่มเติม เช่น การโอเวอร์คล็อก และการเชื่อมต่อความเร็วสูง',
                        'price' => 7600,
                        'stock' => 3,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'chipset' => 'Z790',
                        'socket' => 'LGA1700',
                        'form_factor' => 'ATX',
                        'mem_slots' => 4,
                        'max_mem' => '256GB',
                        'm2_slots' => 3,
                    ],
                    [
                        'id' => 'b550-aorus',
                        'name' => 'AMD B550 Aorus',
                        'details' => 'เมนบอร์ดสำหรับแพลตฟอร์ม Ryzen ที่ให้ความเสถียร รองรับ PCIe และ NVMe เหมาะกับสเปคระดับกลาง',
                        'price' => 3900,
                        'stock' => 7,
                        'image' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80',
                        'chipset' => 'B550',
                        'socket' => 'AM5',
                        'form_factor' => 'ATX',
                        'mem_slots' => 4,
                        'max_mem' => '128GB',
                        'm2_slots' => 2,
                    ],
                ],
            ],

            [
                'key' => 'gpu',
                'title' => 'GPU',
                'description' => 'การ์ดจอกำหนดประสบการณ์เกมและงานกราฟิก — พิจารณาความจำวิดีโอ (VRAM) และประสิทธิภาพตามระดับความต้องการของคุณ',
                'options' => [
                    [
                        'id' => 'nvidia-rtx-4060-ti',
                        'name' => 'NVIDIA RTX 4060 Ti',
                        'details' => 'การ์ดจอสำหรับเล่นเกม AAA ที่ตั้งค่ากลาง-สูง ให้ประสิทธิภาพต่อราคาเหมาะสำหรับเกมเมอร์และผู้สร้างคอนเทนต์',
                        'price' => 13900,
                        'stock' => 4,
                        'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                        'vram' => '8GB',
                        'tdp' => '160W',
                        'length_mm' => 242,
                        'outputs' => ['HDMI','DP'],
                    ],
                    [
                        'id' => 'nvidia-rtx-4070',
                        'name' => 'NVIDIA RTX 4070',
                        'details' => 'ประสิทธิภาพสูงขึ้น เหมาะสำหรับการเล่นเกมที่ตั้งค่าสูงและงานเรนเดอร์กราฟิก รองรับการทำงานร่วมกับจอความละเอียดสูง',
                        'price' => 20900,
                        'stock' => 2,
                        'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                        'vram' => '12GB',
                        'tdp' => '200W',
                        'length_mm' => 267,
                        'outputs' => ['HDMI','DP'],
                    ],
                    [
                        'id' => 'integrated-graphics',
                        'name' => 'Integrated Graphics',
                        'details' => 'กราฟิกในตัวที่เพียงพอสำหรับงานทั่วไป เช่น ท่องเว็บ ดูหนัง และงานเอกสาร แต่ไม่แนะนำสำหรับเกมหนักหรืองานกราฟิกขั้นสูง',
                        'price' => 0,
                        'stock' => 30,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'vram' => 'Shared',
                        'tdp' => 'N/A',
                        'outputs' => ['HDMI','DP'],
                    ],
                ],
            ],

            [
                'key' => 'ram',
                'title' => 'RAM',
                'description' => 'หน่วยความจำสำคัญสำหรับประสิทธิภาพเมื่อรันหลายโปรแกรมพร้อมกัน — เลือกความจุและความเร็วให้ตรงกับงานของคุณ',
                'options' => [
                    [
                        'id' => '16gb-ddr5',
                        'name' => '16GB (2x8GB) DDR5 5600MHz',
                        'details' => 'เหมาะสำหรับเกมทั่วไปและการใช้งานประจำวัน หากไม่รันงานตัดต่อขนาดใหญ่ 16GB ถือว่าเพียงพอ',
                        'price' => 1800,
                        'stock' => 12,
                        'image' => 'https://images.unsplash.com/photo-1548092372-0a8d6a764a88?auto=format&fit=crop&w=800&q=80',
                        'type' => 'DDR5',
                        'speed' => '5600MHz',
                        'modules' => '2x8GB',
                    ],
                    [
                        'id' => '32gb-ddr5',
                        'name' => '32GB (2x16GB) DDR5 5600MHz',
                        'details' => 'สมดุลทั้งเกมและงานสร้างคอนเทนต์ สามารถรันแอปพลิเคชันหลายตัวพร้อมกันและทำงานตัดต่อระดับกลางได้สบาย',
                        'price' => 3200,
                        'stock' => 8,
                        'image' => 'https://images.unsplash.com/photo-1548092372-0a8d6a764a88?auto=format&fit=crop&w=800&q=80',
                        'type' => 'DDR5',
                        'speed' => '5600MHz',
                        'modules' => '2x16GB',
                    ],
                    [
                        'id' => '64gb-ddr5',
                        'name' => '64GB (2x32GB) DDR5 5600MHz',
                        'details' => 'เหมาะสำหรับงานหนัก เช่น งานเรนเดอร์ 3D, ตัดต่อวิดีโอความละเอียดสูง หรือการใช้งานในสถานการณ์ที่ต้องรันหลาย VM/โปรแกรมพร้อมกัน',
                        'price' => 6200,
                        'stock' => 5,
                        'image' => 'https://images.unsplash.com/photo-1548092372-0a8d6a764a88?auto=format&fit=crop&w=800&q=80',
                        'type' => 'DDR5',
                        'speed' => '5600MHz',
                        'modules' => '2x32GB',
                    ],
                ],
            ],

            [
                'key' => 'storage',
                'title' => 'Storage',
                'description' => 'เลือก SSD แบบ NVMe สำหรับความเร็วในการบูตและโหลดโปรแกรม เกมจะโหลดเร็วขึ้นเมื่อใช้ NVMe',
                'options' => [
                    [
                        'id' => '512gb-nvme',
                        'name' => '512GB NVMe SSD',
                        'details' => 'ความจุพอสำหรับระบบปฏิบัติการและโปรแกรมหลัก เหมาะสำหรับผู้ที่มีเกมไม่มากนัก',
                        'price' => 1200,
                        'stock' => 10,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'interface' => 'PCIe NVMe',
                        'capacity' => '512GB',
                        'read' => '3500 MB/s',
                        'write' => '3000 MB/s',
                    ],
                    [
                        'id' => '1tb-nvme',
                        'name' => '1TB NVMe SSD',
                        'details' => 'ความจุที่เหมาะสำหรับผู้เล่นเกมปริมาณมากและครีเอเตอร์ที่ต้องการพื้นที่เก็บไฟล์โปรเจกต์',
                        'price' => 2100,
                        'stock' => 7,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'interface' => 'PCIe NVMe',
                        'capacity' => '1TB',
                        'read' => '7000 MB/s',
                        'write' => '5000 MB/s',
                    ],
                    [
                        'id' => '2tb-nvme',
                        'name' => '2TB NVMe SSD',
                        'details' => 'สำหรับผู้ที่เก็บเกมและคอนเทนต์จำนวนมาก ต้องการพื้นที่ความจุสูงพร้อมความเร็วในการอ่านเขียนที่ดี',
                        'price' => 3900,
                        'stock' => 3,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'interface' => 'PCIe NVMe',
                        'capacity' => '2TB',
                        'read' => '7000 MB/s',
                        'write' => '6500 MB/s',
                    ],
                ],
            ],

            [
                'key' => 'prebuilt',
                'title' => 'คอมเซ็ต',
                'description' => 'ชุดคอมพิวเตอร์สำเร็จรูป แบ่งเป็น AMD และ Intel — แต่ละชุดรวมชิ้นส่วนพื้นฐาน เช่น CPU / เมนบอร์ด / RAM / Storage / PSU / เคส',
                'options' => [
                    [
                        'id' => 'prebuilt-amd',
                        'name' => 'คอมเซ็ต AMD',
                        'details' => 'ชุดคอมพร้อมใช้งานติดตั้งด้วย CPU AMD ประสิทธิภาพดีสำหรับเกมและงานทั่วไป ประกอบด้วยซีพียู Ryzen, เมนบอร์ดที่รองรับ, RAM ขนาดเหมาะสม, NVMe SSD และ PSU ที่มีประสิทธิภาพ',
                        'price' => 45900,
                        'stock' => 5,
                        'components' => [
                            'cpu' => 'amd-ryzen-5-7600x',
                            'motherboard' => 'b550-aorus',
                            'ram' => '16gb-ddr5',
                            'storage' => '1tb-nvme',
                            'gpu' => 'nvidia-rtx-4060-ti',
                            'psu' => '650w-gold',
                        ],
                        'image' => 'https://images.unsplash.com/photo-1587202372775-1f46b6b2d0f2?auto=format&fit=crop&w=800&q=80',
                    ],
                    [
                        'id' => 'prebuilt-intel',
                        'name' => 'คอมเซ็ต Intel',
                        'details' => 'ชุดคอมที่เน้นประสิทธิภาพ ใช้ CPU Intel ประสิทธิภาพสูง เหมาะสำหรับผู้ใช้ที่ต้องการความแรงในงานเกมและการทำงานด้านคอนเทนต์',
                        'price' => 49900,
                        'stock' => 4,
                        'components' => [
                            'cpu' => 'intel-i7-14700f',
                            'motherboard' => 'z790-pro',
                            'ram' => '32gb-ddr5',
                            'storage' => '1tb-nvme',
                            'gpu' => 'nvidia-rtx-4070',
                            'psu' => '750w-gold',
                        ],
                        'image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=800&q=80',
                    ],
                ],
            ],

            [
                'key' => 'psu',
                'title' => 'PSU',
                'description' => 'เพาเวอร์ซัพพลายคือหัวใจของความเสถียร — เลือกวัตต์และมาตรฐานประสิทธิภาพ (เช่น Gold/Platinum) ให้เหมาะกับการ์ดจอและการอัพเกรด',
                'options' => [
                    [
                        'id' => '650w-gold',
                        'name' => '650W Gold',
                        'details' => 'เหมาะสำหรับสเปคกลางที่มีการ์ดจอระดับกลาง รองรับการอัพเกรดเล็กน้อยและให้ประสิทธิภาพ/ความประหยัดพลังงานที่ดี',
                        'price' => 1900,
                        'stock' => 9,
                        'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
                        'efficiency' => '80+ Gold',
                        'modular' => 'Semi-Modular',
                        'wattage' => '650W',
                    ],
                    [
                        'id' => '750w-gold',
                        'name' => '750W Gold',
                        'details' => 'พลังสำรองมากขึ้น เหมาะสำหรับผู้ที่วางแผนจะอัพเกรดการ์ดจอหรือ CPU ในอนาคต',
                        'price' => 2500,
                        'stock' => 5,
                        'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                        'efficiency' => '80+ Gold',
                        'modular' => 'Full-Modular',
                        'wattage' => '750W',
                    ],
                    [
                        'id' => '850w-platinum',
                        'name' => '850W Platinum',
                        'details' => 'มาตรฐานสูงสุดสำหรับความเสถียรและประสิทธิภาพ เหมาะกับสเปคเครื่องระดับสูงหรือเซิร์ฟเวอร์ขนาดเล็ก',
                        'price' => 3700,
                        'stock' => 4,
                        'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80',
                        'efficiency' => '80+ Platinum',
                        'modular' => 'Full-Modular',
                        'wattage' => '850W',
                    ],
                ],
            ],
        ];

        // remove prebuilt/comset from spec builder
        $sections = array_values(array_filter($sections, function($s) { return ($s['key'] ?? '') !== 'prebuilt'; }));
        return $sections;
    }

    // inventory helpers (file-based inventory in storage/app/inventory.json)
    private function inventoryPath()
    {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'inventory.json');
    }

    private function buildInitialInventory()
    {
        $sections = $this->specSections();
        $inventory = [
            'prebuilt-amd' => 5,
            'prebuilt-intel' => 4,
        ];

        foreach ($sections as $section) {
            if (! isset($section['options']) || ! is_array($section['options'])) {
                continue;
            }
            foreach ($section['options'] as $opt) {
                $inventory[$opt['id']] = $opt['stock'] ?? 0;
            }
        }
        return $inventory;
    }

    private function loadInventory()
    {
        $path = $this->inventoryPath();
        if (! file_exists($path)) {
            $initial = $this->buildInitialInventory();
            $this->saveInventory($initial);
            return $initial;
        }

        $json = file_get_contents($path);
        $data = json_decode($json, true);
        if (! is_array($data)) {
            $data = $this->buildInitialInventory();
            $this->saveInventory($data);
        }
        return $data;
    }

    private function saveInventory(array $inventory)
    {
        $path = $this->inventoryPath();
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($inventory, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    private function applyCurrentStockToSections(array $sections)
    {
        $inventory = $this->loadInventory();
        foreach ($sections as &$section) {
            if (! isset($section['options']) || ! is_array($section['options'])) {
                continue;
            }
            foreach ($section['options'] as &$opt) {
                $opt['stock'] = $inventory[$opt['id']] ?? ($opt['stock'] ?? 0);
            }
            unset($opt);
        }
        unset($section);
        return $sections;
    }

    private function decrementInventory(array $required)
    {
        $inventory = $this->loadInventory();
        foreach ($required as $id => $qty) {
            $inventory[$id] = max(0, ($inventory[$id] ?? 0) - (int) $qty);
        }
        $this->saveInventory($inventory);
    }
}
