$css = @"
/* ========================= */
/* EmployeeDashboardPage Styles */
/* ========================= */

/* Summary Cards */
.employee-summary-wrapper {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: var(--space-4);
  margin-bottom: var(--space-6);
}

.employee-summary-card {
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  background: var(--color-white);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-normal);
  display: flex;
  flex-direction: column;
}

.employee-summary-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-primary);
}

.employee-summary-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--space-3);
}

.employee-summary-label {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  font-weight: var(--font-weight-medium);
  margin-bottom: var(--space-1);
}

.employee-summary-subtitle {
  font-size: var(--font-size-xs);
  color: var(--color-text-tertiary);
  margin: 0;
}

.employee-summary-value {
  font-size: var(--font-size-2xl);
  color: var(--color-text-primary);
  font-weight: var(--font-weight-bold);
  line-height: 1.2;
  font-family: var(--font-family-heading);
}

.employee-summary-icon-wrapper {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: var(--shadow-sm);
  flex-shrink: 0;
}

.employee-icon-blue {
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
}

.employee-icon-green {
  background: #d1fae5;
  color: var(--color-success);
}

.employee-icon-orange {
  background: #fef3c7;
  color: var(--color-warning);
}

.employee-icon-red {
  background: #fee2e2;
  color: var(--color-error);
}

.employee-icon-purple {
  background: #f3e8ff;
  color: #7c3aed;
}

.employee-value-blue { color: var(--color-primary); }
.employee-value-green { color: var(--color-success); }
.employee-value-orange { color: var(--color-warning); }
.employee-value-red { color: var(--color-error); }
.employee-value-purple { color: #7c3aed; }

/* Charts Grid */
.charts-grid-2 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: var(--space-4);
  margin-bottom: var(--space-4);
}

/* Chart Card */
.chart-card {
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  background: var(--color-white);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.chart-header {
  margin-bottom: var(--space-3);
}

.chart-title-area {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.chart-title {
  margin: 0;
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
  font-family: var(--font-family-heading);
  color: var(--color-text-primary);
}

.chart-subtitle {
  margin: 0;
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
}

.chart-empty {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 200px;
  color: var(--color-text-tertiary);
  font-size: var(--font-size-sm);
}

/* Table Card */
.table-card {
  padding: 0 !important;
  border-radius: var(--radius-lg) !important;
  border: 1px solid var(--color-border) !important;
  background: var(--color-white) !important;
  box-shadow: var(--shadow-sm) !important;
  overflow: hidden;
}

.table-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: var(--space-3) var(--space-4);
  background: var(--color-white);
  color: var(--color-text-primary);
  border-bottom: 1px solid var(--color-border);
}

.table-header-bar h3 {
  margin: 0;
  font-size: var(--font-size-md);
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-bold);
  color: var(--color-text-primary);
}

.table-wrap {
  overflow-x: auto;
  padding: 0;
  scrollbar-width: thin;
  scrollbar-color: var(--color-border) transparent;
}

.data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 600px;
  font-family: var(--font-family-base);
}

.data-table thead th {
  background: var(--color-surface-soft);
  padding: var(--space-3) var(--space-4);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
  text-transform: uppercase;
  letter-spacing: var(--letter-spacing-wide);
  color: var(--color-text-secondary);
  border-bottom: 1px solid var(--color-border);
  text-align: left;
}

.data-table tbody td {
  padding: var(--space-4);
  border-bottom: 1px solid var(--color-border-light);
  vertical-align: middle;
  color: var(--color-text-primary);
}

.data-table tr:hover {
  background: var(--color-surface-soft);
}

.data-table .cell-stacked {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.data-table .cell-stacked__main {
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-primary);
  font-size: var(--font-size-sm);
}

.data-table .cell-stacked__sub {
  font-size: var(--font-size-xs);
  color: var(--color-text-secondary);
}

.td-center {
  text-align: center;
}

.th-center {
  text-align: center;
}

/* Status Badges */
.status-badge {
  display: inline-flex;
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
}

.status-badge--approved {
  background: var(--color-success-light);
  color: var(--color-success);
}

.status-badge--pending {
  background: var(--color-warning-light);
  color: var(--color-warning);
}

.status-badge--draft {
  background: var(--color-error-light);
  color: var(--color-error);
}

.badge-soft {
  display: inline-flex;
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
}

.badge-soft--blue {
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
}

/* Buttons */
.btn-primary {
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  border: none;
  background: var(--color-primary);
  color: var(--color-white);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all var(--transition-fast);
  font-family: var(--font-family-base);
}

.btn-primary:hover {
  background: var(--color-primary-hover);
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
}

.btn-outline {
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background: var(--color-white);
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all var(--transition-fast);
  font-family: var(--font-family-base);
}

.btn-outline:hover {
  background: var(--color-surface-soft);
  border-color: var(--color-primary);
  color: var(--color-primary-dark);
}

.btn-full {
  width: 100%;
}

/* Analytics Card */
.analytics-title-card {
  padding: var(--space-4);
  border-radius: var(--radius-lg);
  border: 1px solid var(--color-border);
  background: var(--color-white);
  box-shadow: var(--shadow-sm);
  margin-bottom: var(--space-4);
}

.analytics-title-inner {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.analytics-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-md);
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.analytics-title {
  margin: 0;
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
  font-family: var(--font-family-heading);
  color: var(--color-text-primary);
}

.analytics-subtitle {
  margin: 0;
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
}
"@
Set-Content C:\Users\raulm\Downloads\hris-frontend\src\pages\dashboard\EmployeeDashboardPage.css $css -Encoding UTF8
