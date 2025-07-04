<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_category()
    {
        $category = Category::create([
            'name' => 'Minuman',
            'description' => 'Kategori untuk minuman'
        ]);

        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Minuman', $category->name);
        $this->assertEquals('Kategori untuk minuman', $category->description);
    }

    public function test_it_has_fillable_attributes()
    {
        $category = new Category();
        $expected = ['name', 'description'];
        
        $this->assertEquals($expected, $category->getFillable());
    }

    public function test_it_has_many_products()
    {
        $category = Category::create(['name' => 'Test Category']);
        
        $product1 = Product::factory()->create(['category_id' => $category->id]);
        $product2 = Product::factory()->create(['category_id' => $category->id]);

        $this->assertCount(2, $category->products);
        $this->assertTrue($category->products->contains($product1));
        $this->assertTrue($category->products->contains($product2));
    }

    public function test_it_can_be_soft_deleted()
    {
        $category = Category::create(['name' => 'Test Category']);
        $categoryId = $category->id;

        $category->delete();

        $this->assertSoftDeleted('categories', ['id' => $categoryId]);
        $this->assertNotNull($category->fresh()->deleted_at);
    }

    public function test_name_is_required()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        Category::create(['description' => 'Test description']);
    }
} 