# POSTMAN CURL COMMANDS - HRIS API Testing

**Base URL:** `https://moccasin-crab-693879.hostingersite.com/api/`

> Replace `{TOKEN}` with your actual Bearer token from login response
> Replace `{id}`, `{employee_id}`, `{payroll_id}` with actual IDs from your database

---

## 📌 PUBLIC ROUTES (No Authentication)

### 1. Health Check
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/" \
  -H "Accept: application/json"
```

### 2. Register
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/register" \
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
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### 4. Google Auth Redirect
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/auth/google" \
  -H "Accept: application/json"
```

### 5. Google Auth Callback
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/auth/google/callback?code=CODE&state=STATE" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/profiles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Profile
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/profiles" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/profiles/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Profile
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/profiles/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/profiles/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 👤 EMPLOYEE SELF-SERVICE (ESS) - MY DATA

### Get My KPI
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/kpi" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Submit My KPI
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/kpi/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Reimbursements
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter My Reimbursements by Status
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create My Reimbursement
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements" \
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
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Payroll
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Payroll Slip
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/slip" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Export My Payroll Slip CSV
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/export" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: text/csv"
```

### Export My Payroll Slip PDF
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf"
```

### Get My Leaves
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/my" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Leave Balance
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/balance" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Leave Policies (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leave-policies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Leave Policy (HR/Admin)
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/leave-policies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "year": 2026,
    "annual_allowance": 12,
    "carry_over_allowance": 5,
    "max_pending_days": 30,
    "active": true,
    "notes": "Default leave policy for 2026"
  }'
```

### Update Leave Policy (HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leave-policies/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "annual_allowance": 14,
    "carry_over_allowance": 3,
    "max_pending_days": 30,
    "active": true
  }'
```

### Delete Leave Policy (HR/Admin)
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/leave-policies/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 🧰 ASSET MANAGEMENT

### Get All Assets (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Asset (HR/Admin)
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "AST-0001",
    "name": "Laptop Dell Latitude",
    "category": "laptop",
    "brand": "Dell",
    "model": "Latitude 5440",
    "serial_number": "SN123456789",
    "condition": "good",
    "status": "available",
    "purchase_date": "2026-04-13",
    "purchase_price": 15000000,
    "notes": "Assigned to IT inventory"
  }'
```

### Assign Asset to Employee
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/assets/{id}/assign" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "assignment_note": "Assigned for daily work",
    "assigned_at": "2026-04-13 09:00:00"
  }'
```

### Return Asset
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/assets/assignments/{assignmentId}/return" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "return_note": "Returned during offboarding",
    "returned_at": "2026-08-01 17:00:00",
    "condition": "good"
  }'
```

### Get My Assets
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📄 DOCUMENT MANAGEMENT

### Get My Documents
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/documents" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Upload My Document
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/documents" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "title=Employment Contract" \
  -F "document_type=contract" \
  -F "category=hr" \
  -F "expires_at=2027-04-13" \
  -F "is_confidential=1" \
  -F "file=@C:/path/to/contract.pdf"
```

### Get All Employee Documents (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/documents?status=pending&per_page=15" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Review Document (HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/documents/{id}/review" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "approved",
    "review_notes": "Verified and approved for employee file"
  }'
```

### Get Expiring Documents (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/documents/expiring?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 🎫 HELPDESK REQUESTS

### Get My Requests
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/requests" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create My Request
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/requests" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "category": "letter_request",
    "priority": "medium",
    "subject": "Request employment verification letter",
    "description": "Please issue an employment verification letter for bank submission."
  }'
```

### Get All Requests (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/requests?status=open&per_page=15" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assign Request (HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/requests/{id}/assign" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "assigned_to": 5,
    "status": "in_progress",
    "due_at": "2026-04-20 17:00:00"
  }'
```

### Update Request Status (HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/requests/{id}/status" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "resolved",
    "resolution_note": "Letter has been prepared and sent to employee email."
  }'
```

### Add Request Comment
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/requests/{id}/comments" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "message": "Thank you, I already attached the requested file.",
    "is_internal": false
  }'
```

### Check-in Attendance
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/attendance/check-in" \
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
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/attendance/check-out" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Attendance History
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/history" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Today's Attendance
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/today" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Attendance Intelligence
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/intelligence?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My Overtime Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/overtime?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Detailed People Insights Dashboard (HR/Admin/Manager)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/insights/people/detailed?window_days=30&expiring_days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📋 LEAVE MANAGEMENT

### Get All Leaves
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Leave Request
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/leaves" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/calendar" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Leave Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Leave Request
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Pending Leaves (Manager/HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Leave (Manager/HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Approved"
  }'
```

### Reject Leave (Manager/HR/Admin)
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}/reject" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Employee
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/employees" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Employee
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Start Employee Onboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/onboarding/start" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "probation_end_date": "2026-07-13"
  }'
