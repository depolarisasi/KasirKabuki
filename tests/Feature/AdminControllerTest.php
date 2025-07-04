<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
        
        if (!Role::where('name', 'staf')->exists()) {
            Role::create(['name' => 'staf']);
        }
    }

    public function test_admin_controller_dashboard_method_works()
    {
        // Test the controller method directly instead of through HTTP
        $controller = new \App\Http\Controllers\AdminController();
        $response = $controller->dashboard();
        
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('admin.dashboard', $response->getName());
    }

    public function test_admin_can_access_categories_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/categories');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.categories.index');
    }

    public function test_admin_can_access_products_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/products');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.products.index');
    }

    public function test_admin_can_access_partners_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/partners');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.partners.index');
    }

    public function test_admin_can_access_config_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        $response = $this->actingAs($admin)->get('/admin/config');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.config.index');
    }

    public function test_admin_can_access_reports()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $admin->assignRole('admin');
        
        // Test sales report
        $response = $this->actingAs($admin)->get('/admin/reports/sales');
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.sales');
        
        // Test expenses report
        $response = $this->actingAs($admin)->get('/admin/reports/expenses');
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.expenses');
        
        // Test stock report
        $response = $this->actingAs($admin)->get('/admin/reports/stock');
        $response->assertStatus(200);
        $response->assertViewIs('admin.reports.stock');
    }

    public function test_staff_cannot_access_admin_routes()
    {
        $staff = User::factory()->create(['role' => 'staf']);
        $staff->assignRole('staf');
        
        $response = $this->actingAs($staff)->get('/admin/dashboard');
        $response->assertStatus(403);
        
        $response = $this->actingAs($staff)->get('/admin/categories');
        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_admin_routes()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
} 