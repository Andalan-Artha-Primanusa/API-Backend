# POSTMAN CURL COMMANDS - HRIS API Testing (Organized by Role)

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

## 📋 ROLE-BASED FEATURE INDEX

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
9. **Locations** - Create, List, Update
10. **Compliance Tasks** - Create, View

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
12. **Assets** - Create, List, Assign

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

# 🔄 CURL COMMANDS ORGANIZED BY ROLE

---

## 👑 SUPER_ADMIN & 🛡️ ADMIN COMMANDS

Use token: `HRIS_ADMIN_TOKEN`

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

### Locations - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/locations" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Locations - Create
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

### Locations - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Locations - Update
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

### Locations - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/locations/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Admin Notifications - Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/notifications/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Admin Notifications - Broadcast
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

### Admin Notifications - Create For Users
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

### Email Notifications - Logs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/email-notifications/logs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Email Notifications - Send
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

### Email Templates - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/email-templates" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Email Templates - Create
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

### Email Templates - Update
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

### Email Templates - Preview
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

### Biometric Devices - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/biometric/devices" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Biometric Devices - Register
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

### Biometric - Sync Attendance
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

### Audit Logs - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/audit-logs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Audit Logs - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/audit-logs/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Import - Get Template
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/admin/import/template" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Import - Users
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/import/users" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "role=employee" \
  -F "file=@C:/path/to/users.csv"
```

### Import - Employees
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/admin/import/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json" \
  -F "update_existing=true" \
  -F "file=@C:/path/to/employees.csv"
```

---

## 💼 HR COMMANDS

Use token: `HRIS_HR_TOKEN`

### Employees - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/employees" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employees - Create
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

### Employees - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employees - Update
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

### Employees - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employees - Start Onboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/onboarding/start" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "probation_end_date": "2026-07-13"
  }'
```

### Employees - Complete Onboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/onboarding/complete" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Employees - Start Offboarding
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

### Employees - Complete Offboarding
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/employees/{id}/offboarding/complete" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leave Policies - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leave-policies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leave Policies - Create
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

### Leave Policies - Update
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

### Leave Policies - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/leave-policies/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Create
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

### Payroll - Generate Monthly (Bulk)
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/generate/monthly" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "period": "2026-04"
  }'
```

### Payroll - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Get Slip
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/slip" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Export CSV
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/export" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: text/csv"
```

### Payroll - Export PDF
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf"
```

### Payroll - Update
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

### Payroll - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Approve
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll - Mark as Paid
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/payroll/{id}/pay" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll Details - Get
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/{payroll_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Payroll Details - Add (Bulk)
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

### Payroll Details - Update Single
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

### Payroll Details - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/payroll-details/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Benefits - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/benefits" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Benefits - Get by Employee
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/benefits/employee/{employeeId}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Dashboard Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/dashboard-summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Attendance Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/attendance?start_date=2026-04-01&end_date=2026-04-30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Leave Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/leave?year=2026" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Payroll Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/payroll?start_date=2026-04-01&end_date=2026-04-30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Competency Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/competency" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Employee Lifecycle
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/employee-lifecycle?year=2026" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reports - Asset Analytics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reports/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Compliance - Overview
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/overview" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Compliance - Audit Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/audit-summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Compliance - Expiring Documents
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/compliance/expiring-documents?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Training - Get Programs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/training/programs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Training - Create Program
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

### Training - Enroll Employees
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/training/programs/{id}/enroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "employee_ids": [1, 2, 3]
  }'
```

### Training - Complete Enrollment
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

### Competencies - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/competencies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Competencies - Create
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

### Competencies - Assign to Employees
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

### Documents - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/documents?status=pending&per_page=15" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Documents - Review
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

### Documents - Get Expiring
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/documents/expiring?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Requests - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/requests?status=open&per_page=15" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Requests - Assign
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

### Requests - Update Status
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

### Assets - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Assets - Create
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

### Assets - Assign to Employee
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

### Assets - Return
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

### Approval Flows - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/approval-flows" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Approval Flows - Create
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

---

## 👔 MANAGER COMMANDS

Use token: `HRIS_MANAGER_TOKEN`

### Organization - Directory
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/directory" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization - Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization - Chart
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/chart" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization - Team Members
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/team/{managerUserId}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Organization - Master Data
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/organization/master-data" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leaves - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leaves - Create
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

### Leaves - Get Pending (Manager View)
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Leaves - Approve
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "approved"
  }'
```

### Leaves - Reject
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}/reject" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Cannot approve at this time"
  }'
```

### KPI - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/kpis" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### KPI - Create
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

### KPI - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### KPI - Update
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

### KPI - Approve
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/kpis/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Create
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

### Reimbursements - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Update
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

### Reimbursements - Approve
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/approve" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Approved"
  }'
```

### Reimbursements - Reject
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/reject" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "note": "Missing receipt"
  }'
