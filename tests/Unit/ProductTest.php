<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_product()
    {
        $category = Category::create(['name' => 'Test Category']);
        
        $product = Product::create([
            'name' => 'Sate Ayam',
            'price' => 15000,
            'category_id' => $category->id
        ]);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Sate Ayam', $product->name);
        $this->assertEquals(15000, $product->price);
        $this->assertEquals($category->id, $product->category_id);
    }

    #[Test]
    public function it_has_fillable_attributes()
    {
        $product = new Product();
        $expected = ['name', 'price', 'category_id', 'photo'];
        
        $this->assertEquals($expected, $product->getFillable());
    }

    #[Test]
    public function it_belongs_to_category()
    {
        $category = Category::create(['name' => 'Test Category']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    #[Test]
    public function it_can_be_soft_deleted()
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNotNull($product->fresh()->deleted_at);
    }

    #[Test]
    public function price_is_cast_to_decimal()
    {
        $product = new Product();
        $casts = $product->getCasts();
        
        $this->assertEquals('decimal:2', $casts['price']);
    }

    #[Test]
    public function it_has_formatted_price_attribute()
    {
        $product = Product::factory()->create(['price' => 15000]);
        
        $this->assertEquals('Rp 15.000', $product->formatted_price);
    }
} 