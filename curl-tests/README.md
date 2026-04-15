# Role-based curl smoke tests

Use these scripts to test the HRIS API faster by role.

## Files

- `admin-curl.ps1` for admin and super-admin routes
- `hr-curl.ps1` for HR routes
- `manager-curl.ps1` for manager routes
- `employee-curl.ps1` for employee self-service routes

## Environment variables

- `HRIS_BASE_URL` defaults to `http://127.0.0.1:8000/api`
- `HRIS_ADMIN_TOKEN` for the admin script
- `HRIS_HR_TOKEN` for the HR script
- `HRIS_MANAGER_TOKEN` for the manager script
- `HRIS_EMPLOYEE_TOKEN` for the employee script

## Run examples

```powershell
powershell -ExecutionPolicy Bypass -File .\curl-tests\admin-curl.ps1
powershell -ExecutionPolicy Bypass -File .\curl-tests\hr-curl.ps1
powershell -ExecutionPolicy Bypass -File .\curl-tests\manager-curl.ps1
powershell -ExecutionPolicy Bypass -File .\curl-tests\employee-curl.ps1
```