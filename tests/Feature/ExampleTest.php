<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertRedirect('/tienda');
        $this->get('/tienda')->assertOk();
    }

    public function test_admin_requires_login_and_loads_for_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');

        $admin = User::factory()->create([
            'rol' => 'admin',
            'activo' => true,
        ]);

        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
