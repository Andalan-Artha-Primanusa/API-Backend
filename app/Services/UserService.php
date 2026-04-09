<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Employee;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepo
    ) {}

    public function register(array $data): User
    {
        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign default employee role via RBAC pivot table
        $employeeRole = Role::where('name', User::ROLE_EMPLOYEE)->first();
        if ($employeeRole) {
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }

        return $user;
    }

    public function login(array $data): User
    {
        $user = $this->userRepo->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password'],
            ]);
        }

        return $user;
    }

    public function findOrCreateFromGoogle($googleUser): User
{
    $user = $this->userRepo->findByEmail($googleUser->getEmail());

    if (!$user) {
        $user = $this->userRepo->create([
            'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
            'email' => $googleUser->getEmail(),
            'password' => Hash::make(Str::random(32)),
        ]);

        // assign role
        $employeeRole = Role::where('name', User::ROLE_EMPLOYEE)->first();
        if ($employeeRole) {
            $user->roles()->syncWithoutDetaching([$employeeRole->id]);
        }
    }

    // 🔥 auto create employee (IMPORTANT)
    if (!$user->employee()->exists()) {
        Employee::create([
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . str_pad((string)$user->id, 4, '0', STR_PAD_LEFT),
            'position' => 'Staff',
            'department' => 'General',
            'hire_date' => now(),
            'salary' => 0,
        ]);
    }

    return $user;
}
}