```

### Complete Employee Onboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/onboarding/complete" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Start Employee Offboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/offboarding/start" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "termination_date": "2026-08-01",
    "termination_reason": "Resignation"
  }'
```

### Complete Employee Offboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/offboarding/complete" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 🎓 TRAINING & COMPETENCY

### Get Training Programs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/training/programs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Training Program
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/training/programs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Leadership Basics",
    "description": "Basic leadership training for supervisors",
    "provider": "Internal HR",
    "mode": "hybrid",
    "start_date": "2026-05-01",
    "end_date": "2026-05-03",
    "budget": 5000000,
    "status": "active"
  }'
```

### Enroll Employees to Training
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/training/programs/{id}/enroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_ids": [1, 2, 3]
  }'
```

### Complete Training Enrollment
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/training/enrollments/{id}/complete" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "score": 92,
    "certificate_path": "/certificates/leadership-basics.pdf",
    "notes": "Completed with excellent result"
  }'
```

### Get My Trainings
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/trainings" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Competencies
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/competencies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Competency
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/competencies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "LEAD-001",
    "name": "Leadership",
    "category": "Management",
    "description": "Ability to lead a team effectively",
    "status": "active"
  }'
```

### Assign Competency to Employees
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/competencies/{id}/assign" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_ids": [1, 2],
    "proficiency_level": 4,
    "assessed_at": "2026-04-13",
    "notes": "Strong leadership capability"
  }'
```

### Get My Competencies
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/competencies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📍 ATTENDANCE MANAGEMENT

### Get All Attendance Records (Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/all" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Attendance Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Delete Attendance Record
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/attendance/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💰 PAYROLL MANAGEMENT (HR/Admin)

### Get All Payroll
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Payroll
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll" \
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
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/generate/monthly" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "period": "2026-04"
  }'
```

### Get Payroll Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Payroll Slip (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/slip" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Export Payroll Slip CSV (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/export" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: text/csv"
```

### Export Payroll Slip PDF (HR/Admin)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf"
```

### Update Payroll
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Payroll
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Mark Payroll as Paid
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/pay" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💳 PAYROLL DETAILS MANAGEMENT (HR/Admin)

### Get Payroll Details
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/{payroll_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Add Payroll Details (Bulk)
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll-details" \
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
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/{id}" \
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
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/bulk-update" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💼 KPI MANAGEMENT (Manager/HR/Admin)

### Get All KPIs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/kpis" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create KPI
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/kpis" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update KPI
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get KPIs by Employee
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/kpis/employee/{employee_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve KPI
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get My KPIs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/kpi" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Submit KPI for Review
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/kpi/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 💰 REIMBURSEMENT MANAGEMENT (Manager/HR/Admin)

### Get All Reimbursements (with filters)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Status
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Category
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements?category=travel" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Filter by Employee
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements?employee_id=1" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Reimbursement (by Manager/HR)
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/reimbursements" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Reimbursement
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approve Reimbursement
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Approved"
  }'
```

### Reject Reimbursement
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/reject" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Missing receipt"
  }'
```

### Mark Reimbursement as Paid
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/mark-paid" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Pending Reimbursements
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Reimbursements by Employee
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/employee/{employee_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Get Reimbursement Statistics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/statistics?employee_id=1" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📍 LOCATION MANAGEMENT (Admin)

### Get All Locations
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/locations" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Location
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/locations" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Update Location
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/locations/{id}" \
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
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 👨‍💼 USER MANAGEMENT (Admin)

### Get All Users
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/users" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assign Roles to User
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/users/{id}/assign-role" \
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
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/roles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assign Permissions to Role
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/roles/{id}/assign-permission" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "permission_ids": [1, 2, 3]
  }'
```

### Get All Permissions
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/permissions" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 🎯 LOGOUT

### Logout
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/logout" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📝 NOTES FOR TESTING

---

## 📊 HR REPORTING & ANALYTICS (HR/Admin)

### Dashboard Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/dashboard-summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/attendance?start_date=2026-04-01&end_date=2026-04-30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leave Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/leave?year=2026" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/payroll?start_date=2026-04-01&end_date=2026-04-30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Competency Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/competency" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employee Lifecycle Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/employee-lifecycle?year=2026" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Asset Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📄 PAYROLL PDF EXPORT

### Export Payroll Slip PDF (ESS - Own)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{payroll_id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf" \
  -o payroll-slip.pdf
```

### Export Payroll Slip PDF (HR/Admin - Any Employee)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{payroll_id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf" \
  -o payroll-slip.pdf
```

---

## 📋 RECOMMENDED USAGE NOTES

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
   - `base_url`: `https://moccasin-crab-693879.hostingersite.com/api/`
   - `token`: (will be filled after login)

3. Update curl commands to use:
   - `{{base_url}}` instead of `https://moccasin-crab-693879.hostingersite.com/api/`
   - `{{token}}` instead of `{TOKEN}`

4. After login, set token in environment variable for reuse
