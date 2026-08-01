<?php

namespace Tests\Feature\Modules;

use App\Models\Permission;
use App\Models\User;
use App\Support\Modules\ModuleBootstrapper;
use App\Support\Modules\ModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class ModuleSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_modules_are_discovered(): void
    {
        $names = modules()->all()->map->name()->all();

        $this->assertContains('system', $names);
        $this->assertContains('exca-coin', $names);
    }

    public function test_navigation_contains_module_menu_entries(): void
    {
        $routes = modules()->navigation()->pluck('route')->all();

        $this->assertContains('dashboard', $routes);
        $this->assertContains('all-coins.index', $routes);
        $this->assertContains('all-finds.index', $routes);
    }

    public function test_permission_groups_are_built_from_module_manifests(): void
    {
        $groups = modules()->permissionGroups();

        $this->assertArrayHasKey('coins', $groups);
        $this->assertSame('Sikkeler', $groups['coins']['title']);
        $this->assertSame('circle-stack', $groups['coins']['icon']);
        $this->assertSame('amber', $groups['coins']['color']);

        $this->assertArrayHasKey('users', $groups);
        $this->assertSame('shield-check', $groups['roles']['icon']);
    }

    public function test_module_permissions_can_be_synced(): void
    {
        Permission::query()->delete();

        modules()->syncPermissions();

        $this->assertDatabaseHas('permissions', ['name' => 'coins.view', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'users.view', 'guard_name' => 'web']);
        $this->assertDatabaseHas('permissions', ['name' => 'quick_entry.access', 'guard_name' => 'web']);
    }

    public function test_module_routes_include_the_web_middleware_group(): void
    {
        $route = Route::getRoutes()->getByName('dashboard');

        $this->assertNotNull($route);
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }

    public function test_make_module_generates_and_boots_a_new_module(): void
    {
        $base = app_path('Modules/TestModule');

        if (is_dir($base)) {
            $this->fail('TestModule already exists, refusing to run.');
        }

        try {
            $command = $this->artisan('make:module', ['name' => 'TestModule']);

            $this->assertSame(
                0,
                $command instanceof PendingCommand ? $command->run() : $command,
            );

            $this->assertFileExists($base.'/module.php');
            $this->assertFileExists($base.'/routes/web.php');
            $this->assertFileExists($base.'/resources/views/pages/test-module/index.blade.php');

            // Discover the new module with a fresh manager instance.
            $this->app->forgetInstance(ModuleManager::class);
            $manager = $this->app->make(ModuleManager::class);

            $this->assertTrue($manager->has('test-module'));
            $this->assertCount(4, $manager->get('test-module')->permissions());

            // Boot it (views + Livewire namespace + routes) and hit its page.
            $this->app->make(ModuleBootstrapper::class)->boot($manager->get('test-module'));

            $manager->syncPermissions();

            $user = User::factory()->create();
            $user->givePermissionTo('test_module.view');

            $this->actingAs($user)
                ->get('/adminx/test-module')
                ->assertOk();
        } finally {
            if (is_dir($base)) {
                File::deleteDirectory($base);
            }
        }
    }
}
