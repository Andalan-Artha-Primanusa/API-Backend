# POSTMAN CURL COMMANDS - HRIS API Testing

**Base URL:** `http://localhost:8000/api`

> Replace `{TOKEN}` with your actual Bearer token from login response
> Replace `{id}`, `{employee_id}`, `{payroll_id}` with actual IDs from your database

---

## 📌 PUBLIC ROUTES (No Authentication)

### 1. Health Check
```bash
curl -X GET "http://localhost:8000/api/" \
  -H "Accept: application/json"
```

### 2. Register
```bash
curl -X POST "http://localhost:8000/api/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 3. Login
```bash
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### 4. Google Auth Redirect
```bash
curl -X GET "http://localhost:8000/api/auth/google" \
  -H "Accept: application/json"
```

### 5. Google Auth Callback
```bash
curl -X GET "http://localhost:8000/api/auth/google/callback?code=CODE&state=STATE" \
  -H "Accept: application/json"
```

---

## 🔐 PROTECTED ROUTES (Requires Authorization)

**Header for all protected routes:**
```
Authorization: Bearer {TOKEN}
```

---

## 👤 USER PROFILE

### Get All Profiles
```bash
curl -X GET "http://localhost:8000/api/profiles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Profile
```bash
curl -X POST "http://localhost:8000/api/profiles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "+628123456789",
    "address": "123 Main Street",
    "city": "Jakarta",
    "province": "DKI Jakarta",
    "postal_code": "12345"
  }'
```

### Get Profile Detail
```bash
curl -X GET "http://localhost:8000/api/profiles/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Profile
```bash
curl -X PUT "http://localhost:8000/api/profiles/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "+628123456789",
    "address": "456 New Street",
    "city": "Bandung",
    "province": "Jawa Barat",
    "postal_code": "40000"
  }'
```

### Delete Profile
```bash
curl -X DELETE "http://localhost:8000/api/profiles/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 👤 EMPLOYEE SELF-SERVICE (ESS) - MY DATA

### Get My KPI
```bash
curl -X GET "http://localhost:8000/api/my/kpi" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Submit My KPI
```bash
curl -X POST "http://localhost:8000/api/my/kpi/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Reimbursements
```bash
curl -X GET "http://localhost:8000/api/my/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter My Reimbursements by Status
```bash
curl -X GET "http://localhost:8000/api/my/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create My Reimbursement
```bash
curl -X POST "http://localhost:8000/api/my/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Business Trip Expenses",
    "description": "Travel to Jakarta for client meeting",
    "amount": 500000,
    "category": "travel",
    "expense_date": "2026-04-09",
    "receipt_path": "/receipts/travel_001.pdf"
  }'
```

### Submit My Reimbursement
```bash
curl -X POST "http://localhost:8000/api/my/reimbursements/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Payroll
```bash
curl -X GET "http://localhost:8000/api/my/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Leaves
```bash
curl -X GET "http://localhost:8000/api/leaves/my" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Leave Balance
```bash
curl -X GET "http://localhost:8000/api/leaves/balance" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Check-in Attendance
```bash
curl -X POST "http://localhost:8000/api/attendance/check-in" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "latitude": -6.200000,
    "longitude": 106.816666
  }'
```

### Check-out Attendance
```bash
curl -X POST "http://localhost:8000/api/attendance/check-out" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Attendance History
```bash
curl -X GET "http://localhost:8000/api/attendance/history" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Today's Attendance
```bash
curl -X GET "http://localhost:8000/api/attendance/today" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📋 LEAVE MANAGEMENT

### Get All Leaves
```bash
curl -X GET "http://localhost:8000/api/leaves" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Leave Request
```bash
curl -X POST "http://localhost:8000/api/leaves" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "annual",
    "start_date": "2026-05-01",
    "end_date": "2026-05-05",
    "total_days": 5,
    "reason": "Family vacation"
  }'
```

### Get Leave Calendar
```bash
curl -X GET "http://localhost:8000/api/leaves/calendar" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Leave Detail
```bash
curl -X GET "http://localhost:8000/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Leave Request
```bash
curl -X PUT "http://localhost:8000/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "start_date": "2026-05-02",
    "end_date": "2026-05-06",
    "total_days": 5,
    "reason": "Updated reason"
  }'
```

### Delete Leave Request
```bash
curl -X DELETE "http://localhost:8000/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Pending Leaves (Manager/HR/Admin)
```bash
curl -X GET "http://localhost:8000/api/leaves/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Leave (Manager/HR/Admin)
```bash
curl -X PUT "http://localhost:8000/api/leaves/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Approved"
  }'
