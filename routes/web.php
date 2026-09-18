<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

Route::get('/', function () {
    return redirect()->route('welcome');
});

Route::get('/home', function () {
    return view('home');
})->name('home');

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

// Dev-only user creation endpoint (local testing). Protected by DEV_CREATE_KEY in .env
if (env('APP_ENV') === 'local') {
    Route::post('/dev/create-user', function (Request $request) {
        $key = env('DEV_CREATE_KEY');
        if (! $key || $request->header('X-DEV-KEY') !== $key) {
            abort(403, 'Forbidden');
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        auth()->login($user);
        $request->session()->regenerate();
        return response()->json(['status' => 'created', 'user_id' => $user->id]);
    })->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
}

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/repair-request', [AuthController::class, 'showRepairRequest'])->name('repair.request');
    Route::get('/products/{section}', [AuthController::class, 'showProductCategory'])->name('products.category');
    // direct vendor pages for prebuilt sets
    Route::get('/products/prebuilt/{vendor}', [AuthController::class, 'showPrebuiltVendor'])->name('products.prebuilt.vendor');
    Route::post('/cart/add-product', [AuthController::class, 'addProductToCart'])->name('cart.add.product');
    Route::get('/specs', [AuthController::class, 'redirectToFirstSpec'])->name('specs');
    Route::get('/specs/summary', [AuthController::class, 'showSpecSummary'])->name('specs.summary');
    Route::get('/specs/{section}', [AuthController::class, 'showSpecs'])->name('specs.section');
    Route::post('/specs/{section}', [AuthController::class, 'submitSpecs'])->name('specs.submit');
    Route::get('/cart', [AuthController::class, 'showCart'])->name('cart');
    Route::post('/cart/add', [AuthController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/remove', [AuthController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/clear', [AuthController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/checkout', [AuthController::class, 'checkoutCart'])->name('cart.checkout');
    Route::get('/warranty', [AuthController::class, 'showWarranty'])->name('warranty');
    Route::get('/products/item/{id}', [AuthController::class, 'showProductDetail'])->name('products.item');

    // customer orders
    Route::get('/orders', [AuthController::class, 'ordersIndex'])->name('orders.index');
    Route::get('/orders/{id}', [AuthController::class, 'showOrderForUser'])->name('orders.show');
    Route::post('/orders/{id}/cancel', [AuthController::class, 'cancelOrder'])->name('orders.cancel');

    // Repair request (full system)
    Route::get('/repairs', [\App\Http\Controllers\RepairRequestController::class, 'index'])->name('repairs.index');
    Route::get('/repairs/create', [\App\Http\Controllers\RepairRequestController::class, 'create'])->name('repairs.create');
    Route::post('/repairs', [\App\Http\Controllers\RepairRequestController::class, 'store'])->name('repairs.store');
    Route::get('/repairs/{id}', [\App\Http\Controllers\RepairRequestController::class, 'show'])->name('repairs.show');
    Route::post('/repairs/{id}/status', [\App\Http\Controllers\RepairRequestController::class, 'updateStatus'])->name('repairs.update_status');
    Route::post('/repairs/{id}/accept', [\App\Http\Controllers\RepairQuoteController::class, 'accept'])->name('repairs.accept');
    Route::get('/repairs/{id}/download/{index}', [\App\Http\Controllers\RepairRequestController::class, 'downloadAttachment'])->name('repairs.download');

    // AI Chat (local LLM)
    Route::get('/ai/chat', [\App\Http\Controllers\AiChatController::class, 'show'])->name('ai.chat');
    Route::post('/ai/chat/message', [\App\Http\Controllers\AiChatController::class, 'send'])->name('ai.chat.message');
});

// Technician routes: require standard auth + technician role
Route::post('/technician/logout', [\App\Http\Controllers\TechnicianController::class, 'logout'])->name('technician.logout');

Route::middleware(['auth', \App\Http\Middleware\TechnicianAccess::class])->group(function () {
    Route::get('/technician', [\App\Http\Controllers\TechnicianController::class, 'dashboard'])->name('technician.dashboard');
    Route::get('/technician/repair/{id}', [\App\Http\Controllers\TechnicianController::class, 'showRepair'])->name('technician.repair.show');
    Route::post('/technician/repair/{id}/updates', [\App\Http\Controllers\RepairUpdateController::class, 'store'])->name('technician.repair.update');
    Route::get('/repairs/{repairId}/updates/{updateId}/download/{index}', [\App\Http\Controllers\RepairUpdateController::class, 'downloadUpdateAttachment'])->name('repairs.update.download');

    // technician order routes
    Route::get('/technician/order/{id}', [\App\Http\Controllers\TechnicianController::class, 'showOrder'])->name('technician.order.show');
    Route::post('/technician/order/{id}/upload', [\App\Http\Controllers\TechnicianController::class, 'uploadOrderClip'])->name('technician.order.upload');
    Route::post('/technician/order/{id}/dispatch', [\App\Http\Controllers\TechnicianController::class, 'dispatchOrder'])->name('technician.order.dispatch');
});

// Admin area (requires normal auth + admin role)
Route::middleware(['auth', \App\Http\Middleware\AdminAccess::class])->group(function () {
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/repairs/{id}', [\App\Http\Controllers\AdminController::class, 'showRepair'])->name('admin.repairs.show');
    Route::post('/admin/repairs/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assign'])->name('admin.repairs.assign');
    Route::post('/admin/repairs/{id}/quote', [\App\Http\Controllers\AdminController::class, 'sendQuote'])->name('admin.repairs.quote');
    
    Route::get('/admin/warranty', [\App\Http\Controllers\AdminController::class, 'warrantySearch'])->name('admin.warranty');

    Route::get('/admin/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::post('/admin/users/{id}/role', [\App\Http\Controllers\AdminController::class, 'updateUserRole'])->name('admin.users.role');

    // Admin product management with category support
    Route::get('/admin/products', [\App\Http\Controllers\AdminController::class, 'products'])->name('admin.products');
    Route::post('/admin/products', [\App\Http\Controllers\AdminController::class, 'addProduct'])->name('admin.products.add');
    Route::post('/admin/products/{id}/remove', [\App\Http\Controllers\AdminController::class, 'removeProduct'])->name('admin.products.remove');

    Route::get('/admin/orders', [\App\Http\Controllers\AdminController::class, 'orders'])->name('admin.orders.index');
    Route::get('/admin/orders/{id}', [\App\Http\Controllers\AdminController::class, 'showOrder'])->name('admin.orders.show');
    Route::post('/admin/orders/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignOrder'])->name('admin.orders.assign');
    Route::get('/admin/orders/{id}', [\App\Http\Controllers\AdminController::class, 'showOrder'])->name('admin.orders.show');
    Route::post('/admin/orders/{id}/assign', [\App\Http\Controllers\AdminController::class, 'assignOrder'])->name('admin.orders.assign');
});