<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
    }

    public function test_it_has_fillable_attributes()
    {
        $user = new User();
        $expected = ['name', 'email', 'password', 'role'];
        
        $this->assertEquals($expected, $user->getFillable());
    }

    public function test_it_hides_password_in_array()
    {
        $user = User::factory()->make([
            'password' => 'secret123'
        ]);

        $array = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_it_casts_email_verified_at_to_datetime()
    {
        $user = new User();
        $casts = $user->getCasts();
        
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    public function test_it_hashes_password_when_created()
    {
        $user = User::factory()->create([
            'password' => 'plaintext'
        ]);

        $this->assertNotEquals('plaintext', $user->password);
        $this->assertTrue(\Hash::check('plaintext', $user->password));
    }
} 