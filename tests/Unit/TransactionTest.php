<?php

namespace Tests\Unit;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Partner;
use App\Models\TransactionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_can_create_a_transaction()
    {
        $user = User::factory()->create();
        
        $transaction = Transaction::create([
            'transaction_code' => 'TRX-20250101-001',
            'user_id' => $user->id,
            'order_type' => 'dine_in',
            'subtotal' => 50000,
            'total_discount' => 5000,
            'partner_commission' => 0,
            'final_total' => 45000,
            'payment_method' => 'cash',
            'status' => 'completed'
        ]);

        $this->assertInstanceOf(Transaction::class, $transaction);
        $this->assertEquals($user->id, $transaction->user_id);
        $this->assertEquals('dine_in', $transaction->order_type);
        $this->assertEquals('cash', $transaction->payment_method);
        $this->assertEquals('completed', $transaction->status);
    }

    /** @test */
    public function test_it_has_fillable_attributes()
    {
        $transaction = new Transaction();
        $expected = [
            'transaction_code', 'user_id', 'order_type', 'partner_id',
            'subtotal', 'total_discount', 'partner_commission', 'final_total',
            'payment_method', 'status', 'discount_details', 'notes', 'completed_at'
        ];
        
        $this->assertEquals($expected, $transaction->getFillable());
    }

    /** @test */
    public function test_it_belongs_to_user()
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $transaction->user);
        $this->assertEquals($user->id, $transaction->user->id);
    }

    /** @test */
    public function test_it_belongs_to_partner()
    {
        $partner = Partner::factory()->create();
        $transaction = Transaction::factory()->create(['partner_id' => $partner->id]);

        $this->assertInstanceOf(Partner::class, $transaction->partner);
        $this->assertEquals($partner->id, $transaction->partner->id);
    }

    /** @test */
    public function test_it_has_many_items()
    {
        $transaction = Transaction::factory()->create();
        
        $item1 = TransactionItem::factory()->create(['transaction_id' => $transaction->id]);
        $item2 = TransactionItem::factory()->create(['transaction_id' => $transaction->id]);

        $this->assertCount(2, $transaction->items);
        $this->assertTrue($transaction->items->contains($item1));
        $this->assertTrue($transaction->items->contains($item2));
    }

    /** @test */
    public function test_it_casts_amounts_to_decimal()
    {
        $transaction = new Transaction();
        $casts = $transaction->getCasts();
        
        $this->assertEquals('decimal:2', $casts['subtotal']);
        $this->assertEquals('decimal:2', $casts['total_discount']);
        $this->assertEquals('decimal:2', $casts['partner_commission']);
        $this->assertEquals('decimal:2', $casts['final_total']);
    }

    /** @test */
    public function test_it_casts_discount_details_to_array()
    {
        $transaction = new Transaction();
        $casts = $transaction->getCasts();
        
        $this->assertEquals('array', $casts['discount_details']);
    }

    /** @test */
    public function test_it_has_formatted_amounts()
    {
        $transaction = Transaction::factory()->create([
            'subtotal' => 50000,
            'final_total' => 45000
        ]);

        $this->assertEquals('Rp 50.000', $transaction->formatted_subtotal);
        $this->assertEquals('Rp 45.000', $transaction->formatted_final_total);
    }
} 