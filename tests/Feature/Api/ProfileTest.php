<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $password = 'current_password_123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => Hash::make($this->password),
            'phone' => '+1234567890',
            'address' => '123 Main St, City',
        ]);
    }

    public function test_can_view_profile(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonPath('data.id', $this->user->id)
            ->assertJsonPath('data.name', $this->user->name)
            ->assertJsonPath('data.email', $this->user->email);
    }

    public function test_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/profile', [
            'name' => 'Updated Name',
            'phone' => '+9876543210',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'phone' => '+9876543210',
        ]);
    }

    public function test_can_change_password(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/change-password', [
            'current_password' => $this->password,
            'new_password' => 'new_secure_password_456',
            'new_password_confirmation' => 'new_secure_password_456',
        ]);

        $response->assertOk();

        // Verify the new password works
        $this->assertTrue(Hash::check('new_secure_password_456', $this->user->fresh()->password));
    }

    public function test_change_password_fails_with_wrong_current(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/change-password', [
            'current_password' => 'wrong_password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    }

    public function test_change_password_fails_with_mismatched_confirmation(): void
    {
        $response = $this->actingAs($this->user)->putJson('/api/change-password', [
            'current_password' => $this->password,
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'different_password',
        ]);

        $response->assertStatus(422);
    }

    public function test_requires_authentication_for_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    public function test_requires_authentication_for_change_password(): void
    {
        $response = $this->putJson('/api/change-password', [
            'current_password' => 'test',
            'new_password' => 'newpass123',
            'new_password_confirmation' => 'newpass123',
        ]);

        $response->assertStatus(401);
    }
}
