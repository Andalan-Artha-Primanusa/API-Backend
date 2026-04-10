# 📝 User Profile - JSON Payload Examples

## 🔗 Endpoints
- **CREATE**: `POST /api/profiles`
- **UPDATE**: `PUT /api/profiles/{id}`
- **GET**: `GET /api/profiles` or `GET /api/profiles/{id}`
- **DELETE**: `DELETE /api/profiles/{id}`

---

## 📤 CREATE - POST /api/profiles

### Complete Payload (All Fields)
```json
{
  "phone": "+62812345678",
  "address": "Jl. Merdeka No. 123",
  "birth_date": "1995-06-15",
  "gender": "male",
  "marital_status": "married",
  "religion": "Islam",
  "nationality": "Indonesian",
  "id_number": "3206051995061500",
  "emergency_contact_name": "Siti Nurhaliza",
  "emergency_contact_phone": "+62812987654",
  "emergency_contact_relation": "spouse",
  "current_address": "Jl. Merdeka No. 123, Bandung, Jawa Barat 40132",
  "permanent_address": "Jl. Gatot Subroto No. 45, Jakarta, DKI Jakarta 12345",
  "bank_name": "BCA",
  "bank_account_number": "1234567890",
  "bank_account_name": "Andi Wijaya",
  "tax_number": "123456789012345",
  "last_education": "S2 Teknik Informatika",
  "institution_name": "Universitas Indonesia",
  "graduation_year": 2018,
  "profile_photo_path": "uploads/profiles/user-123-photo.jpg"
}
```

### Minimal Payload (All Optional)
```json
{}
```

### Common Payload (Typical Data)
```json
{
  "phone": "+62812345678",
  "birth_date": "1995-06-15",
  "gender": "male",
  "marital_status": "married",
  "religion": "Islam",
  "nationality": "Indonesian",
  "id_number": "3206051995061500",
  "current_address": "Jl. Merdeka No. 123, Bandung, Jawa Barat",
  "bank_name": "BCA",
  "bank_account_number": "1234567890",
  "bank_account_name": "Andi Wijaya",
  "last_education": "S2 Teknik Informatika",
  "institution_name": "Universitas Indonesia",
  "graduation_year": 2018
}
```

---

## ✏️ UPDATE - PUT /api/profiles/{id}

### Update All Fields
```json
{
  "phone": "+62812345679",
  "address": "Jl. Merdeka No. 124",
  "birth_date": "1995-06-15",
  "gender": "male",
  "marital_status": "married",
  "religion": "Islam",
  "nationality": "Indonesian",
  "id_number": "3206051995061500",
  "emergency_contact_name": "Siti Nurhaliza",
  "emergency_contact_phone": "+62812987654",
  "emergency_contact_relation": "spouse",
  "current_address": "Jl. Merdeka No. 124, Bandung, Jawa Barat 40132",
  "permanent_address": "Jl. Gatot Subroto No. 45, Jakarta, DKI Jakarta 12345",
  "bank_name": "BCA",
  "bank_account_number": "1234567890",
  "bank_account_name": "Andi Wijaya",
  "tax_number": "123456789012345",
  "last_education": "S2 Teknik Informatika",
  "institution_name": "Universitas Indonesia",
  "graduation_year": 2018,
  "profile_photo_path": "uploads/profiles/user-123-photo-2024.jpg"
}
```

### Partial Update (Phone Only)
```json
{
  "phone": "+62812345679"
}
```

### Update Contact & Address Info
```json
{
  "emergency_contact_name": "Siti Nurhaliza",
  "emergency_contact_phone": "+62812987654",
  "emergency_contact_relation": "spouse",
  "current_address": "Jl. Merdeka No. 124, Bandung, Jawa Barat 40132",
  "permanent_address": "Jl. Gatot Subroto No. 45, Jakarta, DKI Jakarta 12345"
}
```

### Update Banking & Tax Info
```json
{
  "bank_name": "Mandiri",
  "bank_account_number": "9876543210",
  "bank_account_name": "Andi Wijaya",
  "tax_number": "098765432109876"
}
```

### Update Personal & Education Info
```json
{
  "birth_date": "1995-06-15",
  "gender": "male",
  "religion": "Islam",
  "marital_status": "married",
  "nationality": "Indonesian",
  "last_education": "S2 Teknik Informatika",
  "institution_name": "Universitas Indonesia",
  "graduation_year": 2018
}
```

---

## 📋 Field Validation Rules

| Field | Type | Max Length | Rules | Example |
|-------|------|-----------|-------|---------|
| phone | string | 20 | nullable | +62812345678 |
| address | string | 500 | nullable | Jl. Merdeka No. 123 |
| birth_date | date | - | nullable, before:today | 1995-06-15 |
| gender | string | - | nullable, in:male,female,other | male |
| marital_status | string | - | nullable, in:single,married,divorced,widowed | married |
| religion | string | 50 | nullable | Islam |
| nationality | string | 100 | nullable | Indonesian |
| id_number | string | 100 | nullable, unique | 3206051995061500 |
| emergency_contact_name | string | 255 | nullable | Siti Nurhaliza |
| emergency_contact_phone | string | 20 | nullable | +62812987654 |
| emergency_contact_relation | string | 100 | nullable | spouse |
| current_address | string | 1000 | nullable | Jl. Merdeka No. 123, Bandung... |
| permanent_address | string | 1000 | nullable | Jl. Gatot Subroto No. 45... |
| bank_name | string | 100 | nullable | BCA |
| bank_account_number | string | 100 | nullable | 1234567890 |
| bank_account_name | string | 255 | nullable | Andi Wijaya |
| tax_number | string | 100 | nullable | 123456789012345 |
| last_education | string | 100 | nullable | S2 Teknik Informatika |
| institution_name | string | 255 | nullable | Universitas Indonesia |
| graduation_year | integer | 4 digits | nullable, min:1950, max:current_year | 2018 |
| profile_photo_path | string | 255 | nullable | uploads/profiles/photo.jpg |

