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

## � ROLE-BASED FEATURE INDEX

Test endpoints sesuai role Anda. Ikuti urutan feature agar testing lebih terstruktur dan tidak loncat-loncat.

### 👑 SUPER_ADMIN Features (use HRIS_ADMIN_TOKEN)
1. **Admin Notifications** - Summary & Broadcast
2. **Email Notifications** - Send & Logs
3. **Email Templates** - Create, List, Update
4. **Biometric Devices** - Register & Sync
5. **Audit Logs** - View logs
6. **Import** - Users & Employees

### 🛡️ ADMIN Features (use HRIS_ADMIN_TOKEN)
1. **User Management** - List, Assign Roles
2. **Role Management** - List, Assign Permissions
3. **Permission Management** - List all
4. **Notifications** - Summary, Broadcast, Admin notifications
5. **Email** - Send, Templates, Logs
6. **Audit Logs** - View logs
7. **Biometric** - Devices, Sync
8. **Import** - Users, Employees, Templates

### 💼 HR Features (use HRIS_HR_TOKEN)
1. **Employees** - Create, List, Update, Onboarding/Offboarding
2. **Leave Policies** - Create, List, Update
3. **Payroll** - Create, Generate, Update, Approve, Mark Paid
4. **Payroll Details** - Add, Update, Delete
5. **Benefits** - List, Assign
6. **Reports** - Dashboard, Attendance, Leave, Payroll, Competency
7. **Compliance** - Overview, Audit, Expiring Documents
8. **Training** - Programs, Enrollments
9. **Competencies** - Create, Assign
10. **Documents** - Review, Expiring
11. **Requests** - Assign, Update Status

### 👔 MANAGER Features (use HRIS_MANAGER_TOKEN)
1. **Organization** - Directory, Summary, Chart, Team Members
2. **Leaves** - Pending, Approve, Reject
3. **KPI** - Create, List, Approve
4. **Reimbursements** - Create, List, Approve, Reject
5. **People Insights** - Detailed Dashboard
6. **Performance** - Summary, Cycles, Reviews
7. **OKR** - Create, List, Update
8. **360 Reviews** - Create, List
9. **Calibration** - Sessions
10. **Career** - IDP, Succession
11. **Engagement** - Surveys
12. **Recruitment** - Job Openings
13. **Workforce Policies** - Shift Swaps, Overtime, Holidays

### 👨‍💼 EMPLOYEE Features (use HRIS_EMPLOYEE_TOKEN)
1. **My Profile** - View, Update
2. **My KPI** - View, Submit
3. **My Reimbursements** - Create, View, Submit
4. **My Payroll** - View, Export
5. **My Leaves** - Create, View, Balance
6. **Attendance** - Check-in, Check-out, History
7. **My Documents** - Upload, View
8. **My Trainings** - View, Competencies
9. **My Assets** - View
10. **Notifications** - View, Mark Read
11. **Requests (Helpdesk)** - Create, View, Comment

---

## �👤 USER PROFILE

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
    "status": "approved"
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
    "manager_id": 1,
    "employee_code": "EMP-0002",
    "position": "Manager",
    "department": "IT",
    "hire_date": "2025-01-15",
    "salary": 50000000,
    "status": "active",
    "location_id": 1,
    "work_schedule_id": 1
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
    "target": 100,
    "period": "2026-Q2"
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

## 🆕 FITUR BARU UNTUK TESTING

### Organization Directory
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/directory" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization Chart
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/chart" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Team Members by Manager
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/team/{managerUserId}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization Master Data
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/master-data" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Compliance Overview
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/overview" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Compliance Audit Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/audit-summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Expiring Documents
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/expiring-documents?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/notifications" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications Unread Count
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/notifications/unread-count" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Mark All Notifications Read
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/notifications/read-all" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Mark Notification Read
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/notifications/{id}/read" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Delete Notification
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/notifications/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Admin Notification Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/notifications/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Broadcast Notification
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/notifications/broadcast" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "System Maintenance",
    "message": "System will be under maintenance tonight at 22:00.",
    "type": "system.maintenance",
    "category": "broadcast",
    "data": {
      "starts_at": "2026-04-15 22:00:00"
    }
  }'
