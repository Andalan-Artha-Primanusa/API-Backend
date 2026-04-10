<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Default seed accounts configuration
     */
    private array $accounts = [
        [
            'email'         => 'superadmin@gmail.com',
            'name'          => 'Super Admin',
            'role'          => User::ROLE_SUPER_ADMIN,
            'employee_code' => 'ADM-001',
            'position'      => 'Super Administrator',
            'department'    => 'Management',
            'salary'        => 20000000,
            'env_password'  => 'SUPER_ADMIN_PASSWORD',
            'default_pass'  => 'SuperAdmin@123',
        ],
        [
            'email'         => 'admin@gmail.com',
            'name'          => 'Admin',
            'role'          => User::ROLE_ADMIN,
            'employee_code' => 'ADM-002',
            'position'      => 'Administrator',
            'department'    => 'Management',
            'salary'        => 15000000,
            'env_password'  => 'ADMIN_PASSWORD',
            'default_pass'  => 'Admin@123456',
        ],
        [
            'email'         => 'employee@gmail.com',
            'name'          => 'Employee',
            'role'          => User::ROLE_EMPLOYEE,
            'employee_code' => 'EMP-001',
            'position'      => 'Employee',
            'department'    => 'General',
            'salary'        => 5000000,
            'env_password'  => 'EMPLOYEE_PASSWORD',
            'default_pass'  => 'Employee@1234',
        ],
    ];

    public function run(): void
    {
        // Use transaction for data consistency
        DB::transaction(function () {
            // Cache roles to avoid repeated queries
            $roles = Role::whereIn('name', [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_EMPLOYEE,
            ])->get()->keyBy('name');

            if ($roles->isEmpty()) {
                $this->command?->error('Roles not found. Run RbacSeeder first.');
                return;
            }

            // Process each account
            foreach ($this->accounts as $accountConfig) {
                $this->seedAccount($accountConfig, $roles);
            }

            if (app()->isLocal()) {
                $this->command?->info('✅ Seed accounts created successfully.');
                $this->command?->info('📝 Configure passwords in .env using: SUPER_ADMIN_PASSWORD, ADMIN_PASSWORD, EMPLOYEE_PASSWORD');
            }
        }, 5); // 5 retry attempts
    }

    /**
     * Seed a single account with its role and employee data
     */
    private function seedAccount(array $config, $roles): void
    {
        // Get password from env or use default (with validation)
        $password = $this->getPassword(
            $config['env_password'],
            $config['default_pass']
        );

        // Create or update user
        $user = User::firstOrCreate(
            ['email' => $config['email']],
            [
                'name'     => $config['name'],
                'password' => Hash::make($password),
            ]
        );

        // Assign role (sync to avoid duplicates)
        if (isset($roles[$config['role']])) {
            $user->roles()->syncWithoutDetaching([$roles[$config['role']]->id]);
        }

        // Create employee record if not exists
        Employee::firstOrCreate(
            ['user_id' => $user->id],
            [
                'employee_code' => $config['employee_code'],
                'position'      => $config['position'],
                'department'    => $config['department'],
                'hire_date'     => now(),
                'salary'        => $config['salary'],
            ]
        );
    }

    /**
     * Get password from environment or use secure default
     * 
     * @param string $envKey Environment variable key
     * @param string $default Default password
     * @return string
     */
    private function getPassword(string $envKey, string $default): string
    {
        $password = env($envKey, $default);

        // Validate password strength in production
        if (!app()->isLocal() && strlen($password) < 12) {
            throw new \Exception(
                "Password for {$envKey} must be at least 12 characters in production"
            );
        }

        return $password;
    }
}
