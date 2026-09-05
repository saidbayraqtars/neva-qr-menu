<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admins_can_see_it(): void
    {
        $this->get(route('admin.system'))->assertRedirect(route('login'));

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $this->actingAs($owner)->get(route('admin.system'))->assertForbidden();
    }

    public function test_admin_sees_the_health_screen(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get(route('admin.system'))
            ->assertOk()
            ->assertSee('Sistem Durumu')
            ->assertSee('Veritabanı')
            ->assertSee('Son yedek');
    }

    /**
     * Ölçümler eksik olabilir (Linux olmayan geliştirme makinesi, kısıtlı
     * ortam). Sayfa bu durumda çökmemeli, "—" göstermeli.
     */
    public function test_it_survives_missing_metrics(): void
    {
        $health = \App\Support\ServerHealth::all();

        $this->assertArrayHasKey('memory', $health);
        $this->assertArrayHasKey('disk', $health);
        $this->assertArrayHasKey('queue', $health);
        $this->assertIsString(\App\Support\ServerHealth::bytes(null));
        $this->assertSame('—', \App\Support\ServerHealth::bytes(null));
    }

    public function test_byte_formatting(): void
    {
        $this->assertSame('512 B', \App\Support\ServerHealth::bytes(512));
        $this->assertSame('1 KB', \App\Support\ServerHealth::bytes(1024));
        $this->assertSame('1.5 MB', \App\Support\ServerHealth::bytes((int) (1.5 * 1024 * 1024)));
    }
}