---

## ✅ Response Format

### Success Response (201 Created / 200 OK)
```json
{
  "success": true,
  "message": "Profile created successfully",
  "data": {
    "id": 1,
    "user_id": 5,
    "phone": "+62812345678",
    "address": "Jl. Merdeka No. 123",
    "birth_date": "1995-06-15",
    "gender": "male",
    "marital_status": "married",
    "religion": "Islam",
    "nationality": "Indonesian",
    "id_number": "3206051995061500",
    "emergency_contact_name": "Siti Nurhaliza",
    "emergency_contact_phone": "+62812987654",
    "emergency_contact_relation": "spouse",
    "current_address": "Jl. Merdeka No. 123, Bandung, Jawa Barat 40132",
    "permanent_address": "Jl. Gatot Subroto No. 45, Jakarta, DKI Jakarta 12345",
    "bank_name": "BCA",
    "bank_account_number": "1234567890",
    "bank_account_name": "Andi Wijaya",
    "tax_number": "123456789012345",
    "last_education": "S2 Teknik Informatika",
    "institution_name": "Universitas Indonesia",
    "graduation_year": 2018,
    "profile_photo_path": "uploads/profiles/user-123-photo.jpg",
    "created_at": "2026-04-10T10:30:45.000000Z",
    "updated_at": "2026-04-10T10:30:45.000000Z",
    "user": {
      "id": 5,
      "name": "Andi Wijaya",
      "email": "andi@example.com",
      "roles": [
        {
          "id": 2,
          "name": "employee",
          "permissions": [...]
        }
      ],
      "profile": {...}
    },
    "employee": {
      "id": 3,
      "user_id": 5,
      "employee_code": "EMP-0005",
      "position": "Software Engineer",
      "department": "IT",
      "salary": "8000000.00",
      "manager": {...}
    },
    "attendances": [...],
    "leaves": [...],
    "kpis": [...],
    "reimbursements": [...],
    "payrolls": [...]
  }
}
```

### Error Response (400/422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "birth_date": ["The birth date must be a date before today."],
    "graduation_year": ["The graduation year must be 4 digits."],
    "id_number": ["The id number must be unique."]
  }
}
```

### Error Response (404)
```json
{
  "success": false,
  "message": "Profile not found",
  "data": null
}
```

### Error Response (403 Forbidden)
```json
{
  "success": false,
  "message": "Forbidden",
  "error": "No permission"
}
```

---

## 🔐 Authorization & Headers

### Required Headers
```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

### GET Request Example (cURL)
```bash
curl -X GET "http://localhost:8000/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

### CREATE Request Example (cURL)
```bash
curl -X POST "http://localhost:8000/api/profiles" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+62812345678",
    "birth_date": "1995-06-15",
    "gender": "male",
    "bank_account_number": "1234567890",
    "bank_account_name": "Andi Wijaya"
  }'
```

### UPDATE Request Example (cURL)
```bash
curl -X PUT "http://localhost:8000/api/profiles/1" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+62812345679",
    "bank_account_number": "9876543210"
  }'
```

---

## 🎯 Valid Values for Enum Fields

### Gender
- `male`
- `female`
- `other`

### Marital Status
- `single`
- `married`
- `divorced`
- `widowed`

### Common Religions
- `Islam`
- `Christian`
- `Catholic`
- `Hindu`
- `Buddhist`
- `Confucian`
- `Other`

### Common Education Levels
- `SD / Sekolah Dasar`
- `SMP / Sekolah Menengah Pertama`
- `SMA / Sekolah Menengah Atas`
- `D1 / Diploma Satu`
- `D2 / Diploma Dua`
- `D3 / Diploma Tiga`
- `S1 / Sarjana`
- `S2 / Magister`
- `S3 / Doktor`

### Common Bank Names (Indonesia)
- `BCA`
- `Mandiri`
- `BRI`
- `BTN`
- `CIMB Niaga`
- `Permata`
- `Maybank`

---

## 💡 Notes

1. **All fields are nullable** - You don't need to send all fields
2. **phone numeric only** - Use international format like +62...
3. **birthdate validation** - Must be before today (no future dates)
4. **id_number must be unique** - Cannot duplicate
5. **graduation_year range** - 1950 to current year only
6. **Profile belongs to user** - Automatically assigned to authenticated user on CREATE
7. **Complete response includes relations** -GET returns user, employee, attendances, leaves, kpis, reimbursements, payrolls
8. **UPDATE is partial** - Only send fields you want to update
9. **DELETE returns deleted data** - Shows full data that was deleted