```

### Reject Leave (Manager/HR/Admin)
```bash
curl -X PUT "http://localhost:8000/api/leaves/{id}/reject" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Cannot approve at this time"
  }'
```

---

## 👥 EMPLOYEE MANAGEMENT

### Get All Employees
```bash
curl -X GET "http://localhost:8000/api/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Employee
```bash
curl -X POST "http://localhost:8000/api/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_id": 2,
    "employee_code": "EMP-0002",
    "position": "Manager",
    "department": "IT",
    "hire_date": "2025-01-15",
    "salary": 50000000
  }'
```

### Get Employee Detail
```bash
curl -X GET "http://localhost:8000/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Employee
```bash
curl -X PUT "http://localhost:8000/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "position": "Senior Manager",
    "department": "IT",
    "salary": 60000000
  }'
```

### Delete Employee
```bash
curl -X DELETE "http://localhost:8000/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📍 ATTENDANCE MANAGEMENT

### Get All Attendance Records (Admin)
```bash
curl -X GET "http://localhost:8000/api/attendance/all" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Attendance Detail
```bash
curl -X GET "http://localhost:8000/api/attendance/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Delete Attendance Record
```bash
curl -X DELETE "http://localhost:8000/api/attendance/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💰 PAYROLL MANAGEMENT (HR/Admin)

### Get All Payroll
```bash
curl -X GET "http://localhost:8000/api/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Payroll
```bash
curl -X POST "http://localhost:8000/api/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "period": "2026-04",
    "allowance": 2000000,
    "bonus": 500000
  }'
```

### Generate Monthly Payroll (Bulk)
```bash
curl -X POST "http://localhost:8000/api/payroll/generate/monthly" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "period": "2026-04"
  }'
```

### Get Payroll Detail
```bash
curl -X GET "http://localhost:8000/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Payroll
```bash
curl -X PUT "http://localhost:8000/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "allowance": 2500000,
    "bonus": 750000
  }'
```

### Delete Payroll
```bash
curl -X DELETE "http://localhost:8000/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Payroll
```bash
curl -X POST "http://localhost:8000/api/payroll/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Mark Payroll as Paid
```bash
curl -X POST "http://localhost:8000/api/payroll/{id}/pay" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💳 PAYROLL DETAILS MANAGEMENT (HR/Admin)

### Get Payroll Details
```bash
curl -X GET "http://localhost:8000/api/payroll-details/{payroll_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Add Payroll Details (Bulk)
```bash
curl -X POST "http://localhost:8000/api/payroll-details" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "payroll_id": 1,
    "details": [
      {
        "type": "allowance",
        "name": "Housing Allowance",
        "amount": 2000000
      },
      {
        "type": "deduction",
        "name": "Tax",
        "amount": 500000
      }
    ]
  }'
```

### Update Payroll Detail (Single)
```bash
curl -X PUT "http://localhost:8000/api/payroll-details/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "type": "allowance",
    "name": "Housing Allowance",
    "amount": 2500000
  }'
```

### Bulk Update Payroll Details
```bash
curl -X POST "http://localhost:8000/api/payroll-details/bulk-update" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "details": [
      {
        "id": 1,
        "amount": 2500000
      },
      {
        "id": 2,
        "amount": 600000
      }
    ]
  }'
```

### Delete Payroll Detail
```bash
curl -X DELETE "http://localhost:8000/api/payroll-details/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💼 KPI MANAGEMENT (Manager/HR/Admin)

### Get All KPIs
```bash
curl -X GET "http://localhost:8000/api/kpis" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create KPI
```bash
curl -X POST "http://localhost:8000/api/kpis" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "title": "Sales Target",
    "description": "Achieve 100 new customers",
    "target": 100
  }'
```

### Get KPI Detail
```bash
curl -X GET "http://localhost:8000/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update KPI
```bash
curl -X PUT "http://localhost:8000/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Sales Target",
    "target": 100,
    "achievement": 85
  }'
```

