<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StafController;
use Illuminate\Support\Facades\Log;

// Root route - redirect directly to login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Authentication Routes (Breeze will handle these)
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Debug logging untuk troubleshoot role issues
        Log::info('Dashboard Route Debug', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_role_column' => $user->role ?? 'NULL',
            'hasRole_admin' => $user->hasRole('admin'),
            'hasRole_staf' => $user->hasRole('staf'),
            'all_roles' => $user->roles ? $user->roles->pluck('name')->toArray() : [],
            'roles_count' => $user->roles ? $user->roles->count() : 0
        ]);
        
        // Redirect based on user role
        if ($user->hasRole('admin')) {
            Log::info('Redirecting admin user to admin.dashboard');
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('staf')) {
            Log::info('Redirecting staff user to staf.cashier');
            return redirect()->route('staf.cashier');
        }
        
        // Enhanced fallback - try using role column as backup
        if (isset($user->role)) {
            if ($user->role === 'admin') {
                Log::warning('Using fallback: role column shows admin, redirecting to admin.dashboard');
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'staf') {
                Log::warning('Using fallback: role column shows staf, redirecting to staf.cashier');
                return redirect()->route('staf.cashier');
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
    
    // Test Receipt Route
    Route::get('/test-receipt', [AdminController::class, 'testReceipt'])->name('test-receipt');
    
    // Reports
    Route::get('/reports/sales', [AdminController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/expenses', [AdminController::class, 'expensesReport'])->name('reports.expenses');
    Route::get('/reports/stock', [AdminController::class, 'stockReport'])->name('reports.stock');
});

// Staff Routes - Protected by auth and staf or admin role
Route::middleware(['auth', 'role:staf|admin'])->prefix('staf')->name('staf.')->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('staf.cashier');
    })->name('dashboard');
    
    // Main Staff Interfaces
    Route::get('/cashier', [StafController::class, 'cashier'])->name('cashier');
    Route::get('/stock', [StafController::class, 'stock'])->name('stock');
    Route::get('/expenses', [StafController::class, 'expenses'])->name('expenses');
    
    // Transaction Management
    Route::get('/transactions/{transaction}', [StafController::class, 'transactionDetail'])->name('transactions.show');
    
    // Receipt Print Route
    Route::get('/receipt/{transaction}', [StafController::class, 'receiptPrint'])->name('receipt.print');
});

// Breeze Profile Route (keep for user management)
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/test-toaster', function () {
    \Masmerise\Toaster\Toaster::success('Official Toaster is working!');
    \Masmerise\Toaster\Toaster::info('Info message test');
    \Masmerise\Toaster\Toaster::warning('Warning message test');
    \Masmerise\Toaster\Toaster::error('Error message test');
    
    return view('welcome');
})->name('test.toaster');
