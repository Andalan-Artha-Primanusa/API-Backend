# HRIS Role-Based Workflows & Documentation

**System:** HR Information System (HRIS) - Laravel 12  
**Date:** April 2026  
**Status:** Production Ready

---

## 📋 Table of Contents

1. [System Architecture Overview](#system-architecture-overview)
2. [Role Hierarchy & Permissions](#role-hierarchy--permissions)
3. [Employee Workflow](#employee-workflow-ess---employee-self-service)
4. [Manager Workflow](#manager-workflow)
5. [HR Officer Workflow](#hr-officer-workflow)
6. [Admin Workflow](#admin-workflow)
7. [Super Admin Workflow](#super-admin-workflow)
8. [Authentication Flow](#authentication-flow)
9. [Approval Workflows](#approval-workflows)
10. [Module Integration Matrix](#module-integration-matrix)

---

## System Architecture Overview

### Core Components
```
┌─────────────────────────────────────────────────────────────┐
│                      API Gateway (Laravel)                  │
├─────────────────────────────────────────────────────────────┤
│  Authentication  →  Role-Based Middleware  →  Controllers   │
├─────────────────────────────────────────────────────────────┤
│  Services Layer (Business Logic)                             │
├─────────────────────────────────────────────────────────────┤
│  Models & Database                                           │
└─────────────────────────────────────────────────────────────┘
```

### Key Features by Layer
- **Auth**: Sanctum token-based + Google SSO
- **Authorization**: Role & Permission-based middleware
- **Audit**: Automatic trail logging on all protected routes
- **Notifications**: Real-time event-based notifications

---

## Role Hierarchy & Permissions

### Role Structure (4-Tier System)

```
┌─────────────────────────────────────────────────────────┐
│                    SUPER_ADMIN                          │
│  (System Owner - Full Access - All Features)            │
└────────────────┬────────────────────────────────────────┘
                 │
       ┌─────────┴──────────┬──────────────┐
       │                    │              │
┌──────▼──────┐    ┌────────▼──────┐  ┌───▼──────────┐
│    ADMIN     │    │      HR       │  │   MANAGER    │
│ (System Ops) │    │ (Operational) │  │ (Team Lead)  │
└──────┬──────┘    └────────┬──────┘  └───┬──────────┘
       │                    │              │
       └────────────┬───────┴──────────────┘
                    │
            ┌───────▼────────┐
            │    EMPLOYEE    │
            │  (End User)    │
            └────────────────┘
```

### Permission Matrix

| Module | Employee | Manager | HR | Admin | Super Admin |
|--------|----------|---------|----|----|---|
| **Own Profile** | ✅ RO | ✅ RO | ✅ RW | ✅ RW | ✅ RW |
| **Own Attendance** | ✅ C/R | ✅ C/R | ✅ RW | ✅ RW | ✅ RW |
| **Team Attendance** | ❌ | ✅ R | ✅ RW | ✅ RW | ✅ RW |
| **Own Leave** | ✅ CRU | ✅ CRU | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Team Leave Approval** | ❌ | ✅ RW | ✅ RW | ✅ RW | ✅ RW |
| **Own KPI** | ✅ R/Submit | ✅ RW | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Own Payroll** | ✅ R/Export | ❌ | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Training/Competency** | ✅ R | ✅ RW | ✅ CRUD | ✅ CRUD | ✅ CRUD |
| **Asset Management** | ✅ View Own | ✅ RW | ✅ RW | ✅ CRUD | ✅ CRUD |
| **HR Reports** | ❌ | ❌ | ✅ R | ✅ R | ✅ R |
| **Audit Logs** | ❌ | ❌ | ❌ | ✅ R | ✅ R |
| **Role/Permission** | ❌ | ❌ | ❌ | ✅ RW | ✅ RW |
| **System Settings** | ❌ | ❌ | ❌ | ✅ CRUD | ✅ CRUD |

**Legend:** RO = Read-Only | R = Read | C = Create | U = Update | D = Delete | RW = Read-Write | CRUD = Full Control

---

## EMPLOYEE WORKFLOW (ESS - Employee Self-Service)

### Role Definition
- **Access Level:** Self-service only
- **Scope:** Own data only
- **Middleware:** `auth:sanctum, audit.trail`

### Use Cases & Actions

#### 1️⃣ **Authentication & Profile**
```
LOGIN → 
├─ Email/Password OR Google SSO
├─ Receive Bearer Token (24h expiry)
└─ Access Profile (RO) - View own user & profile data

PROFILE MANAGEMENT →
├─ View Own Profile (/profiles/{id})
└─ Cannot modify own profile (HR updates)
```

#### 2️⃣ **Attendance Management**
```
DAILY CHECK-IN/OUT →
├─ Check-In (POST /attendance/check-in)
│  ├─ Record time, location (optional)
│  ├─ System checks schedule
│  └─ Auto-status: On-time/Late/Absent
├─ Check-Out (POST /attendance/check-out)
│  ├─ Record end time
│  └─ Calculate work hours
└─ Status Updates (REAL-TIME via Notification)

ATTENDANCE RECORDS →
├─ View History (/attendance/history)
├─ View Today's Status (/attendance/today)
├─ View Attendance Intelligence (/attendance/intelligence)
│  ├─ Overtime calculation
│  ├─ Leave vs Attendance match
│  └─ Performance metrics
└─ View Overtime (/attendance/overtime)
```

#### 3️⃣ **Leave Management**
```
REQUEST LEAVE →
├─ POST /leaves with:
│  ├─ leave_type (Sick, Annual, Unpaid, etc)
│  ├─ start_date, end_date
│  ├─ reason
│  └─ duration (auto-calculated)
├─ Status: PENDING
└─ Notification → Manager + HR

TRACK LEAVE →
├─ View My Leaves (/leaves/my)
├─ Check Balance (/leaves/balance)
│  ├─ Available vs Used
│  ├─ By leave type
│  └─ Renewal date
├─ Cancel Leave (if pending)
└─ Notification on Approval/Rejection

LEAVE CALENDAR →
├─ View company holidays
├─ View team member leaves (read-only)
└─ Plan leave around blackout dates
```

#### 4️⃣ **Payroll Self-Service**
```
VIEW PAYROLL →
├─ GET /my/payroll (monthly list)
├─ GET /my/payroll/{id}/slip (detailed)
│  ├─ Basic Salary, Allowances, Bonuses
│  ├─ Deductions, Taxes
│  └─ Net Pay + Payment Method
└─ Status: Draft → Approved → Paid

EXPORT PAYROLL →
├─ CSV Export (/my/payroll/{id}/export)
├─ PDF Export (/my/payroll/{id}/export-pdf)
└─ Available after approval status
```

#### 5️⃣ **KPI & Performance**
```
SUBMIT KPI →
├─ GET /my/kpi (view assigned KPIs)
├─ POST /my/kpi/{id}/submit
│  ├─ Add achievement notes
│  ├─ Attach evidence/documents
│  └─ Submit to Manager for review
└─ Status: DRAFT → SUBMITTED → REVIEWED

TRACK KPI →
├─ View assigned KPIs
├─ View achievement progress
├─ View manager feedback
└─ Re-submit if rejected
```

#### 6️⃣ **Reimbursement Claims**
```
CREATE CLAIM →
├─ POST /my/reimbursements with:
│  ├─ category (office, travel, meals, etc)
│  ├─ amount, date
│  ├─ description
│  └─ receipt/evidence file
├─ Status: DRAFT → SUBMITTED
└─ Notification → Manager/HR

TRACK CLAIM →
├─ GET /my/reimbursements
├─ View status & approval history
└─ Cancel if not yet approved
```

#### 7️⃣ **Training & Competencies**
```
VIEW TRAINING →
├─ GET /my/trainings (enrolled programs)
├─ View training progress
├─ View certificates/completion
└─ Request new training (form submission)

VIEW COMPETENCIES →
├─ GET /my/competencies
├─ View assigned competencies
├─ View proficiency levels
├─ Request skill assessments
└─ View development gaps
```

#### 8️⃣ **Asset Management**
```
VIEW ASSETS →
├─ GET /my/assets (assigned items)
├─ Serial numbers & condition
├─ Maintenance status
└─ Return request (if damaged/lost)

REPORT ASSET ISSUE →
├─ Create service request
├─ Attach photos/documentation
└─ Get ticket number for tracking
```

#### 9️⃣ **Documents**
```
MANAGE DOCUMENTS →
├─ POST /my/documents (upload)
│  ├─ ID Cards, Certificates, Licenses
│  ├─ Resume, Education, Insurance
│  └─ Expiry date tracking
├─ GET /my/documents (view own)
├─ PUT /my/documents/{id} (update)
└─ Expiry reminders

DOCUMENT WORKFLOW →
├─ Upload required documents
├─ HR reviews & approves
├─ Status: SUBMITTED → REVIEWED → APPROVED/REJECTED
└─ Resubmit if needed
```

#### 🔟 **HR Service Requests**
```
CREATE REQUEST →
├─ POST /my/requests (general HR support)
│  ├─ Type: Letter, Certificate, Update, etc
│  ├─ Description
│  └─ Attachments
├─ Status: OPEN → ASSIGNED → IN PROGRESS → CLOSED
└─ Notification on status change

TRACK REQUEST →
├─ GET /my/requests
├─ View assigned HR officer
├─ Comment on request (Q&A)
├─ Receive notification on completion
└─ Rate service quality
```

#### 1️⃣1️⃣ **Notifications**
```
NOTIFICATION DASHBOARD →
├─ GET /notifications (all)
├─ GET /notifications/unread-count
├─ PUT /notifications/{id}/read (mark read)
└─ PUT /notifications/read-all

NOTIFICATION TYPES →
├─ Leave Approved/Rejected
├─ Payroll Posted
├─ KPI Due Date Reminder
├─ Training Enrollment
├─ Document Expiry Alert
├─ Reimbursement Status
└─ System Announcements
```

### Employee API Endpoints Summary
```
GET    /api/profiles/{id}                       (Own profile)
GET    /api/my/payroll                          (Payroll list)
GET    /api/my/payroll/{id}/slip                (Payroll detail)
GET    /api/my/payroll/{id}/export              (CSV export)
GET    /api/my/payroll/{id}/export-pdf          (PDF export)
GET    /api/my/kpi                              (My KPIs)
POST   /api/my/kpi/{id}/submit                  (Submit KPI)
GET    /api/my/reimbursements                   (My claims)
POST   /api/my/reimbursements                   (Create claim)
POST   /api/my/reimbursements/{id}/submit       (Submit claim)
POST   /api/attendance/check-in                 (Check-in)
POST   /api/attendance/check-out                (Check-out)
GET    /api/attendance/history                  (History)
GET    /api/attendance/today                    (Today status)
GET    /api/attendance/intelligence             (Analytics)
GET    /api/attendance/overtime                 (Overtime data)
GET    /api/leaves/my                           (My leaves)
GET    /api/leaves/balance                      (Leave balance)
POST   /api/leaves                              (Request leave)
GET    /api/leaves/{id}                         (Leave detail)
PUT    /api/leaves/{id}                         (Update leave)
DELETE /api/leaves/{id}                         (Cancel leave)
GET    /api/my/trainings                        (My trainings)
GET    /api/my/competencies                     (My competencies)
GET    /api/my/assets                           (My assets)
GET    /api/my/documents                        (My documents)
POST   /api/my/documents                        (Upload document)
GET    /api/my/requests                         (Service requests)
POST   /api/my/requests                         (Create request)
GET    /api/my/requests/{id}                    (Request detail)
POST   /api/my/requests/{id}/comments           (Add comment)
GET    /api/notifications                       (All notifications)
GET    /api/notifications/unread-count          (Unread count)
PUT    /api/notifications/{id}/read             (Mark as read)
PUT    /api/notifications/read-all              (Mark all as read)
```

---

## MANAGER WORKFLOW

### Role Definition
- **Access Level:** Own team + Management functions
- **Scope:** Self + Direct Reports + Approval Authority
- **Middleware:** `auth:sanctum, audit.trail, role:admin,manager,hr,super_admin`

### Scope Limitations
```
visibility:
├─ Own Data: Full (like Employee)
├─ Team Members: Only direct reports
├─ Other Teams: None (read-only leave calendar)
└─ Company Data: Summary only
```

### Use Cases & Actions

#### 1️⃣ **Team Attendance Management**
```
VIEW TEAM ATTENDANCE →
├─ GET /attendance/employee/{userId}/intelligence
├─ View all team members:
│  ├─ Attendance rate (present, absent, late, permission)
│  ├─ Overtime hours
│  ├─ Leave vs actual attendance
│  └─ Performance trends
└─ Filter by date range

MONITOR TEAM HEALTH →
├─ Mark suspicious patterns (e.g., frequent lates)
├─ Identify at-risk employees
├─ Generate attendance reports
└─ Prepare coaching notes
```

#### 2️⃣ **Leave Approval Workflow**
```
PENDING LEAVE REQUESTS →
├─ GET /leaves/pending (team's pending)
├─ View details:
│  ├─ Employee name, dates, type, reason
│  ├─ Leave balance status
│  ├─ Impact on team schedule
│  └─ Attachments/evidence
└─ Filter by employee, date, type

APPROVE/REJECT →
├─ PUT /leaves/{id}/approve
│  ├─ Add approval note (optional)
│  ├─ Team schedule check
│  └─ Status: PENDING → APPROVED
├─ PUT /leaves/{id}/reject
│  ├─ Add rejection reason
│  └─ Status: PENDING → REJECTED
└─ Notification sent to Employee + HR

LEAVE IMPACT ANALYSIS →
├─ Check team coverage
├─ Identify conflicts
├─ Suggest alternative dates
└─ Document approval reason
```

#### 3️⃣ **KPI Management**
```
CREATE KPI FOR TEAM →
├─ POST /kpis with:
│  ├─ Target employee(s)
│  ├─ KPI title & description
│  ├─ Target metrics & deadline
│  ├─ Weightage & difficulty
│  └─ Review frequency
├─ Status: DRAFT → PUBLISHED
└─ Notification to Employee

MONITOR KPI PROGRESS →
├─ GET /kpis/employee/{employee_id}
├─ View submission status
├─ Provide feedback on evidence
├─ Update progress notes
└─ Schedule review meetings

KPI APPROVAL FLOW →
├─ Review employee submission
├─ PUT /kpis/{id}/approve
│  ├─ Rate achievement level
│  ├─ Add comments
│  └─ Calculate score (weighted)
├─ Forward to HR if needed
└─ Notification to employee
```

#### 4️⃣ **Reimbursement Review**
```
REVIEW TEAM CLAIMS →
├─ GET /reimbursements (team's)
├─ Filter:
│  ├─ Status (pending, approved, rejected, paid)
│  ├─ Employee, date range
│  ├─ Category (travel, meals, supplies, etc)
│  └─ Amount range

PROCESS CLAIMS →
├─ Verify:
│  ├─ Policy compliance
│  ├─ Receipt authenticity
│  ├─ Category correctness
│  └─ Amount reasonableness
├─ PUT /reimbursements/{id}/approve (if allowed)
│  └─ Add approval note
├─ PUT /reimbursements/{id}/reject
│  └─ Provide rejection reason
└─ Forward to HR/Finance
```

#### 5️⃣ **Training & Development**
```
MANAGE TEAM TRAINING →
├─ View enrolled training programs
├─ POST /training/programs/{id}/enroll (team members)
├─ Track completion:
│  ├─ Start date, progress
│  ├─ Completion deadline
│  ├─ Assessment scores
│  └─ Certificate status

COMPETENCY DEVELOPMENT →
├─ POST /competencies/{id}/assign (to team)
├─ View competency gaps
├─ Recommend training
├─ Track proficiency levels
└─ Plan succession pipeline
```

#### 6️⃣ **Team Insights Dashboard**
```
MANAGER INSIGHTS (Limited) →
├─ GET /insights/people (overview)
├─ View:
│  ├─ Team headcount
│  ├─ Turnover risk (from employee documents)
│  ├─ Training completion rate
│  ├─ Average attendance rate
│  └─ Pending approvals (leaves, KPIs, claims)

TEAM PERFORMANCE →
├─ Attendance summary
├─ Leave utilization
├─ KPI achievement trends
├─ Training participation
└─ Performance patterns
```

#### 7️⃣ **Employee Lifecycle Events**
```
PARTICIPATE IN EVENTS →
├─ Promotion: Recommend & approve within team
├─ Transfer: Facilitate handover
├─ Leave of Absence: Manage team impact
├─ Termination: Document, handover, feedback
└─ Track timeline + attachments
```

### Manager API Endpoints Summary
```
GET    /api/attendance/employee/{userId}/intelligence
GET    /api/leaves/pending                      (Team's pending)
PUT    /api/leaves/{id}/approve                 (Approve leave)
PUT    /api/leaves/{id}/reject                  (Reject leave)
GET    /api/kpis                                (Team's KPIs)
POST   /api/kpis                                (Create KPI)
GET    /api/kpis/employee/{employee_id}         (Employee's KPIs)
GET    /api/kpis/{id}                           (KPI detail)
PUT    /api/kpis/{id}                           (Update KPI)
PUT    /api/kpis/{id}/approve                   (Approve KPI)
DELETE /api/kpis/{id}                           (Delete KPI)
GET    /api/reimbursements                      (Team's claims)
GET    /api/reimbursements/pending              (Pending claims)
GET    /api/reimbursements/employee/{emp_id}   (Employee's claims)
GET    /api/reimbursements/{id}                 (Claim detail)
GET    /api/reimbursements/statistics           (Statistics)
GET    /api/training/programs                   (Training programs)
POST   /api/training/programs/{id}/enroll       (Enroll team)
GET    /api/competencies                        (All competencies)
GET    /api/competencies/{id}                   (Competency detail)
POST   /api/competencies/{id}/assign            (Assign to employee)
GET    /api/competencies/employee/{empId}      (Employee's competencies)
GET    /api/insights/people                     (Team dashboard)
GET    /api/insights/people/detailed            (Detailed dashboard)
GET    /api/insights/people/team-health         (Team health)
+ All Employee endpoints (own data)
```

---

## HR OFFICER WORKFLOW

### Role Definition
- **Access Level:** Organization-wide Operational
- **Scope:** All employees + All HR operations
- **Middleware:** `auth:sanctum, audit.trail, role:admin,hr,super_admin`

### HR Core Responsibilities
```
1. Employee Lifecycle Management
2. Leave & Attendance Administration
3. Training & Development Coordination
4. Payroll Processing & Verification
5. Policy Enforcement
6. Reports & Analytics
7. Grievance & Support
```

### Use Cases & Actions

#### 1️⃣ **Employee Lifecycle Management**
```
ONBOARDING →
├─ Create Employee Record (via EmployeeController or Admin)
├─ PUT /employees/{id}/onboarding/start
│  ├─ Send welcome package
│  ├─ Assign mentor/buddy
│  ├─ Schedule orientation
│  ├─ Create checklist
│  └─ Set completion date
├─ Track onboarding checklist:
│  ├─ Documents received
│  ├─ Training completed
│  ├─ Systems access granted
│  ├─ Bank details updated
│  └─ Medical checkup done
└─ PUT /employees/{id}/onboarding/complete
   └─ Status: ACTIVE

EMPLOYEE UPDATES →
├─ GET /employees (all)
├─ GET /employees/{id} (detail)
├─ PUT /employees/{id} (update)
│  ├─ Contact info, emergency contact
│  ├─ Department, position, manager
│  ├─ Salary grade, cost center
│  └─ Work schedule
└─ Track change history (audit trail)

PROMOTION/TRANSFER →
├─ View eligible employees (based on KPI, training)
├─ POST or PUT employee record with new details
├─ Process:
│  ├─ Approval by current manager
│  ├─ Approval by new manager
│  ├─ HR confirmation
│  └─ Salary adjustment processing
└─ Notification chain

OFFBOARDING →
├─ PUT /employees/{id}/offboarding/start
│  ├─ Collect separation documents
│  ├─ Settle final dues
│  ├─ Retrieve company items/assets
│  ├─ Clear access from systems
│  └─ Conduct exit interview
├─ Track checklist:
│  ├─ Settlement calculation
│  ├─ Notice period compliance
│  ├─ Knowledge transfer
│  ├─ Gratuity processing
│  └─ Final compensation
└─ PUT /employees/{id}/offboarding/complete
   └─ Status: INACTIVE
```

#### 2️⃣ **Leave Administration**
```
LEAVE POLICY SETUP →
├─ POST /leave-policies
│  ├─ Policy name (Annual, Sick, Unpaid, etc)
│  ├─ Entitlement (days per year/month)
│  ├─ Accrual method (monthly, yearly, immediate)
│  ├─ Carryover rules (max days, expiry)
│  ├─ Blackout dates
│  ├─ Approval chain
│  └─ Min/Max duration rules
├─ PUT /leave-policies/{id} (update)
└─ DELETE /leave-policies/{id} (deactivate)

LEAVE REQUEST PROCESSING →
├─ GET /leaves (all requests)
├─ Filter:
│  ├─ Status: PENDING, APPROVED, REJECTED
│  ├─ Employee, date range
│  ├─ Leave type, department
│  └─ Manager approval status
├─ Final approval by HR:
│  ├─ Verify against policy
│  ├─ Check balance availability
│  ├─ Review manager approval
│  └─ PUT /leaves/{id}/approve (final confirmation)
├─ Balance updates (automatically)
└─ Notification to employees + payroll

LEAVE BALANCE MANAGEMENT →
├─ View employee balances:
│  ├─ GET /leaves/balance (for own)
│  ├─ Manual query for others
├─ Handle special cases:
│  ├─ Balance adjustments (manual grant/deduction)
│  ├─ Carry-over processing (monthly/yearly)
│  ├─ Expiry enforcement
│  ├─ Reinstatement requests
│  └─ Leave encashment on separation
```

#### 3️⃣ **Attendance Administration**
```
ATTENDANCE MONITORING →
├─ GET /attendance/all (organization-wide)
├─ View by:
│  ├─ Employee, department, date range
│  ├─ Status (present, absent, late, permission)
│  ├─ Total hours vs scheduled
│  └─ Overtime tracking
├─ Generate reports:
│  ├─ Attendance summary
│  ├─ Absenteeism trends
│  ├─ Punctuality analysis
│  └─ Overtime distribution

ATTENDANCE CORRECTIONS →
├─ DELETE /attendance/{id} (remove incorrect entry)
├─ Manual entry for:
│  ├─ Absent employees (off-site work)
│  ├─ Late arrivals (traffic exceptions)
│  ├─ Missing check-outs
│  └─ System errors
└─ Approval chain + audit trail

EXCEPTION HANDLING →
├─ Export & send to team leads
├─ Get manager confirmation
├─ Apply policy (warning, deduction, etc)
└─ Document reason
```

#### 4️⃣ **KPI & Performance Management**
```
KPI TEMPLATE CREATION →
├─ Define organizational KPIs
├─ Set evaluation criteria
├─ Create review calendar
└─ Assign review authorities

KPI REVIEW CYCLE →
├─ Q1-Q4 Planning
├─ Mid-year review
├─ Year-end evaluation
├─ Compilation: /api/kpis (full access)
├─ Review manager submissions
├─ Consolidate at HR level
└─ Generate performance reports

KPI ANALYTICS →
├─ GET /reports/competency (aggregated)
├─ Views:
│  ├─ Top performers
│  ├─ At-risk performers
│  ├─ Training needs analysis
│  ├─ Promotion readiness
│  └─ Succession planning insights
```

#### 5️⃣ **Training & Development**
```
TRAINING PROGRAM MANAGEMENT →
├─ POST /training/programs
│  ├─ Program name, type, duration
│  ├─ Trainer details, cost
│  ├─ Schedule, location (physical/online)
│  ├─ Capacity, prerequisites
│  ├─ Learning objectives
│  └─ Certification requirements
├─ PUT /training/programs/{id} (update)
└─ DELETE /training/programs/{id} (archive)

ENROLLMENT MANAGEMENT →
├─ View all enrollments
├─ POST /training/programs/{id}/enroll (manage enrollments)
├─ Track:
│  ├─ Enrollment status
│  ├─ Attendance records
│  ├─ Assignment progress
│  ├─ Assessment scores
│  └─ Certificate issuance
└─ PUT /training/enrollments/{id}/complete (finalize)

COMPETENCY FRAMEWORK →
├─ POST /competencies
│  ├─ Competency name, category, level
│  ├─ Proficiency scale (1-5)
│  ├─ Assessment method
│  └─ Linked to roles
├─ PUT /competencies/{id} (update)
├─ DELETE /competencies/{id} (deactivate)
├─ POST /competencies/{id}/assign (to employees)
└─ Analyze competency gaps

TRAINING ANALYTICS →
├─ GET /reports/competency
├─ Views:
│  ├─ Training completion rate
│  ├─ Skill distribution in workforce
│  ├─ Training ROI indicators
│  ├─ Compliance training status
│  └─ Development plan fulfillment
```

#### 6️⃣ **Payroll Processing**
```
PAYROLL SETUP →
├─ Define salary structure
├─ Set allowances & deductions
├─ Configure tax calculations
├─ Define payment methods (bank, cash)
└─ Set payroll cycle (monthly, bi-weekly)

MONTHLY PAYROLL PROCESSING →
├─ POST /payroll/generate/monthly (bulk generation)
│  ├─ System auto-fetches:
│  │  ├─ Attendance data (working days)
│  │  ├─ Leave records (deductions)
│  │  ├─ KPI bonuses
│  │  ├─ Reimbursements
│  │  ├─ Commission/incentives
│  │  └─ Previous month carryover
│  ├─ Calculations:
│  │  ├─ Basic Salary
│  │  ├─ Allowances (HRA, DA, etc)
│  │  ├─ Deductions (PF, Insurance)
│  │  ├─ Taxes (Income Tax)
│  │  ├─ Bonuses & Incentives
│  │  └─ Net Pay
│  └─ Status: DRAFT (for review)

PAYROLL REVIEW & APPROVAL →
├─ GET /payroll (all draft)
├─ Review slip details:
│  ├─ GET /payroll/{id}/slip (complete breakdown)
│  ├─ Verify calculations
│  ├─ Check against approval limits
│  └─ Exception handling
├─ POST /payroll/{id}/approve (final approval)
│  └─ Status: DRAFT → APPROVED
├─ Notification to Accounting/Finance
└─ Lock for changes

PAYROLL EXPORT →
├─ GET /payroll/{id}/export (CSV)
│  ├─ For bank transfer processing
│  ├─ For accounting import
│  └─ For record keeping
├─ GET /payroll/{id}/export-pdf (PDF)
│  └─ For employee distribution
├─ Batch salary file generation
│  ├─ NEFT/RTGS format
│  ├─ Bank-specific format
│  └─ Signed payroll register

PAYROLL PAYMENT STATUS →
├─ POST /payroll/{id}/pay (mark as paid)
│  ├─ Record payment date
│  ├─ Reference/transaction ID
│  └─ Status: APPROVED → PAID
├─ Track payment status
└─ Reconciliation with bank statements
```

#### 7️⃣ **Reimbursement Processing**
```
REIMBURSEMENT POLICY →
├─ Define expense categories
├─ Set approval limits by category
├─ Define supporting document requirements
├─ Set payment terms (advance, reimbursement)
└─ Update policy annually

REIMBURSEMENT WORKFLOW →
├─ GET /reimbursements (all)
├─ Review submissions:
│  ├─ Verify documents (receipt, invoice)
│  ├─ Check policy compliance
│  ├─ Verify amount/category match
│  └─ Check account balance/project code
├─ PUT /reimbursements/{id}/approve (HR approval)
│  ├─ Approval note optional
│  └─ Forward to Finance
├─ PUT /reimbursements/{id}/reject (if needed)
│  ├─ Provide reason
│  └─ Return to employee
├─ GET /reimbursements/statistics (reporting)
└─ PUT /reimbursements/{id}/mark-paid (Finance)

EXPENSE REPORTS →
├─ By employee, department, category
├─ Trends & patterns
├─ Budget vs actuals
└─ Policy violation alerts
```

#### 8️⃣ **Asset Management**
```
ASSET INVENTORY →
├─ POST /assets (new asset)
│  ├─ Asset name, type, category
│  ├─ Serial number, cost, purchase date
│  ├─ Warranty expiry, insurance details
│  └─ Location, custody agent
├─ GET /assets (all inventory)
└─ PUT /assets/{id} (update)

ASSET ASSIGNMENT →
├─ POST /assets/{id}/assign
│  ├─ To employee with date
│  ├─ Condition notes
│  └─ Acknowledgment requirement
├─ Track:
│  ├─ Current owner/location
│  ├─ Assignment history
│  ├─ Maintenance records
│  └─ Return status

ASSET LIFECYCLE →
├─ In-use: Monitor condition
├─ Maintenance: Schedule & track
├─ Return: PUT /assignments/{assignmentId}/return
│  ├─ Condition assessment
│  ├─ Damage charges (if any)
│  └─ Inventory update
├─ Repair/Replacement
├─ Depreciation tracking
└─ Disposal

ASSET REPORTS →
├─ Inventory by category
├─ Asset allocation by department
├─ Unassigned assets
├─ Maintenance schedule
└─ Asset aging & replacement needs
```

#### 9️⃣ **Documents & Compliance**
```
DOCUMENT MANAGEMENT →
├─ GET /documents (all)
├─ Review submitted docs:
│  ├─ ID Cards, Certificates, Licenses
│  ├─ Educational qualifications
│  ├─ Professional certifications
│  ├─ Insurance & medical documents
│  └─ Tax compliance (PAN, Aadhaar references)
├─ PUT /documents/{id}/review
│  ├─ Approval status
│  ├─ Expiry tracking
│  ├─ Compliance flag
│  └─ Action items (if needed)

EXPIRY ALERTS →
├─ GET /documents/expiring (upcoming expiries)
├─ Auto-notifications to employees
├─ Track resubmission
├─ Compliance dashboard
└─ Regulatory reporting
```

#### 🔟 **HR Service Requests**
```
REQUEST MANAGEMENT →
├─ GET /requests (all)
├─ Types:
│  ├─ Experience Letters
│  ├─ Employment Verification
│  ├─ Salary Certificates
│  ├─ Leave Balance Certificates
│  ├─ Address Change
│  ├─ Nominee Update
│  └─ General HR Support

ASSIGNMENT & FULFILLMENT →
├─ PUT /requests/{id}/assign (to HR officer)
├─ PUT /requests/{id}/status (track progress)
├─ Comment/Q&A: POST /requests/{id}/comments
├─ Self-service generation (where applicable)
│  ├─ Auto-generate letters
│  ├─ Batch processing
│  └─ Digital signature
└─ Notification on completion
```

#### 1️⃣1️⃣ **HR Analytics & Reporting**
```
HR DASHBOARD →
├─ GET /reports/dashboard-summary
├─ Key Metrics:
│  ├─ Total Headcount by department
│  ├─ Turnover rate, resignation ratio
│  ├─ Vacancy rate, hiring in progress
│  └─ Engagement metrics

ATTENDANCE TRENDS →
├─ GET /reports/attendance
├─ Analytics:
│  ├─ Overall attendance rate
│  ├─ Absenteeism patterns (weekly, monthly)
│  ├─ Late-coming trends
│  ├─ Department comparison
│  └─ Early indicators of problems

LEAVE ANALYTICS →
├─ GET /reports/leave
├─ Views:
│  ├─ Leave utilization by type
│  ├─ Unused leave track
│  ├─ Leave balance distribution
│  ├─ Peak leave periods
│  └─ Compliance with min/max leave rules

PAYROLL ANALYTICS →
├─ GET /reports/payroll
├─ Data:
│  ├─ Total salary expense
│  ├─ Component-wise breakdown
│  ├─ Allowance vs deduction ratio
│  ├─ Tax compliance
│  └─ Top earners analysis

COMPETENCY ANALYTICS →
├─ GET /reports/competency
├─ Insights:
│  ├─ Skill distribution in workforce
│  ├─ Training completion status
│  ├─ Competency gaps by role
│  ├─ Succession readiness
│  └─ Development plan progress

EMPLOYEE LIFECYCLE →
├─ GET /reports/employee-lifecycle
├─ Trends:
│  ├─ New hires by month
│  ├─ Separations & reasons
│  ├─ Promotion rate & timelines
│  ├─ Engagement at life stages
│  └─ Onboarding time-to-productivity

ASSET ANALYTICS →
├─ GET /reports/assets
├─ Inventory:
│  ├─ Asset utilization rate
│  ├─ Maintenance frequency
│  ├─ Replacement schedule
│  ├─ Cost per employee
│  └─ Asset aging
```

### HR Officer API Endpoints Summary
```
GET    /api/employees                           (All employees)
POST   /api/employees                           (Create)
GET    /api/employees/{id}                      (Detail)
PUT    /api/employees/{id}                      (Update)
DELETE /api/employees/{id}                      (Remove)
PUT    /api/employees/{id}/onboarding/start     (Start onboarding)
PUT    /api/employees/{id}/onboarding/complete  (Complete onboarding)
PUT    /api/employees/{id}/offboarding/start    (Start offboarding)
PUT    /api/employees/{id}/offboarding/complete (Complete offboarding)

GET    /api/leave-policies                      (Policies)
POST   /api/leave-policies                      (Create policy)
PUT    /api/leave-policies/{id}                 (Update policy)
DELETE /api/leave-policies/{id}                 (Delete policy)
GET    /api/leaves                              (All leaves)
PUT    /api/leaves/{id}/approve                 (Final approval)
PUT    /api/leaves/{id}/reject                  (Final rejection)

GET    /api/attendance/all                      (All attendance)
DELETE /api/attendance/{id}                     (Correct entry)

GET    /api/payroll                             (All payroll)
POST   /api/payroll                             (Create)
POST   /api/payroll/generate/monthly            (Bulk generate)
GET    /api/payroll/{id}                        (Detail)
GET    /api/payroll/{id}/slip                   (Slip)
GET    /api/payroll/{id}/export                 (CSV export)
GET    /api/payroll/{id}/export-pdf             (PDF export)
PUT    /api/payroll/{id}                        (Update)
DELETE /api/payroll/{id}                        (Delete)
POST   /api/payroll/{id}/approve                (Approve)
POST   /api/payroll/{id}/pay                    (Mark paid)

GET    /api/payroll-details/{payroll_id}        (Details)
POST   /api/payroll-details                     (Add detail)
PUT    /api/payroll-details/{id}                (Update detail)
DELETE /api/payroll-details/{id}                (Delete detail)

GET    /api/reimbursements                      (All claims)
POST   /api/reimbursements                      (Create)
GET    /api/reimbursements/{id}                 (Detail)
PUT    /api/reimbursements/{id}                 (Update)
DELETE /api/reimbursements/{id}                 (Delete)
PUT    /api/reimbursements/{id}/approve         (Approve)
PUT    /api/reimbursements/{id}/reject          (Reject)
PUT    /api/reimbursements/{id}/mark-paid       (Mark paid)
GET    /api/reimbursements/pending              (Pending)
GET    /api/reimbursements/employee/{emp_id}   (By employee)
GET    /api/reimbursements/statistics           (Stats)

GET    /api/training/programs                   (All programs)
POST   /api/training/programs                   (Create program)
GET    /api/training/programs/{id}              (Program detail)
PUT    /api/training/programs/{id}              (Update program)
DELETE /api/training/programs/{id}              (Delete program)
POST   /api/training/programs/{id}/enroll       (Enroll)
PUT    /api/training/enrollments/{id}/complete  (Complete)

GET    /api/competencies                        (All competencies)
POST   /api/competencies                        (Create)
GET    /api/competencies/{id}                   (Detail)
PUT    /api/competencies/{id}                   (Update)
DELETE /api/competencies/{id}                   (Delete)
POST   /api/competencies/{id}/assign            (Assign to employee)
GET    /api/competencies/employee/{empId}      (Employee's)

GET    /api/assets                              (All assets)
POST   /api/assets                              (Create)
GET    /api/assets/{id}                         (Detail)
PUT    /api/assets/{id}                         (Update)
DELETE /api/assets/{id}                         (Delete)
POST   /api/assets/{id}/assign                  (Assign)
PUT    /api/assets/assignments/{assignmentId}/return (Return)

GET    /api/documents                           (All documents)
POST   /api/documents                           (Upload)
GET    /api/documents/{id}                      (Detail)
PUT    /api/documents/{id}                      (Update)
DELETE /api/documents/{id}                      (Delete)
GET    /api/documents/expiring                  (Expiring)
PUT    /api/documents/{id}/review               (Review)

GET    /api/requests                            (All requests)
POST   /api/requests                            (Create)
GET    /api/requests/{id}                       (Detail)
PUT    /api/requests/{id}/assign                (Assign)
PUT    /api/requests/{id}/status                (Update status)
POST   /api/requests/{id}/comments              (Add comment)
DELETE /api/requests/{id}                       (Delete)

GET    /api/reports/dashboard-summary           (Dashboard)
GET    /api/reports/attendance                  (Attendance analytics)
GET    /api/reports/leave                       (Leave analytics)
GET    /api/reports/payroll                     (Payroll analytics)
GET    /api/reports/competency                  (Competency analytics)
GET    /api/reports/employee-lifecycle          (Lifecycle analytics)
GET    /api/reports/assets                      (Asset analytics)

GET    /api/insights/people                     (People dashboard)
GET    /api/insights/people/detailed            (Detailed dashboard)
GET    /api/insights/people/trends              (Trends)
GET    /api/insights/people/team-health         (Team health)
+ All Employee endpoints
+ All Manager endpoints
```

---

## ADMIN WORKFLOW

### Role Definition
- **Access Level:** System Operations & Configuration
- **Scope:** All organizational data + System settings
- **Middleware:** `auth:sanctum, audit.trail, role:admin,super_admin`

### Admin Core Responsibilities
```
1. System Infrastructure & Configuration
2. Master Data Management (Locations, Schedules)
3. Access Control (Roles, Permissions, Users)
4. Audit & Compliance
5. Notifications & Communications
6. Integration Management
```

### Use Cases & Actions

#### 1️⃣ **Role & Permission Management**
```
ROLE MANAGEMENT →
├─ GET /admin/roles (view all roles)
├─ Define organizational roles:
│  ├─ Super Admin (full system access)
│  ├─ Admin (operations & config)
│  ├─ HR (operations - all employee data)
│  ├─ Manager (team management)
│  └─ Employee (self-service only)
├─ Create custom roles (if needed)
└─ Monitor role assignments

PERMISSION MANAGEMENT →
├─ GET /admin/permissions (all permissions)
├─ Define granular permissions:
│  ├─ view_payroll
│  ├─ approve_leave
│  ├─ manage_training
│  ├─ view_audit_logs
│  ├─ create_role
│  ├─ assign_permission
│  └─ Configure system settings
├─ Link permissions to roles
└─ Version control for changes

ASSIGN ROLE TO USER →
├─ GET /admin/users (all users)
├─ POST /users/{id}/assign-role
│  ├─ User ID
│  ├─ Select role
│  ├─ Effective date
│  ├─ Expiry date (optional)
│  └─ Reason for assignment
├─ Automatic notification to user
├─ Audit trail creation
└─ System updates user permissions in real-time

ASSIGN PERMISSION TO ROLE →
├─ POST /roles/{id}/assign-permission
│  ├─ Role ID
│  ├─ Select permissions (multi-select)
│  ├─ Effective date
│  └─ Reason/comment
├─ Update all users with this role
├─ Audit trail creation
└─ Backward/Forward compatibility check

MONITOR ROLE ASSIGNMENTS →
├─ View all user→role mappings
├─ Identify users without roles (access denied)
├─ Track role change history
├─ Deactivate old role assignments
├─ Orphaned role reviews
└─ Compliance reporting
```

#### 2️⃣ **User Management**
```
USER ACCOUNT MANAGEMENT →
├─ GET /admin/users (all users)
├─ View user status:
│  ├─ Active, Inactive, Suspended, Locked
│  ├─ Last login
│  ├─ Account created date
│  ├─ Password last changed
│  └─ Multi-factor auth status
├─ User lifecycle:
│  ├─ Create user account (by HR)
│  ├─ Activate account
│  ├─ Update user details
│  ├─ Suspend/Lock account (if needed)
│  └─ Deactivate on separation

PASSWORD MANAGEMENT →
├─ Reset user passwords (emergency)
├─ Force password change on next login
├─ Password policy enforcement:
│  ├─ Minimum length (12 chars)
│  ├─ Complexity requirements
│  ├─ Expiry period (90 days)
│  └─ History (prevent reuse)
├─ Lock account after failed attempts
└─ Audit log all changes

ACCOUNT SECURITY →
├─ Monitor suspicious activities
├─ Implement Multi-Factor Authentication (MFA)
├─ Session management:
│  ├─ View active sessions
│  ├─ Force logout if needed
│  └─ Set session timeout (24h)
├─ IP-based access control (if needed)
└─ API token management
```

#### 3️⃣ **Master Data Management**

**LOCATIONS**
```
LOCATION MANAGEMENT →
├─ POST /locations (create)
│  ├─ Location name, city, country
│  ├─ Address, postal code
│  ├─ Phone, email
│  ├─ Head of location
│  ├─ Department(s)
│  ├─ Facilities info
│  └─ Status (active, inactive)
├─ GET /locations (all)
├─ GET /locations/{id} (detail)
├─ PUT /locations/{id} (update)
└─ DELETE /locations/{id} (archive)

LOCATION USAGE →
├─ Link employees to location
├─ Attendance facilities setup
├─ Holiday calendar by location
├─ Shift/schedule management by location
└─ Reports by location
```

**WORK SCHEDULES**
```
WORK SCHEDULE MANAGEMENT →
├─ POST /work-schedules (create)
│  ├─ Schedule name (Weekdays, Weekends, Shift1, etc)
│  ├─ Working days (Mon-Fri, Mon-Sat, etc)
│  ├─ Start time, end time
│  ├─ Break time, lunch hours
│  ├─ Holidays/Blackout dates
│  ├─ Shift rotation (if applicable)
│  └─ Applicable locations/departments
├─ GET /work-schedules (all)
├─ GET /work-schedules/{id} (detail)
├─ PUT /work-schedules/{id} (update)
└─ DELETE /work-schedules/{id} (archive)

SCHEDULE APPLICATION →
├─ Assign to individual employees
├─ Assign to department
├─ Assign to location
├─ Override for specific dates
├─ Version control (past schedules retained for history)
└─ Notification to affected employees
```

#### 4️⃣ **Audit & Compliance**
```
AUDIT LOG VIEWING →
├─ GET /admin/audit-logs (all)
├─ Filter by:
│  ├─ User (who made change)
│  ├─ Module (what was changed - attendance, payroll, etc)
│  ├─ Action (create, update, delete, approve)
│  ├─ Date range
│  ├─ Resource type (employee, payroll, leave, etc)
│  └─ Result (success, failure)
├─ GET /admin/audit-logs/{id} (detail)

AUDIT TRAIL CONTENTS →
├─ Timestamp (exact second)
├─ User ID & name
├─ Action taken
├─ Resource type & ID
├─ Before/After values (for updates)
├─ IP address & user agent
├─ Status (success/failure)
└─ Error message (if failed)

COMPLIANCE REPORTING →
├─ Generate audit reports:
│  ├─ All changes to sensitive data
│  ├─ Unauthorized access attempts
│  ├─ Role/permission changes
│  ├─ User account changes
│  ├─ System configuration changes
│  └─ Failed transactions
├─ Export for external audit
└─ Archive logs (regulatory requirement - 7 years)
```

#### 5️⃣ **Notifications & Communications**
```
ADMIN NOTIFICATIONS →
├─ POST /admin/notifications/broadcast
│  ├─ Message content
│  ├─ Target audience:
│  │  ├─ All users
│  │  ├─ By role
│  │  ├─ By department
│  │  ├─ By location
│  │  └─ Specific users
│  ├─ Priority (normal, urgent)
│  ├─ Schedule (immediate, scheduled, recurring)
│  └─ Channels (in-app, email, SMS)

NOTIFICATION MANAGEMENT →
├─ POST /admin/notifications (create)
├─ View notification history
├─ Delivery status tracking
├─ Read rate analytics
├─ Re-send failed notifications
└─ Archive old notifications

SYSTEM ANNOUNCEMENTS →
├─ Maintenance notifications
├─ Policy updates
├─ Holiday announcements
├─ Important alerts
└─ Emergency communications
```

#### 6️⃣ **System Configuration**
```
FEATURE TOGGLES →
├─ Enable/disable features:
│  ├─ Google SSO
│  ├─ Leave module
│  ├─ Payroll module
│  ├─ Training module
│  ├─ Asset management
│  └─ Reporting module
├─ Version/beta features
└─ Gradual rollout control

EMAIL CONFIGURATION →
├─ SMTP settings
├─ Email templates:
│  ├─ Leave notifications
│  ├─ Payroll slips
│  ├─ Account creation
│  ├─ Password reset
│  └─ System alerts
├─ Test email sending
└─ Bounce handling

INTEGRATION SETTINGS →
├─ Google OAuth configuration
├─ API rate limiter settings
├─ Data sync schedules
├─ Webhook configurations
└─ Third-party service accounts

BACKUP & RECOVERY →
├─ Automated backup scheduling
├─ Backup encryption
├─ Recovery procedures
├─ Disaster recovery plan
└─ Data retention policies
```

#### 7️⃣ **Monitoring & Analytics**
```
SYSTEM HEALTH →
├─ API uptime & performance
├─ Database status
├─ Storage usage
├─ Active user count
├─ Failed login attempts
└─ System errors/exceptions

USER ANALYTICS →
├─ Active users today/month
├─ Module usage statistics
├─ Feature adoption rate
├─ Peak usage times
└─ Inactive user detection

SYSTEM LOGS →
├─ Application error logs
├─ System warnings
├─ Performance metrics
├─ Failed transactions
└─ Integration logs
```

### Admin API Endpoints Summary
```
GET    /api/admin/roles                         (View roles)
GET    /api/admin/permissions                   (View permissions)
GET    /api/admin/users                         (View users)
POST   /api/users/{id}/assign-role              (Assign role)
POST   /api/roles/{id}/assign-permission        (Assign permission)

GET    /api/locations                           (All locations)
POST   /api/locations                           (Create)
GET    /api/locations/{id}                      (Detail)
PUT    /api/locations/{id}                      (Update)
DELETE /api/locations/{id}                      (Delete)

GET    /api/work-schedules                      (All schedules)
POST   /api/work-schedules                      (Create)
GET    /api/work-schedules/{id}                 (Detail)
PUT    /api/work-schedules/{id}                 (Update)
DELETE /api/work-schedules/{id}                 (Delete)

GET    /api/admin/audit-logs                    (View logs)
GET    /api/admin/audit-logs/{id}               (Log detail)

POST   /api/admin/notifications                 (Create)
POST   /api/admin/notifications/broadcast       (Broadcast)

+ All HR Officer endpoints (for viewing/reporting)
+ Limited Employee endpoints (for admin reference)
```

---

## SUPER ADMIN WORKFLOW

### Role Definition
- **Access Level:** FULL SYSTEM ACCESS - Complete Control
- **Scope:** Everything accessible at all levels
- **Middleware:** `auth:sanctum, audit.trail, role:super_admin` (or bypassed entirely)

### Super Admin Responsibilities
```
1. Complete System Administration
2. Strategic Configuration
3. Access to ALL features at ALL levels
4. Override capabilities
5. System-wide audits
6. Crisis management
```

### Super Admin Capabilities

**✅ All Admin Functions** (System Operations)
- User & role management
- Permission configuration  
- Master data (locations, schedules)
- Audit log access
- System notifications
- Configuration management

**✅ All HR Functions** (Organization Operations)
- Full employee lifecycle
- Payroll operations
- Training management
- Asset management
- Compliance & documents
- HR reports & analytics

**✅ All Manager Functions** (Team Operations)
- Team attendance monitoring
- Leave approvals
- KPI management
- Training enrollment
- Team performance insights

**✅ All Employee Functions** (Self-Service)
- Own profile & data access
- Attendance records
- Leave management  
- Payroll access
- Training participation
- Request submission

**✅ OVERRIDE CAPABILITIES**
```
└─ Force approve/reject at any level
└─ Modify any data (with audit trail)
└─ Delete records
└─ Unlock user accounts
└─ Reset any password
└─ Override approval workflows
└─ Emergency access to all systems
└─ By-pass role restrictions (documented)
```

**✅ STRATEGIC FUNCTIONS**
```
└─ System configuration & feature toggles
└─ Third-party integrations
└─ Custom workflow configuration
└─ Data migration & imports
└─ Advanced analytics & reporting
└─ API key management
└─ Backup & disaster recovery
└─ License & subscription management
```

### Super Admin API Endpoints
**All endpoints accessible without restriction + Override commands**

---

## Authentication Flow

```
┌─────────────────────────────────────────────────────────────┐
│                 AUTHENTICATION WORKFLOW                     │
└─────────────────────────────────────────────────────────────┘

1. LOGIN ENDPOINT (Public)
   POST /api/login
   ├─ Email/Password validation
   ├─ Google OAuth redirect option
   └─ Return Bearer Token (valid 24h)

2. TOKEN GENERATION (Sanctum)
   ├─ Token hash stored in database
   ├─ User agent & IP address logged
   ├─ Expiry set to 24 hours
   └─ Refresh capability available

3. PROTECTED ROUTE ACCESS
   ├─ Header: Authorization: Bearer {TOKEN}
   ├─ Sanctum middleware validates token
   ├─ User loaded from database
   └─ Audit trail entry created

4. ROLE-BASED ACCESS CONTROL
   ├─ User.role loaded
   ├─ Route middleware checks:
   │  ├─ role:admin,hr,super_admin (examples)
   │  └─ User has required role(s)
   ├─ Permission blade syntax available
   └─ Access granted/denied

5. LOGOUT ENDPOINT (Protected)
   POST /api/logout
   ├─ Current token revoked
   └─ Audit trail entry created

GOOGLE SSO FLOW
   ├─ GET /api/auth/google (redirect to Google)
   ├─ User authorizes via Google
   ├─ GET /api/auth/google/callback (process OAuth response)
   ├─ Auto-create user if new
   ├─ Link existing user if found (by email)
   ├─ Generate Bearer Token
   └─ Redirect to frontend with token
```

---

## Approval Workflows

### Leave Approval Chain
```
EMPLOYEE                    MANAGER                         HR
   │                           │                            │
   │─── Request Leave ────────  │                            │
   │                           │                            │
   │                           ├─── Review & Approve ─────> │
   │                           │                            │
   │                           │    (Manager approval)       │
   │                           │                            │
   │                           │                      (HR Final Review)
   │                           │                            │
   │ <─────────── Approved ────────────────────────────────│
   │
   │ Notification sent
```

### KPI Approval Chain
```
EMPLOYEE                 MANAGER                      HR (Optional)
   │                        │                            │
   │       Submit KPI        │                            │
   │─────────────────────>   │                            │
   │                         │                            │
   │                    Manager Reviews                   │
   │              & Approves/Provides Feedback            │
   │                         │                            │
   │ <─────── Feedback ───────                            │
   │     (if needs revision)                              │
   │                                                       │
   │ <────── Approved ───────────────────────────────────│
   │                      (Final approval)
```

### Reimbursement Approval Chain (Optional: Manager → HR → Finance)
```
EMPLOYEE               MANAGER                  HR              FINANCE
   │                      │                     │                │
   │─ Submit Claim ─────> │                     │                │
   │                      │ Review & Approve ── │                │
   │                      │                     │ Verify ──────> │
   │                      │                     │                │
   │                      │                     │ <─ Mark Paid ──│
   │ <─── Approved ─────────────────────────────────────────────│
   │
   │ Notification + Payment initiated
```

### Payroll Approval Chain
```
SYSTEM                  HR OPERATIONAL                    ADMIN/FINANCE
   │                         │                              │
   │─ Generate (Bulk) ─────> │                              │
   │                         │                              │
   │                    Review & Validate                  │
   │                    • Calculations                     │
   │                    • Exceptions                       │
   │                    • Adjustments                      │
   │                         │                              │
   │                         ├─ Escalate to Admin ────────> │
   │                         │                              │
   │                         │                         Final Approval
   │                         │                              │
   │ <─── Approved ──────────────────────────────────────││
   │
   │ Export for Bank Transfer
   │ Send Slips to Employees
```

---

## Module Integration Matrix

| Feature | Employee | Manager | HR | Admin | Super Admin |
|---------|----------|---------|----|----|---|
| Own Attendance Check-in/out | ✅ | ✅ | ✅ | ✅ | ✅ |
| View Team Attendance | ❌ | ✅ | ✅ | ✅ | ✅ |
| Own Leave Request | ✅ | ✅ | ✅ | ✅ | ✅ |
| Approve Leave | ❌ | ✅ | ✅ | ✅ | ✅ |
| View Own Payroll | ✅ | ❌ | ✅ | ✅ | ✅ |
| Process Payroll | ❌ | ❌ | ✅ | ✅ | ✅ |
| Create KPI | ❌ | ✅ | ✅ | ✅ | ✅ |
| Submit KPI | ✅ | ✅ | ✅ | ✅ | ✅ |
| Approve KPI | ❌ | ✅ | ✅ | ✅ | ✅ |
| Manage Training | ❌ | ✅ | ✅ | ✅ | ✅ |
| View Reports | ❌ | ❌ | ✅ | ✅ | ✅ |
| Manage Roles/Permissions | ❌ | ❌ | ❌ | ✅ | ✅ |
| Access Audit Logs | ❌ | ❌ | ❌ | ✅ | ✅ |
| System Configuration | ❌ | ❌ | ❌ | ✅ | ✅ |

---

## Error Handling & Status Codes

```
200 OK              ✅ Request successful
201 Created         ✅ Resource created
204 No Content      ✅ Success, no content to return
400 Bad Request     ❌ Invalid input/validation error
401 Unauthorized    ❌ Missing/invalid token
403 Forbidden       ❌ Token valid, but insufficient permissions
404 Not Found       ❌ Resource doesn't exist
409 Conflict        ❌ Duplicate/conflict (e.g., duplicate email)
422 Unprocessable   ❌ Validation failed with detail
429 Rate Limited    ❌ Too many requests
500 Server Error    ❌ Internal error (contact support)
503 Unavailable     ❌ Service temporarily down

RESPONSE FORMAT
{
  "success": true/false,
  "message": "Action description",
  "data": { ... } or null,
  "errors": { ... } (if validation errors)
}
```

---

## Data Security & Privacy

```
ENCRYPTION
├─ Passwords: bcrypt (10 rounds)
├─ Tokens: SHA-256 hash
├─ Data in transit: HTTPS/TLS 1.2+
└─ Sensitive data: AES-256 (at-rest)

ACCESS CONTROL
├─ Role-Based Access Control (RBAC)
├─ Middleware enforcement on every request
├─ Granular permission checking
└─ Audit trail on all changes

DATA PRIVACY
├─ Personal data: PII protected
├─ Minimum data collection principle
├─ Data retention policies (GDPR compliant)
├─ Right to be forgotten support
└─ Regular security audits
```

---

## Compliance & Regulatory

```
STANDARDS MET
├─ GDPR (EU data privacy)
├─ SOX (Internal controls)
├─ ISO 27001 (Information security)
└─ Industry-specific regulations

AUDIT TRAIL
├─ Automatic logging of all changes
├─ 7-year data retention
├─ Tamper-proof logging
├─ Exportable reports for auditors
└─ Real-time alert capabilities

DOCUMENT RETENTION
├─ Employee records: 5+ years post-separation
├─ Payroll records: 7 years
├─ Financial records: 7 years
├─ Audit logs: 7 years
└─ Training records: 3+ years
```

---

## Testing Checklist

```
EMPLOYEE TEST FLOW
└─ [ ] Register / Login
   [ ] Update own profile
   [ ] Check-in / Check-out
   [ ] Request leave
   [ ] Submit KPI
   [ ] Submit reimbursement
   [ ] View payroll slip
   [ ] Download PDF slip
   [ ] View notifications

MANAGER TEST FLOW
└─ [ ] Login as manager
   [ ] View team attendance
   [ ] Approve team leaves
   [ ] Create KPI for team
   [ ] Review team KPI
   [ ] Approve reimbursements
   [ ] View team insights
   [ ] Manage team training

HR TEST FLOW
└─ [ ] Process monthly payroll
   [ ] Generate payroll
   [ ] Approve payroll
   [ ] Export payroll (CSV/PDF)
   [ ] Manage leave policies
   [ ] View analytics
   [ ] Process documents
   [ ] Manage employee lifecycle

ADMIN TEST FLOW
└─ [ ] Assign role to user
   [ ] Assign permission to role
   [ ] Create location
   [ ] Create work schedule
   [ ] View audit logs
   [ ] Create system announcement
   [ ] View user management

SUPER ADMIN TEST FLOW
└─ [ ] Access all functions
   [ ] Override approvals
   [ ] Emergency override test
   [ ] Full system audit check
```

---

## Conclusion

This HRIS system provides a **complete, role-based HR management solution** with:
- ✅ **5 distinct role levels** (Employee → Super Admin)
- ✅ **15+ major modules** (Attendance, Payroll, Leave, Training, etc.)
- ✅ **Automated workflows** (Approvals, notifications, audit trails)
- ✅ **Advanced reporting & analytics**
- ✅ **Enterprise-grade security** (RBAC, encryption, audit logs)
- ✅ **Production-ready** with proper error handling & compliance

**Ready for deployment and use!** 🚀

---

**Document Version:** 1.0  
**Last Updated:** April 2026  
**Status:** Complete & Ready for Production