### Delete KPI
```bash
curl -X DELETE "http://localhost:8000/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get KPIs by Employee
```bash
curl -X GET "http://localhost:8000/api/kpis/employee/{employee_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve KPI
```bash
curl -X PUT "http://localhost:8000/api/kpis/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My KPIs
```bash
curl -X GET "http://localhost:8000/api/my/kpi" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Submit KPI for Review
```bash
curl -X POST "http://localhost:8000/api/my/kpi/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💰 REIMBURSEMENT MANAGEMENT (Manager/HR/Admin)

### Get All Reimbursements (with filters)
```bash
curl -X GET "http://localhost:8000/api/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Status
```bash
curl -X GET "http://localhost:8000/api/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Category
```bash
curl -X GET "http://localhost:8000/api/reimbursements?category=travel" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Employee
```bash
curl -X GET "http://localhost:8000/api/reimbursements?employee_id=1" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Reimbursement (by Manager/HR)
```bash
curl -X POST "http://localhost:8000/api/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "title": "Office Supplies",
    "description": "Monthly office supplies",
    "amount": 1000000,
    "category": "office_supplies",
    "expense_date": "2026-04-09",
    "receipt_path": "/receipts/office_001.pdf"
  }'
```

### Get Reimbursement Detail
```bash
curl -X GET "http://localhost:8000/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Reimbursement
```bash
curl -X PUT "http://localhost:8000/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Office Supplies Updated",
    "amount": 1200000,
    "category": "office_supplies"
  }'
```

### Delete Reimbursement
```bash
curl -X DELETE "http://localhost:8000/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Reimbursement
```bash
curl -X PUT "http://localhost:8000/api/reimbursements/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Approved"
  }'
```

### Reject Reimbursement
```bash
curl -X PUT "http://localhost:8000/api/reimbursements/{id}/reject" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Missing receipt"
  }'
```

### Mark Reimbursement as Paid
```bash
curl -X PUT "http://localhost:8000/api/reimbursements/{id}/mark-paid" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Pending Reimbursements
```bash
curl -X GET "http://localhost:8000/api/reimbursements/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Reimbursements by Employee
```bash
curl -X GET "http://localhost:8000/api/reimbursements/employee/{employee_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Reimbursement Statistics
```bash
curl -X GET "http://localhost:8000/api/reimbursements/statistics?employee_id=1" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📍 LOCATION MANAGEMENT (Admin)

### Get All Locations
```bash
curl -X GET "http://localhost:8000/api/locations" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Location
```bash
curl -X POST "http://localhost:8000/api/locations" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jakarta Office",
    "latitude": -6.200000,
    "longitude": 106.816666,
    "radius": 100
  }'
```

### Get Location Detail
```bash
curl -X GET "http://localhost:8000/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Location
```bash
curl -X PUT "http://localhost:8000/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jakarta Main Office",
    "radius": 150
  }'
```

### Delete Location
```bash
curl -X DELETE "http://localhost:8000/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 👨‍💼 USER MANAGEMENT (Admin)

### Get All Users
```bash
curl -X GET "http://localhost:8000/api/admin/users" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assign Roles to User
```bash
curl -X POST "http://localhost:8000/api/admin/users/{id}/assign-role" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "role_ids": [2, 3]
  }'
```

---

## 🔐 ROLE & PERMISSION MANAGEMENT (Admin)

### Get All Roles
```bash
curl -X GET "http://localhost:8000/api/admin/roles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assign Permissions to Role
```bash
curl -X POST "http://localhost:8000/api/admin/roles/{id}/assign-permission" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "permission_ids": [1, 2, 3]
  }'
```

### Get All Permissions
```bash
curl -X GET "http://localhost:8000/api/admin/permissions" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 🎯 LOGOUT

### Logout
```bash
curl -X POST "http://localhost:8000/api/logout" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📝 NOTES FOR TESTING

1. **Get Token First**: Login first to get your token using the Login endpoint
2. **Replace Placeholders**: 
   - `{TOKEN}` → Use token from login response
   - `{id}` → Use actual ID from database
   - `{employee_id}` → Use actual employee ID
   - `{payroll_id}` → Use actual payroll ID

3. **Content-Type**: Always include `Content-Type: application/json` for POST/PUT requests

4. **Test Order** (Recommended):
   - Register / Login (get token)
   - Create Employee
   - Create Payroll
   - Create KPI
   - Create Reimbursement
   - Check-in/Check-out Attendance
   - Request Leave
   - Approve/Reject (with HR role)

5. **Common Response Format**:
   ```json
   {
     "success": true/false,
     "message": "Action message",
     "data": {}
   }
   ```

---

## 🚀 IMPORT TO POSTMAN

1. Open Postman
2. Create new Environment with variables:
   - `base_url`: `http://localhost:8000/api`
   - `token`: (will be filled after login)

3. Update curl commands to use:
   - `{{base_url}}` instead of `http://localhost:8000/api`
   - `{{token}}` instead of `{TOKEN}`

4. After login, set token in environment variable for reuse
