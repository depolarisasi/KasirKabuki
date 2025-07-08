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
    
    // Test Receipt Route
    Route::get('/test-receipt', [AdminController::class, 'testReceipt'])->name('test-receipt');
    
    // Reports
    Route::get('/reports/sales', [AdminController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/expenses', [AdminController::class, 'expensesReport'])->name('reports.expenses');
    Route::get('/reports/stock', [AdminController::class, 'stockReport'])->name('reports.stock');
});

// Staff Routes - Protected by auth and staf or admin role
Route::middleware(['auth', 'role:staf|admin|investor'])->prefix('staf')->name('staf.')->group(function () {
    // Dashboard redirect
    Route::get('/dashboard', function () {
        return redirect()->route('staf.cashier');
    })->name('dashboard');
    
    // Main Staff Interfaces
    Route::get('/cashier', [StafController::class, 'cashier'])->name('cashier');
    Route::get('/stock', [StafController::class, 'stock'])->name('stock');
    Route::get('/expenses', [StafController::class, 'expenses'])->name('expenses');
    
    // Transaction Management
    
    Route::get('transactions', App\Livewire\TransactionPageComponent::class)->name('transactions');

    Route::get('/transactions/{transaction}', [StafController::class, 'transactionDetail'])->name('transactions.show');
    
    // Receipt Print Route
    Route::get('/receipt/{transaction}', [StafController::class, 'receiptPrint'])->name('receipt.print');
});

// Investor Routes - Protected by auth and investor role
Route::middleware(['auth', 'role:investor'])->prefix('investor')->name('investor.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\InvestorController::class, 'dashboard'])->name('dashboard');
    
    // Reports - Read-only access
    Route::get('/reports/sales', [App\Http\Controllers\InvestorController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/expenses', [App\Http\Controllers\InvestorController::class, 'expensesReport'])->name('reports.expenses');
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
