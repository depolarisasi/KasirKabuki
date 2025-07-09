<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'pin',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logException($e);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            return $this->renderCustomException($e, $request);
        });
    }

    /**
     * Log exception with standardized format
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'user_id' => auth()->id(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
        ];

        // Add Livewire context if available
        if (class_exists('\Livewire\Livewire') && \Livewire\Livewire::isLivewireRequest()) {
            $context['livewire_component'] = request()->header('X-Livewire-Component');
            $context['livewire_method'] = request()->header('X-Livewire-Method');
        }

        Log::error('Application Exception', $context);
    }

    /**
     * Render custom exception responses
     */
    protected function renderCustomException(Throwable $e, Request $request)
    {
        // Handle AJAX/Livewire requests
        if ($request->wantsJson() || $request->header('X-Livewire')) {
            return $this->renderJsonException($e, $request);
        }

        // Handle specific exception types
        if ($e instanceof BusinessException) {
            return $this->handleBusinessException($e, $request);
        }

        if ($e instanceof \Illuminate\Database\QueryException) {
            return $this->handleDatabaseException($e, $request);
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return $this->handleValidationException($e, $request);
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return $this->handleNotFoundException($e, $request);
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->handleAuthenticationException($e, $request);
        }

        if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return $this->handleUnauthorizedException($e, $request);
        }

        // Default handling
        return null;
    }

    /**
     * Render JSON exception response
     */
    protected function renderJsonException(Throwable $e, Request $request)
    {
        $status = $this->getExceptionStatusCode($e);
        $message = $this->getUserFriendlyMessage($e);

        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => get_class($e),
            'debug' => config('app.debug') ? [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ] : null,
        ], $status);
    }

    /**
     * Handle business logic exceptions
     */
    protected function handleBusinessException(BusinessException $e, Request $request)
    {
        if ($request->wantsJson() || $request->header('X-Livewire')) {
            return response()->json($e->toArray(), $e->getStatusCode());
        }

        return back()->with('error', $e->getUserMessage());
    }

    /**
     * Handle database exceptions
     */
    protected function handleDatabaseException(\Illuminate\Database\QueryException $e, Request $request)
    {
        $userMessage = 'Terjadi kesalahan pada database. Silakan coba lagi.';
        
        // Handle specific database errors
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $userMessage = 'Data yang sama sudah ada. Silakan gunakan data yang berbeda.';
        } elseif (str_contains($e->getMessage(), 'foreign key constraint')) {
            $userMessage = 'Data tidak dapat dihapus karena masih digunakan di bagian lain sistem.';
        } elseif (str_contains($e->getMessage(), 'Connection refused')) {
            $userMessage = 'Koneksi database bermasalah. Silakan hubungi administrator.';
        }

        if ($request->wantsJson() || $request->header('X-Livewire')) {
            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error_code' => 'DATABASE_ERROR',
            ], 500);
        }

        return back()->with('error', $userMessage);
    }

    /**
     * Handle validation exceptions
     */
    protected function handleValidationException(\Illuminate\Validation\ValidationException $e, Request $request)
    {
        if ($request->wantsJson() || $request->header('X-Livewire')) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $e->errors(),
            ], 422);
        }

        return back()->withErrors($e->errors())->withInput();
    }

    /**
     * Handle 404 exceptions
     */
    protected function handleNotFoundException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Halaman atau resource yang dicari tidak ditemukan.',
                'error_code' => 'NOT_FOUND',
            ], 404);
        }

        return response()->view('errors.404', [], 404);
    }

    /**
     * Handle authentication exceptions
     */
    protected function handleAuthenticationException(\Illuminate\Auth\AuthenticationException $e, Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus login terlebih dahulu.',
                'error_code' => 'UNAUTHENTICATED',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Handle authorization exceptions
     */
    protected function handleUnauthorizedException(\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk melakukan tindakan ini.',
                'error_code' => 'UNAUTHORIZED',
            ], 403);
        }

        return back()->with('error', 'Anda tidak memiliki akses untuk melakukan tindakan ini.');
    }

    /**
     * Get HTTP status code for exception
     */
    protected function getExceptionStatusCode(Throwable $e): int
    {
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return 422;
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return 401;
        }

        if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return 403;
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 404;
        }

        return 500;
    }

    /**
     * Get user-friendly error message
     */
    protected function getUserFriendlyMessage(Throwable $e): string
    {
        // Handle BusinessException first
        if ($e instanceof BusinessException) {
            return $e->getUserMessage();
        }

        // Custom business logic exceptions
        if (str_contains($e->getMessage(), 'Keranjang kosong')) {
            return 'Keranjang belanja kosong. Silakan tambahkan produk terlebih dahulu.';
        }

        if (str_contains($e->getMessage(), 'Stok tidak mencukupi')) {
            return $e->getMessage(); // Already user-friendly
        }

        if (str_contains($e->getMessage(), 'tidak ditemukan')) {
            return $e->getMessage(); // Already user-friendly
        }

        // Database related errors
        if ($e instanceof \Illuminate\Database\QueryException) {
            return 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
        }

        // Default messages for different exception types
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return 'Data yang dikirim tidak valid.';
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return 'Anda harus login terlebih dahulu.';
        }

        if ($e instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 'Halaman yang dicari tidak ditemukan.';
        }

        // For production, return generic message
        if (!config('app.debug')) {
            return 'Terjadi kesalahan pada sistem. Silakan coba lagi atau hubungi administrator.';
        }

        return $e->getMessage();
    }
} 