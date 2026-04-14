# Permission Registry System - Documentation

## Overview

Seeded dari `App\Constants\Permissions` yang **centralized & maintainable**, bukan hardcoded di seeder. Super Admin bisa customize role-permission di runtime via API.

---

## Architecture

### 1. Permission Registry (`App\Constants\Permissions`)

**File**: `app/Constants/Permissions.php`

Semua permissions di-define dalam satu class dengan **resource grouping**:

```php
const EMPLOYEE = [
    'employee.view' => 'View employee list & details',
    'employee.create' => 'Create new employee',
    'employee.update' => 'Update employee profile',
    'employee.delete' => 'Delete employee',
    'employee.onboard' => 'Start onboarding process',
    'employee.offboard' => 'Start offboarding process',
];

const LEAVE = [
    'leave.view' => 'View leave requests',
    'leave.create' => 'Create leave request',
    'leave.approve' => 'Approve/reject leave (Manager/HR)',
    'leave.policy.manage' => 'Manage leave policies',
];
```

**Keuntungan**:
✅ Semua permissions dokumentasi in one place
✅ Easy to add, remove, atau update permissions
✅ Readable dengan descriptions untuk audit trail
✅ Can be used for UI permission management
✅ Version controlled dengan code

### 2. Role-Permission Mappings

**Default mappings** di-define dalam `roleDefaultPermissions()`:

```php
public static function roleDefaultPermissions(): array
{
    return [
        'super_admin' => array_keys(self::all()),
        'admin' => [...muuuuch more perms...],
        'hr' => [...hr specific...],
        'manager' => [...manager perms...],
        'employee' => [...self service...],
    ];
}
```

**Ini adalah INITIAL mapping saja** - Super Admin bisa ubah later via API!

---

## How to Use

### 1. Initial Setup (First Time)

Run seeder:

```bash
php artisan db:seed --class=RbacSeeder
```

Output:

```
🔐 Seeding RBAC System...
  ✓ Creating roles...
  ✓ Creating permissions from registry (100+ permissions)...
  ✓ Assigning permissions to roles...
    → super_admin: 100+ permissions assigned
    → admin: 80+ permissions assigned
    → hr: 60+ permissions assigned
    → manager: 20+ permissions assigned
    → employee: 15+ permissions assigned

✅ RBAC System seeded successfully!
📝 Customize permissions via: POST /admin/roles/{roleId}/assign-permission
```

### 2. Get All Permissions (For UI/Audit)

**GET** `/admin/permissions`

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "employee.view",
      "description": "View employee list & details",
      "created_at": "2026-04-14T...",
      "updated_at": "2026-04-14T..."
    },
    {
      "id": 2,
      "name": "employee.create",
      "description": "Create new employee",
      ...
    },
    ...
  ]
}
```

### 3. Get Role with Permissions

**GET** `/admin/roles?with=permissions`

Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "super_admin",
      "permissions": [
        { "id": 1, "name": "employee.view", "description": "..." },
        { "id": 2, "name": "employee.create", "description": "..." },
        ...total 100+ permissions
      ]
    },
    {
      "id": 2,
      "name": "admin",
      "permissions": [...]  // ~80+ permissions
    }
  ]
}
```

### 4. Customize Role Permissions (Super Admin Only)

**POST** `/admin/roles/{roleId}/assign-permission`