```

### Create Admin Notification For Users
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/notifications" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "user_ids": [1, 2],
    "title": "Profile Update Required",
    "message": "Please update your employee profile.",
    "type": "profile.reminder",
    "category": "reminder",
    "data": {
      "deadline": "2026-04-30"
    }
  }'
```

### Email Notification Logs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/email-notifications/logs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Send Email Notification
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/email-notifications" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "recipient_email": "employee@example.com",
    "user_id": 2,
    "subject": "Welcome to HRIS",
    "body": "Your account has been activated.",
    "type": "notification",
    "reference_type": "employee",
    "reference_id": 2
  }'
```

### Email Templates
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/email-templates" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Email Template
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/email-templates" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "key": "welcome-template",
    "name": "Welcome Template",
    "description": "Welcome email for new employees",
    "subject": "Welcome to the company",
    "html_body": "<p>Hello {{name}}, welcome aboard!</p>",
    "text_body": "Hello {{name}}, welcome aboard!",
    "placeholders": ["name"]
  }'
```

### Update Email Template
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/admin/email-templates/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Welcome Template",
    "subject": "Updated subject",
    "html_body": "<p>Updated template body</p>",
    "text_body": "Updated template body",
    "placeholders": ["name"],
    "is_active": true
  }'
```

### Preview Email Template
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/email-templates/{id}/preview" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "data": {
      "name": "John Doe"
    }
  }'
```

### Biometric Devices
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/biometric/devices" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Register Biometric Device
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/biometric/devices" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Front Door Scanner",
    "device_type": "fingerprint",
    "vendor": "ZKTeco",
    "serial_number": "BIO-001",
    "endpoint_url": "https://device.local/api",
    "api_key": "device-api-key",
    "active": true,
    "location_id": 1
  }'
```

### Sync Biometric Attendance
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/biometric/sync-attendance" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "biometric_device_id": 1,
    "external_reference": "SCAN-20260415-001",
    "user_id": 2,
    "attendance_date": "2026-04-15",
    "check_in": "2026-04-15 08:05:00",
    "check_out": "2026-04-15 17:02:00",
    "latitude": -6.200000,
    "longitude": 106.816666,
    "status": "on_time",
    "payload": {
      "source": "biometric"
    }
  }'
```

### Audit Logs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/audit-logs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Audit Log Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/audit-logs/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approval Flows List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/approval-flows" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Approval Flow
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/approval-flows" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Leave Approval",
    "module": "leave",
    "steps": [
      {
        "step_order": 1,
        "role_id": 2
      },
      {
        "step_order": 2,
        "role_id": 3
      }
    ]
  }'
```

### Benefits List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/benefits" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employee Benefits
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/benefits/employee/{employeeId}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Performance Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Create Review Cycle
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/performance/cycles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Performance Cycle 2026 Q2",
    "period_type": "quarterly",
    "year": 2026,
    "quarter": 2,
    "start_date": "2026-04-01",
    "end_date": "2026-06-30",
    "status": "open",
    "description": "Quarterly review cycle"
  }'
```

### Create Performance Review
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/performance/reviews" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "review_cycle_id": 1,
    "employee_id": 1,
    "reviewer_user_id": 2,
    "kpi_id": 1,
    "score": 88,
    "strengths": "Reliable execution and good collaboration.",
    "improvements": "Increase ownership on complex tasks.",
    "feedback": "Keep the current pace.",
    "reviewer_comment": "Strong performance overall."
  }'
```

### Create OKR
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/performance/okrs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "period_id": 1,
    "objective": "Improve customer response speed",
    "description": "Reduce average response time for support tickets",
    "weight": 80,
    "target_value": 90,
    "unit": "percentage",
    "start_date": "2026-04-01",
    "end_date": "2026-06-30"
  }'
```

### Create 360 Review
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/performance/360-reviews" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cycle_id": 1,
    "employee_id": 1,
    "manager_id": 2,
    "feeders_required": 3,
    "start_date": "2026-04-01",
    "end_date": "2026-05-15"
  }'
