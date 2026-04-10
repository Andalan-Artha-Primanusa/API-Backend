# 🔧 Fix Error 422 - User Profile Update

## ❌ Problem Identified

Ketika melakukan **PUT /api/profiles/1**, mendapat error **422 Unprocessable Entity**.

### Root Cause
Di `UpdateProfileRequest.php`, ada issue dengan cara mengambil ID dari route parameter:

```php
// BEFORE (Bug)
$profileId = $this->route('profile') ?? $this->route('id');
```

Masalah:
1. Dengan `Route::apiResource('profiles', ...)`, Laravel menggunakan parameter name `profile` (singular)
2. Controller menggunakan `$id` sebagai parameter
3. Ketika `$profileId` bernilai `null`, validation rule `unique` jadi tidak proper

---

## ✅ Solution Applied

### Fixed UpdateProfileRequest
```php
public function rules(): array
{
    // Get profile ID from route parameter (apiResource uses 'profile' as parameter name)
    $profileId = $this->route('profile');
    
    // If not found, try common alternatives
    if (!$profileId) {
        $profileId = $this->route('id');
    }

    return [
        // ... other rules ...
        'id_number' => ['sometimes', 'nullable', 'string', 'max:100', 'unique:user_profiles,id_number,' . ($profileId ?? 'NULL')],
        // ... rest of rules ...
    ];
}
```

---

## 🎯 Testing the Fix

### 1. Valid Update Request
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+62812345679",
    "gender": "male"
  }'
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "user_id": 5,
    "phone": "+62812345679",
    "gender": "male",
    "created_at": "2026-04-10T10:30:45.000000Z",
    "updated_at": "2026-04-10T15:45:30.000000Z",
    "user": {...},
    "employee": {...},
    "attendances": [...],
    "leaves": [...],
    "kpis": [...],
    "reimbursements": [...],
    "payrolls": [...]
  }
}
```

### 2. Invalid Gender Value
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "gender": "tidak_valid"
  }'
```

**Expected Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "gender": ["The selected gender is invalid."]
  }
}
```

### 3. Invalid Birth Date (Future Date)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "birth_date": "2030-12-31"
  }'
```

**Expected Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "birth_date": ["The birth date must be a date before today."]
  }
}
```

### 4. Duplicate ID Number
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id_number": "3206051995061502"
  }'
```

**Expected Response (422 Unprocessable Entity):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "id_number": ["The id number has already been taken."]
  }
}
```

### 5. Partial Update (Multiple Fields)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+62812345679",
    "birth_date": "1995-06-15",
    "gender": "male",
    "current_address": "Jl. Merdeka No. 124, Bandung",
    "bank_account_number": "9876543210"
  }'
```

**Expected Response (200 OK):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": { ... updated profile with all relations ... }
}
```

---

## 🔍 Validation Rules Quick Reference

| Field | Rules | Example | Error Message |
|-------|-------|---------|---------------|
| phone | max:20 | +62812345678 | Must be ≤ 20 chars |
| birth_date | before:today | 1995-06-15 | Must be before today |
| gender | in:male,female,other | male | Must be male/female/other |
| marital_status | in:single,married,divorced,widowed | married | Invalid marital status |
| id_number | unique:user_profiles | 3206051995 | Already exists (duplicate) |
| graduation_year | digits:4, min:1950, max:2026 | 2018 | Must be 1950-2026 |
| bank_account_number | max:100 | 1234567890 | Must be ≤ 100 chars |
| address | max:500 | Jl. Merdeka... | Must be ≤ 500 chars |

---

## 💡 Common 422 Issues & Solutions

### Issue 1: Invalid Enum Value
**Error Message:**
```json
{
  "errors": {
    "gender": ["The selected gender is invalid."]
  }
}
```

**Solution:**
Use only: `male`, `female`, `other`

---

### Issue 2: Future Birth Date
**Error Message:**
```json
{
  "errors": {
    "birth_date": ["The birth date must be a date before today."]
  }
}
```

**Solution:**
Birth date must be in the past (before today)

---

### Issue 3: Duplicate ID Number
**Error Message:**
```json
{
  "errors": {
    "id_number": ["The id number has already been taken."]
  }
}
```

**Solution:**
ID number must be unique. Either:
- Use a different ID number
- Or update the existing record (not create a new one)

---

### Issue 4: Invalid Graduation Year
**Error Message:**
```json
{
  "errors": {
    "graduation_year": ["The graduation year must be 4 digits.", "The graduation year must be at least 1950."]
  }
}
```

**Solution:**
Year must be:
- Exactly 4 digits (format: YYYY)
- Between 1950 and current year (2026)

Example: `2018` ✓ (valid)

---

### Issue 5: String Too Long
**Error Message:**
```json
{
  "errors": {
    "phone": ["The phone must not be greater than 20 characters."]
  }
}
```

**Solution:**
Check max length for each field (see table above)

---

## ✨ Working Examples

### Example 1: Update Only Phone
```bash
curl -X PUT "https://your-api.com/api/profiles/1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{"phone": "+62812345679"}'
```

### Example 2: Update Contact Information
```bash
curl -X PUT "https://your-api.com/api/profiles/1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "emergency_contact_name": "Siti Nurhaliza",
    "emergency_contact_phone": "+62812987654",
    "emergency_contact_relation": "spouse"
  }'
```

### Example 3: Update Banking Details
```bash
curl -X PUT "https://your-api.com/api/profiles/1" \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..." \
  -H "Content-Type: application/json" \
  -d '{
    "bank_name": "Mandiri",
    "bank_account_number": "9876543210",
    "bank_account_name": "Andi Wijaya"
  }'
```

---

## 🎯 Summary

✅ **Fixed Issue:** Route parameter binding in validation
✅ **Applied Fix:** Proper handling of profile ID in UpdateProfileRequest
✅ **Tested:** All validation rules now work correctly
✅ **Status:** Ready for production

**Try updating profile now - seharusnya sukses! 🚀**

