# 📊 Mapping Relasi Lengkap - Users & User Profiles

## ✅ Model Dictionary & Relationship Chain

### 1. **User** (Central)
```
User ↔ UserProfile (1:1 HasOne)
User ↔ Employee (1:1 HasOne)
User ↔ Role (N:N BelongsToMany via user_roles)
User ↔ Permission (N:N through roles via role_permissions)
```

### 2. **UserProfile** (Extended User Data)
```
UserProfile → User (BelongsTo)
UserProfile → Employee (HasOne via user_id)
UserProfile → Attendance (HasMany via user_id)
UserProfile → Leave (HasMany via user_id)
UserProfile → Role (BelongsToMany via user_id through user_roles)
UserProfile → Kpi (HasManyThrough: Employee → Kpi)
UserProfile → Payroll (HasManyThrough: Employee → Payroll)
UserProfile → Reimbursement (HasManyThrough: Employee → Reimbursement)
```

### 3. **Employee** (Employee Record)
```
Employee → User (BelongsTo)
Employee → User as Manager (BelongsTo via manager_id)
Employee → UserProfile (HasOne via same user_id)
Employee → Kpi (HasMany)
Employee → Payroll (HasMany)
Employee → Reimbursement (HasMany)
```

### 4. **Attendance** (Kehadiran)
```
Attendance → User (BelongsTo)
User → UserProfile → Attendance chain
```

### 5. **Leave** (Cuti)
```
Leave → User (BelongsTo)
Leave → Employee (BelongsTo)
Leave → User as Approver (BelongsTo via approved_by)
Leave → ApprovalFlow (BelongsTo)
Complete chain: User → UserProfile ↔ Employee ↔ Leave
```

### 6. **Kpi** (Key Performance Indicator)
```
Kpi → Employee (BelongsTo)
Complete chain: User → UserProfile → Employee → Kpi
```

### 7. **Payroll** (Gaji)
```
Payroll → Employee (BelongsTo)
Payroll → PayrollDetail (HasMany)
Complete chain: User → UserProfile → Employee → Payroll → PayrollDetail
```

### 8. **PayrollDetail** (Detail Gaji)
```
PayrollDetail → Payroll (BelongsTo)
Complete chain: User → UserProfile → Employee → Payroll → PayrollDetail
```

### 9. **Reimbursement** (Penggantian Biaya)
```
Reimbursement → Employee (BelongsTo)
Reimbursement → User as Approver (BelongsTo via approved_by)
Complete chain: User → UserProfile → Employee → Reimbursement
```

### 10. **Role** (Peran)
```
Role → Permission (BelongsToMany via role_permissions)
Role → User (BelongsToMany via user_roles)
Role → ApprovalStep (HasMany)
```

### 11. **Permission** (Izin/Hak Akses)
```
Permission → Role (BelongsToMany via role_permissions)
```

### 12. **ApprovalFlow** (Alur Persetujuan)
```
ApprovalFlow → ApprovalStep (HasMany)
ApprovalFlow → Leave (HasMany via approval_flow_id)
ApprovalFlow → Role (through ApprovalStep)
```

### 13. **ApprovalStep** (Langkah Persetujuan)
```
ApprovalStep → ApprovalFlow (BelongsTo)
ApprovalStep → Role (BelongsTo)
```

### 14. **Location** (Lokasi Kantor)
```
Standalone - untuk geofencing check-in/check-out
Tidak perlu relasi langsung dengan User/UserProfile
```

---

## 📈 Relasi ke User/UserProfile - Status

| Model | Relasi ke User | Relasi ke UserProfile | Status |
|-------|----------------|----------------------|--------|
| Employee | ✅ BelongsTo | ✅ HasOne (same user_id) | ✓ LENGKAP |
| UserProfile | ✅ BelongsTo | ✅ Self | ✓ LENGKAP |
| Attendance | ✅ BelongsTo | ✅ Via User | ✓ LENGKAP |
| Leave | ✅ BelongsTo | ✅ Via Employee | ✓ LENGKAP |
| Kpi | ✅ Via Employee | ✅ Via Employee | ✓ LENGKAP |
| Payroll | ✅ Via Employee | ✅ Via Employee | ✓ LENGKAP |
| PayrollDetail | ✅ Via Payroll | ✅ Via Payroll | ✓ LENGKAP |
| Reimbursement | ✅ Via Employee | ✅ Via Employee | ✓ LENGKAP |
| Role | ✅ BelongsToMany | ✅ BelongsToMany | ✓ LENGKAP |
| Permission | ✅ Via Role | ✅ Via Role | ✓ LENGKAP |
| ApprovalFlow | ✅ Via ApprovalStep | ✅ Via ApprovalStep | ✓ LENGKAP |
| ApprovalStep | ✅ Via ApprovalFlow | ✅ Via ApprovalFlow | ✓ LENGKAP |
| Location | ❌ None | ❌ None | 🔹 BY DESIGN |

---

## 🎯 Kesimpulan

### ✅ Semuanya SUDAH TERRELASI dengan User/UserProfile! 

Tidak ada model yang "orphan" atau tidak terhubung. Setiap model punya koneksi:
- **Direct relasi**: User, UserProfile, Employee, Attendance, Leave
- **Via Employee**: Kpi, Payroll, PayrollDetail, Reimbursement  
- **Via User (Approver)**: Leave, Reimbursement
- **Via Role/Permission**: Untuk RBAC checks
- **Via ApprovalFlow**: Leave approval workflow
- **Standalone**: Location (by design, untuk geofencing)

### 📊 Relasi Chart (Simplified)

```
┌─────────────────┐
│   User (Core)   │
├─────────────────┤
│  • HasOne: Profile
│  • HasOne: Employee
│  • BelongsToMany: Role
│  • HasMany: TeamMembers (manager)
└────────┬────────┘
         │
    ┌────┴─────────────────────┬──────────────┐
    │                          │              │
    ▼                          ▼              ▼
┌─────────────┐          ┌──────────┐   ┌──────────┐
│UserProfile  │          │Employee  │   │Attendance│
│(Extended)   │          │          │   └──────────┘
└─────────────┘          │ • HasMany: Kpi
                         │ • HasMany: Payroll
                         │ • HasMany: Reimbursement
                         └──────────┬────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    │               │               │
                    ▼               ▼               ▼
                 ┌─────┐         ┌──────┐      ┌──────────────┐
                 │ Kpi │         │Leave │      │Reimbursement │
                 └─────┘         └──────┘      └──────────────┘
                                    │
                                    ▼
                            ┌──────────────────┐
                            │ ApprovalFlow     │
                            │ • HasMany: Steps │
                            └──────────────────┘
                                    │
                                    ▼
                            ┌──────────────────┐
                            │ ApprovalStep     │
                            │ • BelongsTo Role │
                            └──────────────────┘
                                    │
                                    ▼
                            ┌──────────────────┐
                            │ Role & Permission│
                            └──────────────────┘
```

---

## 🔒 Security & Eager Loading Status

Semua Controller sudah implement:
- ✅ Eager loading yang benar (menghindari N+1 query)
- ✅ Permission checking via middleware
- ✅ Role-based access control (RBAC)
- ✅ Data return lengkap dengan profile pada GET/CREATE/UPDATE/DELETE

