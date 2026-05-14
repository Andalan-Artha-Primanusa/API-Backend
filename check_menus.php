<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MenuPermission;
use App\Models\Role;

$roles = Role::where('name', '!=', 'super_admin')->pluck('name', 'id');
echo "Roles:\n";
foreach ($roles as $id => $name) {
    echo "  $id => $name\n";
}

$perms = MenuPermission::get();
$grouped = $perms->groupBy('menu_key');

echo "\nMenu Permissions:\n";
foreach ($grouped as $key => $items) {
    $roleIds = $items->pluck('role_id')->toArray();
    $roleNames = [];
    foreach ($roleIds as $rid) {
        $roleNames[] = $roles[$rid] ?? 'unknown';
    }
    echo "  $key: " . implode(', ', $roleNames) . "\n";
}

echo "\nTotal distinct menus: " . $perms->unique('menu_key')->count() . "\n";
