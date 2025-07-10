<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\TransactionAudit;
use App\Models\Product;
use App\Models\Partner;
use App\Services\StockService;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;
use Carbon\Carbon;

class TransactionEditComponent extends Component
{
    public $transaction;
    public $originalData = [];
    
    // Editable fields
    public $notes = '';
    public $orderType = '';
    public $partnerId = null;
    public $paymentMethod = '';
    public $transactionDate = '';
    public $editReason = '';
    
    // Transaction items editing
    public $editableItems = [];
    public $itemsChanged = false;
    
    // UI state
    public $isLoading = false;
    public $showConfirmModal = false;
    public $changesSummary = [];

    protected $rules = [
        'notes' => 'nullable|string|max:1000',
        'orderType' => 'required|in:dine_in,take_away,online',
        'partnerId' => 'nullable|exists:partners,id',
        'paymentMethod' => 'required|in:cash,qris,aplikasi',
        'transactionDate' => 'required|date|before_or_equal:today',
        'editReason' => 'required|string|max:500',
        'editableItems.*.quantity' => 'required|integer|min:1',
    ];

    protected $messages = [
        'transactionDate.required' => 'Tanggal transaksi harus diisi.',
        'transactionDate.date' => 'Format tanggal tidak valid.',
        'transactionDate.before_or_equal' => 'Tanggal transaksi tidak boleh di masa depan.',
        'editReason.required' => 'Alasan edit harus diisi.',
        'editableItems.*.quantity.required' => 'Kuantitas harus diisi.',
        'editableItems.*.quantity.min' => 'Kuantitas minimal 1.',
    ];

    public function mount($transactionId)
    {
        try {
            // Validate admin permission
            if (!auth()->user() || !auth()->user()->hasRole('admin')) {
                abort(403, 'Akses ditolak. Hanya admin yang dapat mengedit transaksi.');
            }

            $this->transaction = Transaction::with(['user', 'partner', 'items.product'])
                ->findOrFail($transactionId);

            // Validate edit conditions
            if ($this->transaction->status !== 'completed') {
                abort(422, 'Hanya transaksi yang sudah selesai yang dapat diedit.');
            }

            // Check 24-hour time limit
            if ($this->transaction->created_at->diffInHours(now()) > 24) {
                abort(422, 'Transaksi hanya dapat diedit dalam 24 jam setelah dibuat.');
            }

            $this->initializeData();

        } catch (\Exception $e) {
            abort(404, 'Transaksi tidak ditemukan atau tidak dapat diedit.');
        }
    }

