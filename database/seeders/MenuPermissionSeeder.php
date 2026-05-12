<?php

namespace Database\Seeders;

use App\Models\MenuPermission;
use App\Models\Role;
use App\Http\Controllers\Api\MenuController;
use Illuminate\Database\Seeder;

class MenuPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $menuKeys = collect(MenuController::MENU_DEFINITIONS)->pluck('key');
        $roles = Role::all();

        foreach ($roles as $role) {
            foreach ($menuKeys as $key) {
                MenuPermission::firstOrCreate([
                    'menu_key' => $key,
                    'role_id' => $role->id,
                ]);
            }
        }

        $this->command?->info('All menus assigned to all roles successfully.');
    }
}