Request body (dapat permission IDs dari endpoint #2):

```json
{
  "permissions": [1, 2, 3, 5, 7, 10, 15, ...]
}
```

Response:

```json
{
  "success": true,
  "message": "Permissions assigned successfully",
  "data": {
    "role": "admin",
    "permissions_count": 25,
    "permissions": [
      { "id": 1, "name": "employee.view", ... },
      { "id": 2, "name": "employee.create", ... },
      ...
    ]
  }
}
```

**Example**: Give `HR` role access to `OKR` approvals:

```bash
# 1. Get all permissions
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/admin/permissions

# 2. Find permission IDs untuk OKR (cari "okr" in response)
# okr.view = 55, okr.approve = 58, etc

# 3. Get current HR role permissions
curl -H "Authorization: Bearer TOKEN" http://localhost:8000/api/admin/roles?with=permissions

# 4. Extract current permission IDs dari HR role result
# Misal: [1, 2, 3, ..., 100]

# 5. Add OKR permissions ke list
# [1, 2, 3, ..., 55, 58, 100]

# 6. Assign
curl -X POST http://localhost:8000/api/admin/roles/3/assign-permission \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"permissions": [1, 2, 3, ..., 55, 58, 100]}'
```

---

## Permission Groups (Kontrol granular)

Permissions organized by resource untuk mudah:

| Resource | Permissions | For Role |
|----------|------------|----------|
| **EMPLOYEE** | view, create, update, delete, onboard, offboard | Admin, HR |
| **LEAVE** | view, create, approve, policy.manage | Admin, HR, Manager, Employee |
| **ATTENDANCE** | view_all, delete, check_in, check_out, view_own | Admin, HR, Employee |
| **PAYROLL** | view, create, approve, pay, export | Admin, HR |
| **KPI** | view, create, update, approve | Admin, HR, Manager |
| **OKR** | view, create, submit, approve, progress | Admin, HR, Manager |
| **PERFORMANCE** | cycle (view, create, manage), review (view, create, submit, approve) | Admin, HR, Manager |
| **360_REVIEW** | view, create, provide_feedback, submit, approve | Admin, HR, Manager |
| **CALIBRATION** | view, create, participate, manage | Admin, HR, Manager |
| **RECRUITMENT** | opening (view, create, update), candidate (view, manage), interview (schedule), offer (create) | Admin, HR |
| **BENEFIT** | view, create, update, assign | Admin, HR |
| **CAREER** | idp (view, create), succession (view, manage) | Admin, HR, Manager |
| **REPORTING** | dashboard, attendance, leave, payroll, competency, lifecycle, assets | Admin, HR, Manager |
| **ADMIN** | user (view, assign_role), role (view, assign_permission), permission (view), audit (view, delete), email (manage), location (manage), schedule (manage), approval_flow (manage), biometric (manage), data import | **Super Admin ONLY** |

---

## Adding New Permissions (Developer Guide)

### Scenario: Add new "Training Approval" permission

**1. Edit** `app/Constants/Permissions.php`:

```php
const TRAINING = [
    'training.view' => 'View training programs',
    'training.create' => 'Create training program',
    'training.update' => 'Update training program',
    'training.delete' => 'Delete training program',
    'training.enroll' => 'Enroll employee in training',
    'training.approve' => 'Approve training requests', // ← NEW
];
```

**2. Update role mappings** (optional):

```php
'hr' => array_merge(
    ...existing...,
    ['training.approve'], // ← ADD THIS
    ...
),
```

**3. Run migration + seeder**:

```bash
php artisan migrate
php artisan db:seed --class=RbacSeeder
```

**4. Use in Controller**:

```php
// Check permission
if (!$request->user()->hasPermissionTo('training.approve')) {
    return $this->unauthorized('training.approve');
}
```

---

## Best Practices

### 1. Permission Naming Convention

**Format**: `<resource>.<action>`

```
✅ employee.view
✅ leave.approve
✅ okr.submit
❌ view_employee (too vague)
❌ can_approve_leave (avoid "can_" prefix)
```

### 2. Keep Descriptions Meaningful

```php
// ✅ Good
'performance.review.approve' => 'Approve performance reviews (final approval)',

// ❌ Bad
'performance.review.approve' => 'Approve review',
```

### 3. Granular > Generic

```php
// ✅ Good: Can customize per level
'okr.view', 'okr.create', 'okr.submit', 'okr.approve'

// ❌ Bad: All or nothing
'okr.manage'
```

### 4. Super Admin Always Has All

```php
// This is automatic, never remove:
'super_admin' => array_keys(self::all()),
```

### 5. Use Migration for Schema Changes

If adding new permission fields (future), use migration:

```bash
php artisan make:migration add_category_to_permissions_table
```

---

## Security Model

```
Routes (api.php)
    ↓
Middleware: role:admin,hr,super_admin
    ↓
Controller: hasPermissionTo('permission.name')
    ↓
Request validated ✅ or 403 Forbidden ❌
```

**Example in Controller**:

```php
class OKRController extends Controller
{
    public function store(Request $request)
    {
        // Option 1: Check single permission
        if (!$request->user()->hasPermissionTo('okr.create')) {
            return $this->unauthorized('okr.create');
        }

        // Option 2: Use middleware on routes (api.php)
        // Route::post('/okrs', [OKRController::class, 'store'])
        //     ->middleware('permission:okr.create');

        // Create OKR...
    }
}
```

---

## Troubleshooting

### Permission Not Found

**Error**: `SQLSTATE[HY000]: General error: 1030 Got an error reading communication packets`

**Cause**: Permission doesn't exist in DB

**Fix**:
```bash
# Check permission exists
SELECT * FROM permissions WHERE name = 'okr.create';

# If missing, add to Permissions.php and re-run seeder
php artisan db:seed --class=RbacSeeder
```

### User Can't Access Endpoint (403)

**Debug**:
```bash
# Check user's role
SELECT * FROM roles WHERE id = (SELECT role_id FROM users WHERE id = USER_ID);

# Check role's permissions
SELECT p.* FROM permissions p
  JOIN role_permissions rp ON p.id = rp.permission_id
  WHERE rp.role_id = ROLE_ID;

# Check controller checks permission
// In controller: dd($request->user()->permissions);
```

### Add Permission to Role (Quick Fix)

```bash
php artisan tinker

# Check permission exists
$perm = Permission::where('name', 'okr.create')->first();

# Get role
$role = Role::where('name', 'hr')->first();

# Attach
$role->permissions()->attach($perm);

# Verify
$role->permissions;
```

---

## Database Schema

### permissions table

```sql
CREATE TABLE permissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) UNIQUE NOT NULL,           -- e.g., 'employee.view'
  description TEXT,                             -- e.g., 'View employee list & details'
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### role_permissions table (pivot)

```sql
CREATE TABLE role_permissions (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  role_id BIGINT NOT NULL FOREIGN KEY → roles(id),
  permission_id BIGINT NOT NULL FOREIGN KEY → permissions(id),
  UNIQUE(role_id, permission_id),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

---

## Next Steps

1. ✅ Permission Registry system implemented & seeded
2. ✅ Super Admin can customize via API endpoints
3. ✅ Permission descriptions stored for audit trail
4. ⏳ (Optional) Create UI permission management panel
5. ⏳ (Optional) Add permission logging for change audits

---

## Summary

```
┌──────────────────────────────────────────────┐
│  Permission Registry System (Production)     │
├──────────────────────────────────────────────┤
│ ✅ Centralized in App\Constants\Permissions │
│ ✅ Seeded via RbacSeeder                     │
│ ✅ 100+ Permissions organized by resource   │
│ ✅ Descriptions for audit & UI              │
│ ✅ Role mappings customizable via API       │
│ ✅ Super Admin full control                 │
│ ✅ Zero hardcoding after initial setup      │
└──────────────────────────────────────────────┘
```

**Status**: 🚀 Ready for production!
