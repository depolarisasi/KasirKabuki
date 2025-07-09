<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StafController;
use Illuminate\Support\Facades\Log;

// Root route - redirect directly to PIN login as default
Route::get('/', function () {
    return redirect()->route('pin-login');
})->name('home');

// Authentication Routes (Breeze will handle these)
require __DIR__.'/auth.php';

// PIN Login Route - Available for guest users
Route::get('/pin-login', App\Livewire\PinLogin::class)
    ->middleware('guest')->name('pin-login');
 

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
         
        // Redirect based on user role
        if ($user->hasRole('admin')) {
            Log::info('Redirecting admin user to admin.dashboard');
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staf')) {
            Log::info('Redirecting staff user to staf.cashier');
            return redirect()->route('staf.cashier');
        } elseif ($user->hasRole('investor')) {
            Log::info('Redirecting investor user to investor.dashboard');
            return redirect()->route('investor.dashboard');
        }
        
        // Enhanced fallback - try using role column as backup
        if (isset($user->role)) {
            if ($user->role === 'admin') {
                Log::warning('Using fallback: role column shows admin, redirecting to admin.dashboard');
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staf') {
                Log::warning('Using fallback: role column shows staf, redirecting to staf.cashier');
                return redirect()->route('staf.cashier');
            } elseif ($user->role === 'investor') {
                Log::warning('Using fallback: role column shows investor, redirecting to investor.dashboard');
                return redirect()->route('investor.dashboard');
            }
        }
        
        // Last resort fallback - tidak redirect ke login
        Log::error('No valid role found for user, showing error message', [
            'user_id' => $user->id,
            'user_role_column' => $user->role ?? 'NULL',
            'spatie_roles' => $user->roles ? $user->roles->pluck('name')->toArray() : []
        ]);
        
        return view('errors.no-role')->with([
            'user' => $user,
            'message' => 'Role pengguna tidak dikenali. Silakan hubungi administrator.'
        ]);
    })->name('dashboard');
});

// Admin Routes - Protected by auth and admin role
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/config', [AdminController::class, 'config'])->name('config');
    Route::get('/store-config', [AdminController::class, 'storeConfig'])->name('store-config');
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    
    // Management Pages
    Route::get('/categories', [AdminController::class, 'categories'])->name('categories');
    Route::get('/products', [AdminController::class, 'products'])->name('products');
    Route::get('/partners', [AdminController::class, 'partners'])->name('partners');
    Route::get('/discounts', [AdminController::class, 'discounts'])->name('discounts');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
     
});

// Admin Reports - Accessible by admin and investor
Route::middleware(['auth', 'role:admin|investor'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports/sales', [AdminController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/expenses', [AdminController::class, 'expensesReport'])->name('reports.expenses');
    Route::get('/reports/stock', [AdminController::class, 'stockReport'])->name('reports.stock');
});

// Receipt and Print Routes - Accessible by staff and admin 
    Route::get('/receipt/{transaction}', [StafController::class, 'receiptPrint'])->name('receipt.print');
    Route::get('/android-print/{transaction}', [StafController::class, 'androidPrintResponse'])->name('android.print.response');
 

// Admin Test Print Routes - Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/test-receipt', [AdminController::class, 'testReceipt'])->name('test-receipt');
    Route::get('/android-test-print', [AdminController::class, 'androidTestPrint'])->name('android.test.print');
});

// Debug route for Android print testing
Route::get('/debug-android-print', function() {
    // Test format persis seperti instruksi
    $a = array();
    
    $obj = new stdClass();
    $obj->type = 0;
    $obj->content = "DEBUG TEST STORE";
    $obj->bold = 1;
    $obj->align = 1;
    $obj->format = 2;
    array_push($a, $obj);
    
    $obj2 = new stdClass();
    $obj2->type = 0;
    $obj2->content = "Debug Address";
    $obj2->bold = 0;
    $obj2->align = 1;
    $obj2->format = 0;
    array_push($a, $obj2);
    
    // Output persis seperti instruksi dokumentasi
    $jsonContent = json_encode($a, JSON_FORCE_OBJECT);
    
    // Log untuk debugging
    \Log::info('Debug Android Print', [
        'json_output' => $jsonContent,
        'content_length' => strlen($jsonContent),
        'array_count' => count($a)
    ]);
    
    return response($jsonContent, 200)
        ->header('Content-Type', 'application/json')
        ->header('Content-Length', strlen($jsonContent));
});

// Endpoint test sederhana untuk Bluetooth Print
Route::get('/simple-print-test', function() {
    try {
        $a = array();
        
        // Test object sederhana
        $obj1 = new stdClass();
        $obj1->type = 0;
        $obj1->content = "TEST PRINT";
        $obj1->bold = 1;
        $obj1->align = 1;
        $obj1->format = 2;
        array_push($a, $obj1);
        
        $obj2 = new stdClass();
        $obj2->type = 0;
        $obj2->content = "Simple test line";
        $obj2->bold = 0;
        $obj2->align = 0;
        $obj2->format = 0;
        array_push($a, $obj2);
        
        // Exact format
        $json = json_encode($a, JSON_FORCE_OBJECT);
        
        \Log::info('Simple Print Test', [
            'output' => $json,
            'size' => strlen($json)
        ]);
        
        return response($json, 200)
            ->header('Content-Type', 'application/json; charset=utf-8')
            ->header('Content-Length', strlen($json))
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
            
    } catch (\Exception $e) {
        \Log::error('Simple Print Test Error', ['error' => $e->getMessage()]);
        return response('{"error":"Failed to generate test print"}', 500)
            ->header('Content-Type', 'application/json');
    }
});

// Staff Routes - Protected by auth and staf or admin role (investor removed from general access)
Route::middleware(['auth', 'role:staf|admin'])->prefix('staf')->name('staf.')->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('staf.cashier');
    })->name('dashboard');
    
    // Main Staff Interfaces
    Route::get('/cashier', [StafController::class, 'cashier'])->name('cashier');
    Route::get('/stock', function () {
        return redirect()->route('staf.stock-sate');
    })->name('stock');
    
    // Transaction Management
    Route::get('transactions', function () {
        return view('staf.transactions.index');
    })->name('transactions');
    Route::get('/transactions/{transaction}', [StafController::class, 'transactionDetail'])->name('transactions.show');
     
});

// Specific Staff Routes - Accessible by staff, admin, and investor
Route::middleware(['auth', 'role:staf|admin|investor'])->prefix('staf')->name('staf.')->group(function () {
    Route::get('/stock-sate', [StafController::class, 'stockSate'])->name('stock-sate');
    Route::get('/expenses', [StafController::class, 'expenses'])->name('expenses');
});

// Investor Routes - Redirect to first accessible page (admin reports sales)
Route::middleware(['auth', 'role:investor'])->prefix('investor')->name('investor.')->group(function () {
    Route::get('/dashboard', function () {
        // Redirect investor to first accessible page instead of dedicated dashboard
        return redirect()->route('admin.reports.sales');
    })->name('dashboard');
});

// Breeze Profile Route (keep for user management)
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');
 