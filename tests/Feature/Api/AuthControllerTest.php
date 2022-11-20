<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

     public function setUp(): void
     {
         parent::setUp();
         $this->artisan('passport:install');
     }

    /** @test */
    public function can_authenticate()
    {
        $response = $this->postJson('/auth/token', [
            'email' => $this->create(User::class)->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200)->assertJsonStructure(['token']);
    }
}