```

### Reimbursements - Mark Paid
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/{id}/mark-paid" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Filter by Status
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Get Pending
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/pending" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Get by Employee
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/employee/{employee_id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Reimbursements - Get Statistics
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/reimbursements/statistics?employee_id=1" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### People Insights - Detailed Dashboard
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/insights/people/detailed?window_days=30&expiring_days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Performance - Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/summary" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Performance - Create Review Cycle
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

### Performance - Create Review
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

### OKR - Create
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

### OKR - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/okrs" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### 360 Review - Create
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

### 360 Review - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/360-reviews" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Calibration - Create Session
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

### Calibration - Get All Sessions
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/performance/calibration" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Career - Create IDP
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

### Career - Get IDPs
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/career/idps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Career - Create Succession Candidate
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

### Career - Get Succession Matrix
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/career/succession" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Engagement - Create Survey
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

### Engagement - Get Surveys
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/engagement/surveys" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Recruitment - Get Job Openings
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/recruitment/openings" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce - Get Holidays
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/holidays" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce - Create Holiday Calendar
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

### Workforce - Get Shift Swaps
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/shift-swaps" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce - Create Shift Swap Request
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

### Workforce - Approve Shift Swap
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/workforce/shift-swaps/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "status": "approved"
  }'
```

### Workforce - Get Overtime Rules
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/workforce/overtime-rules" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Workforce - Create Overtime Rule
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

---

## 👨‍💼 EMPLOYEE COMMANDS

Use token: `HRIS_EMPLOYEE_TOKEN`

### User Profiles - Get My Profile
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/profiles" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### User Profiles - Update My Profile
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

### My KPI - Get
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/kpi" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My KPI - Submit
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/kpi/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Reimbursements - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Reimbursements - Filter by Status
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements?status=draft" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Reimbursements - Create
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

### My Reimbursements - Submit
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/my/reimbursements/{id}/submit" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Payroll - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Payroll - Get Slip
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/slip" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Payroll - Export CSV
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/export" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: text/csv"
```

### My Payroll - Export PDF
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/payroll/{id}/export-pdf" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/pdf"
```

### My Leaves - Get All My Leaves
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/my" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Leaves - Get Balance
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/balance" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Leaves - Create Request
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

### My Leaves - Get Calendar
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/calendar" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Leaves - Get Detail
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Leaves - Update Request
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

### My Leaves - Delete Request
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/leaves/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance - Check-in
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

### Attendance - Check-out
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/attendance/check-out" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance - Get History
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/history" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance - Get Today's
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/today" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance - Get Intelligence
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/intelligence?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Attendance - Get Overtime Summary
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/attendance/overtime?days=30" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Documents - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/documents" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Documents - Upload
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

### My Trainings - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/trainings" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Competencies - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/competencies" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Assets - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/assets" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/notifications" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications - Get Unread Count
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/notifications/unread-count" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications - Read All
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/notifications/read-all" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications - Mark Single as Read
```bash
curl -X PUT "https://moccasin-crab-693879.hostingersite.com/api/notifications/{id}/read" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### Notifications - Delete
```bash
curl -X DELETE "https://moccasin-crab-693879.hostingersite.com/api/notifications/{id}" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Requests - Get All
```bash
curl -X GET "https://moccasin-crab-693879.hostingersite.com/api/my/requests" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

### My Requests - Create
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

### My Requests - Add Comment
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

---

## 🎯 LOGOUT (All Roles)

### Logout
```bash
curl -X POST "https://moccasin-crab-693879.hostingersite.com/api/logout" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "Accept: application/json"
```

---

## 📝 TESTING NOTES

1. **Get Token First**: Login first to get your token using the Login endpoint
2. **Replace Placeholders**: 
   - `{TOKEN}` → Use token from login response
   - `{id}` → Use actual ID from database
   - `{employee_id}` → Use actual employee ID
   - `{payroll_id}` → Use actual payroll ID

3. **Content-Type**: Always include `Content-Type: application/json` for POST/PUT requests

4. **Test Order** (Recommended per role):
   - Register / Login (get token)
   - Follow the feature order in the ROLE-BASED FEATURE INDEX section
   - Test one role completely before switching to another

5. **Common Response Format**:
   ```json
   {
     "success": true/false,
     "message": "Action message",
     "data": {}
   }
   ```

6. **Using PowerShell Scripts**:
   - Use the role-based curl scripts in `curl-tests/` directory
   - Each script contains all curl commands for that role
   - Run one script at a time for focused testing
   ```powershell
   powershell -ExecutionPolicy Bypass -File .\curl-tests\admin-curl.ps1
   powershell -ExecutionPolicy Bypass -File .\curl-tests\hr-curl.ps1
   powershell -ExecutionPolicy Bypass -File .\curl-tests\manager-curl.ps1
   powershell -ExecutionPolicy Bypass -File .\curl-tests\employee-curl.ps1
   ```

---

## 🚀 ENVIRONMENT SETUP

Set these environment variables before running curl commands:

```
HRIS_BASE_URL=http://127.0.0.1:8000/api
HRIS_ADMIN_TOKEN=<admin_token_here>
HRIS_HR_TOKEN=<hr_token_here>
HRIS_MANAGER_TOKEN=<manager_token_here>
HRIS_EMPLOYEE_TOKEN=<employee_token_here>
```

Then use in curl commands:
```bash
curl -X GET "$HRIS_BASE_URL/api/employees" \
  -H "Authorization: Bearer $HRIS_HR_TOKEN" \
  -H "Accept: application/json"
```