    private function initializeData()
    {
        // Store original data for comparison
        $this->originalData = [
            'notes' => $this->transaction->notes,
            'order_type' => $this->transaction->order_type,
            'partner_id' => $this->transaction->partner_id,
            'payment_method' => $this->transaction->payment_method,
            'transaction_date' => $this->transaction->transaction_date ? $this->transaction->transaction_date->format('Y-m-d') : null,
            'items' => $this->transaction->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'total' => $item->total,
                ];
            })->toArray(),
        ];

        // Initialize editable values
        $this->notes = $this->transaction->notes ?? '';
        $this->orderType = $this->transaction->order_type;
        $this->partnerId = $this->transaction->partner_id;
        $this->paymentMethod = $this->transaction->payment_method;
        $this->transactionDate = $this->transaction->transaction_date ? $this->transaction->transaction_date->format('Y-m-d') : now()->format('Y-m-d');

        // Initialize editable items
        $this->editableItems = $this->transaction->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
                'original_quantity' => $item->quantity,
                'discount_amount' => $item->discount_amount,
                'subtotal' => $item->subtotal,
                'total' => $item->total,
            ];
        })->toArray();
    }

    public function render()
    {
        $partners = Partner::orderBy('name')->get();
        
        return view('livewire.transaction-edit-component', [
            'partners' => $partners,
            'orderTypeLabels' => [
                'dine_in' => 'Makan di Tempat',
                'take_away' => 'Bawa Pulang',
                'online' => 'Online'
            ],
            'paymentMethodLabels' => [
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'aplikasi' => 'Aplikasi'
            ]
        ]);
    }

    public function updateItemQuantity($index, $newQuantity)
    {
        if (isset($this->editableItems[$index])) {
            $this->editableItems[$index]['quantity'] = max(1, (int)$newQuantity);
            $this->recalculateItemTotal($index);
            $this->itemsChanged = true;
        }
    }

    private function recalculateItemTotal($index)
    {
        $item = &$this->editableItems[$index];
        $item['subtotal'] = $item['product_price'] * $item['quantity'];
        $item['total'] = $item['subtotal'] - $item['discount_amount'];
    }

    public function updatedOrderType()
    {
        // Reset partner if changing from online to other types
        if ($this->orderType !== 'online') {
            $this->partnerId = null;
        }
    }

    public function previewChanges()
    {
        $this->validate();

        try {
            $this->changesSummary = $this->generateChangesSummary();
            
            if (empty($this->changesSummary)) {
                LivewireAlert::title('Tidak Ada Perubahan!')
                    ->text('Tidak ada perubahan yang terdeteksi pada transaksi.')
                    ->info()
                    ->show();
                return;
            }

            $this->showConfirmModal = true;

        } catch (\Exception $e) {
            LivewireAlert::title('Error!')
                ->text('Gagal memproses perubahan: ' . $e->getMessage())
                ->error()
                ->show();
        }
    }

    private function generateChangesSummary()
    {
        $changes = [];

        // Check basic field changes
        if ($this->notes !== $this->originalData['notes']) {
            $changes[] = [
                'field' => 'Catatan',
                'old' => $this->originalData['notes'] ?: '(kosong)',
                'new' => $this->notes ?: '(kosong)'
            ];
        }

        if ($this->orderType !== $this->originalData['order_type']) {
            $orderLabels = [
                'dine_in' => 'Makan di Tempat',
                'take_away' => 'Bawa Pulang',
                'online' => 'Online'
            ];
            $changes[] = [
                'field' => 'Jenis Pesanan',
                'old' => $orderLabels[$this->originalData['order_type']],
                'new' => $orderLabels[$this->orderType]
            ];
        }

        if ($this->partnerId != $this->originalData['partner_id']) {
            $oldPartner = $this->originalData['partner_id'] 
                ? Partner::find($this->originalData['partner_id'])?->name 
                : '(tidak ada)';
            $newPartner = $this->partnerId 
                ? Partner::find($this->partnerId)?->name 
                : '(tidak ada)';
            
            $changes[] = [
                'field' => 'Partner',
                'old' => $oldPartner,
                'new' => $newPartner
            ];
        }

        if ($this->paymentMethod !== $this->originalData['payment_method']) {
            $paymentLabels = [
                'cash' => 'Tunai',
                'qris' => 'QRIS',
                'aplikasi' => 'Aplikasi'
            ];
            $changes[] = [
                'field' => 'Metode Pembayaran',
                'old' => $paymentLabels[$this->originalData['payment_method']],
                'new' => $paymentLabels[$this->paymentMethod]
            ];
        }

        if ($this->transactionDate !== $this->originalData['transaction_date']) {
            $changes[] = [
                'field' => 'Tanggal Transaksi',
                'old' => $this->originalData['transaction_date'] ? Carbon::parse($this->originalData['transaction_date'])->format('d/m/Y') : '(tidak ada)',
                'new' => $this->transactionDate ? Carbon::parse($this->transactionDate)->format('d/m/Y') : '(tidak ada)'
            ];
        }

        // Check item quantity changes
        foreach ($this->editableItems as $item) {
            if ($item['quantity'] != $item['original_quantity']) {
                $changes[] = [
                    'field' => "Qty {$item['product_name']}",
                    'old' => $item['original_quantity'],
                    'new' => $item['quantity']
                ];
            }
        }

        return $changes;
    }

    public function confirmChanges()
    {
        $this->validate();

        try {
            \DB::beginTransaction();

            $this->isLoading = true;
            $changes = $this->changesSummary;

            // Update transaction basic fields
            $this->updateTransactionFields();

            // Update transaction items and handle stock adjustments
            $this->updateTransactionItems();

            // Recalculate transaction totals
            $this->recalculateTransactionTotals();

            // Create audit trail for each change
            $this->createAuditTrail($changes);

            \DB::commit();

            $this->closeConfirmModal();

            LivewireAlert::title('Berhasil!')
                ->text('Transaksi berhasil diperbarui dan audit trail telah dicatat.')
                ->success()
                ->show();

            // Redirect back to transaction list
            return $this->redirect(route('staf.transactions'), navigate: true);

        } catch (\Exception $e) {
            \DB::rollBack();
            
            LivewireAlert::title('Error!')
                ->text('Gagal mengupdate transaksi: ' . $e->getMessage())
                ->error()
                ->show();
        } finally {
            $this->isLoading = false;
        }
    }

    private function updateTransactionFields()
    {
        // Parse transaction date and preserve original time if it exists
        $transactionDate = $this->transactionDate ? Carbon::parse($this->transactionDate) : null;
        if ($transactionDate && $this->transaction->transaction_date) {
            // Preserve original time
            $originalTime = $this->transaction->transaction_date;
            $transactionDate = $transactionDate->setHour($originalTime->hour)
                                             ->setMinute($originalTime->minute)
                                             ->setSecond($originalTime->second);
        } elseif ($transactionDate) {
            // If no original time, set to current time
            $transactionDate = $transactionDate->setTime(now()->hour, now()->minute, now()->second);
        }

        $this->transaction->update([
            'notes' => $this->notes,
            'order_type' => $this->orderType,
            'partner_id' => $this->partnerId,
            'payment_method' => $this->paymentMethod,
            'transaction_date' => $transactionDate,
        ]);
    }

    private function updateTransactionItems()
    {
        $stockService = app(StockService::class);

        foreach ($this->editableItems as $editableItem) {
            $originalItem = collect($this->originalData['items'])
                ->firstWhere('id', $editableItem['id']);
                
            if (!$originalItem) continue;

            $quantityDiff = $editableItem['quantity'] - $originalItem['quantity'];

            if ($quantityDiff !== 0) {
                // Update transaction item
                $transactionItem = $this->transaction->items()
                    ->where('id', $editableItem['id'])
                    ->first();

                if ($transactionItem) {
                    $newSubtotal = $editableItem['product_price'] * $editableItem['quantity'];
                    $newTotal = $newSubtotal - $editableItem['discount_amount'];

                    $transactionItem->update([
                        'quantity' => $editableItem['quantity'],
                        'subtotal' => $newSubtotal,
                        'total' => $newTotal,
                    ]);

                    // Handle stock adjustment
                    if ($quantityDiff > 0) {
                        // Increased quantity - reduce stock further
                        $stockService->logSale(
                            $editableItem['product_id'],
                            auth()->id(),
                            $quantityDiff,
                            $this->transaction->id,
                            "Transaction edit - quantity increased - {$this->transaction->transaction_code}"
                        );
                    } else {
                        // Decreased quantity - return stock
                        $stockService->logCancellationReturn(
                            $editableItem['product_id'],
                            auth()->id(),
                            abs($quantityDiff),
                            $this->transaction->id,
                            "Transaction edit - quantity decreased - {$this->transaction->transaction_code}"
                        );
                    }
                }
            }
        }
    }

    private function recalculateTransactionTotals()
    {
        $subtotal = $this->transaction->items()->sum('subtotal');
        $totalDiscount = $this->transaction->items()->sum('discount_amount');
        $finalTotal = $subtotal - $totalDiscount;

        // Calculate partner commission if applicable
        $partnerCommission = 0;
        if ($this->orderType === 'online' && $this->partnerId) {
            $partner = Partner::find($this->partnerId);
            if ($partner) {
                $partnerCommission = $subtotal * ($partner->commission_rate / 100);
            }
        }

        $this->transaction->update([
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'final_total' => $finalTotal,
            'partner_commission' => $partnerCommission,
        ]);
    }

    private function createAuditTrail($changes)
    {
        foreach ($changes as $change) {
            TransactionAudit::create([
                'transaction_id' => $this->transaction->id,
                'admin_id' => auth()->id(),
                'field_changed' => $change['field'],
                'old_value' => $change['old'],
                'new_value' => $change['new'],
                'reason' => $this->editReason,
                'changed_at' => now(),
            ]);
        }
    }

    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->changesSummary = [];
    }

    public function getFormattedTimeRemainingProperty()
    {
        $hoursRemaining = 24 - $this->transaction->created_at->diffInHours(now());
        return max(0, $hoursRemaining) . ' jam';
    }
}