```

### Create Calibration Session
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/performance/calibration" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "cycle_id": 1,
    "name": "Calibration Q2 2026",
    "description": "Calibration session for Q2 review results",
    "scheduled_at": "2026-06-15 10:00:00"
  }'
```

### Create IDP
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/career/idps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_id": 1,
    "review_cycle_id": 1,
    "goal_title": "Move into team lead role",
    "goal_description": "Build leadership and planning skills",
    "status": "draft",
    "target_date": "2026-12-31",
    "mentor_user_id": 2
  }'
```

### Create Succession Candidate
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/career/succession" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "position_key": "it_manager",
    "employee_id": 1,
    "readiness": "ready_1_2_years",
    "talent_score": 82,
    "notes": "Needs more budgeting exposure."
  }'
```

### Create Engagement Survey
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/engagement/surveys" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Q2 Engagement Pulse",
    "survey_type": "pulse",
    "start_date": "2026-04-15",
    "end_date": "2026-04-30",
    "anonymous": true,
    "status": "draft",
    "questions": [
      {
        "question_type": "rating",
        "question_text": "How satisfied are you with your work environment?",
        "required": true
      },
      {
        "question_type": "text",
        "question_text": "What could improve your experience?",
        "required": false
      }
    ]
  }'
```

### Create Holiday Calendar
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/workforce/holidays" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Indonesia Holidays 2026",
    "year": 2026,
    "active": true,
    "dates": [
      {
        "holiday_date": "2026-05-01",
        "name": "Labour Day",
        "is_national": true
      }
    ]
  }'
```

### Create Shift Swap Request
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/workforce/shift-swaps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "requester_employee_id": 1,
    "target_employee_id": 2,
    "swap_date": "2026-04-20",
    "reason": "Personal appointment"
  }'
```

### Approve Shift Swap
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/workforce/shift-swaps/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "approved"
  }'
```

### Create Overtime Rule
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/workforce/overtime-rules" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Weekend Overtime",
    "department": "IT",
    "location_id": 1,
    "min_minutes": 60,
    "multiplier": 1.5,
    "requires_approval": true,
    "active": true
  }'
```

### OKR List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/okrs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### 360 Review List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/360-reviews" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Calibration Sessions List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/calibration" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Career IDP List
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/career/idps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Career Succession Matrix
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/career/succession" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Engagement Surveys
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/engagement/surveys" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce Holidays
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/holidays" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce Shift Swaps
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/shift-swaps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce Overtime Rules
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/overtime-rules" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Enterprise Compensation Profile
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/enterprise/compensation/employee/{employeeId}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "tax_number": "NPWP-123456789",
    "tax_status": "TK/0",
    "bpjs_kesehatan_pct": 1,
    "bpjs_ketenagakerjaan_pct": 2,
    "bank_name": "BCA",
    "bank_account_no": "1234567890",
    "bank_account_name": "John Doe"
  }'
```

### Enterprise Notification Template
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/enterprise/notifications/templates" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "hr_reminder",
    "channel": "in_app",
    "title_template": "HR Reminder",
    "body_template": "Please complete your profile.",
    "active": true
  }'
```

### Enterprise Notification Rule
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/enterprise/notifications/rules" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Leave Submitted Rule",
    "event_key": "leave.submitted",
    "conditions": {
      "status": "submitted"
    },
    "channels": ["in_app", "email"],
    "template_id": 1,
    "active": true
  }'
```

### Enterprise Retention Policy
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/enterprise/compliance/retention-policies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "module": "employee_documents",
    "retain_days": 3650,
    "anonymize_after_expiry": false,
    "active": true
  }'
```

### Enterprise Compliance Task
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/enterprise/compliance/tasks" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "title": "Annual Policy Review",
    "module": "compliance",
    "status": "open",
    "due_date": "2026-05-01"
  }'
```

### Enterprise Privacy Request
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/enterprise/compliance/privacy-requests" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "request_type": "export",
    "description": "Request a copy of my personal data."
  }'
```

### Import Users
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/import/users" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "role=employee" \
  -F "file=@C:/path/to/users.csv"
```

### Import Employees
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/import/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "update_existing=true" \
  -F "file=@C:/path/to/employees.csv"
```

### Import Template
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/import/template" \
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
