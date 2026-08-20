$css = @"
.crud-page {
  padding: var(--space-4);
  background: var(--color-background);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  gap: var(--space-4);
}

.hero-card {
  background: var(--color-primary-dark);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
}

.hero-card-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: var(--space-6);
}

.hero-content {
  flex: 1;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-1) var(--space-3);
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-semibold);
  letter-spacing: var(--letter-spacing-wide);
  text-transform: uppercase;
  color: var(--color-white);
  margin-bottom: var(--space-3);
  background: rgba(255,255,255,0.15);
}

.hero-title {
  font-family: var(--font-family-heading);
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-white);
  margin: 0 0 var(--space-2) 0;
  letter-spacing: var(--letter-spacing-tight);
}

.hero-subtitle {
  font-size: var(--font-size-md);
  color: rgba(255, 255, 255, 0.9);
  margin: 0;
  max-width: 50ch;
}

.hero-actions {
  display: flex;
  gap: var(--space-3);
  flex-shrink: 0;
}

.hero-actions .btn-outline,
.hero-actions .btn-primary {
  background: var(--color-white);
  border: none;
  color: var(--color-primary-dark);
  font-weight: var(--font-weight-semibold);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: all var(--transition-fast);
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.hero-actions .btn-outline:hover,
.hero-actions .btn-primary:hover {
  background: rgba(255, 255, 255, 0.9);
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.error-card {
  background: var(--color-error-light);
  border: 1px solid var(--color-error);
  border-radius: var(--radius-md);
  padding: var(--space-3) var(--space-4);
}

.error-card p {
  color: var(--color-error);
  margin: 0;
  font-weight: var(--font-weight-medium);
}

.analytics-title-card {
  background: var(--color-primary-dark);
  border-radius: var(--radius-lg);
  padding: var(--space-4) var(--space-6);
  box-shadow: var(--shadow-sm);
}

.analytics-title-inner {
  display: flex;
  align-items: center;
  gap: var(--space-4);
}

.analytics-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--color-white);
  background: rgba(255, 255, 255, 0.2);
  box-shadow: var(--shadow-sm);
}

.analytics-title {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--color-white);
  margin: 0;
  font-family: var(--font-family-heading);
}

.analytics-subtitle {
  font-size: var(--font-size-sm);
  color: rgba(255, 255, 255, 0.85);
  margin: var(--space-1) 0 0 0;
  font-family: var(--font-family-base);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: var(--space-4);
}

.metric-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  transition: all var(--transition-normal);
  border: 1px solid var(--color-border);
  box-shadow: var(--shadow-sm);
}

.metric-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-md);
  border-color: var(--color-primary);
}

.metric-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--space-3);
}

.metric-label {
  display: block;
  font-size: var(--font-size-xs);
  text-transform: uppercase;
  letter-spacing: var(--letter-spacing-wide);
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-secondary);
  margin-bottom: var(--space-1);
}

.metric-subtitle {
  font-size: var(--font-size-xs);
  color: var(--color-text-tertiary);
  margin: 0;
}

.metric-icon {
  width: 56px;
  height: 56px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: var(--shadow-sm);
}

.metric-icon--blue {
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
}

.metric-icon--green {
  background: #d1fae5;
  color: var(--color-success);
}

.metric-icon--orange {
  background: #fef3c7;
  color: var(--color-warning);
}

.metric-icon--purple {
  background: #f3e8ff;
  color: #7c3aed;
}

.metric-value {
  font-size: var(--font-size-2xl);
  font-weight: var(--font-weight-bold);
  font-family: var(--font-family-heading);
  color: var(--color-text-primary);
  margin-bottom: var(--space-2);
}

.metric-change {
  font-size: var(--font-size-xs);
  color: var(--color-text-secondary);
}

.charts-section {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: var(--space-4);
}

.chart-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  border: 1px solid var(--color-border);
  transition: all var(--transition-normal);
  box-shadow: var(--shadow-sm);
}

.chart-card:hover {
  border-color: var(--color-primary);
  box-shadow: var(--shadow-md);
}

.chart-header {
  margin-bottom: var(--space-4);
}

.chart-title-area {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.chart-title {
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-bold);
  font-family: var(--font-family-heading);
  color: var(--color-text-primary);
  margin: 0;
}

.chart-subtitle {
  font-size: var(--font-size-sm);
  color: var(--color-text-secondary);
  margin: 0;
}

.chart-empty {
  height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-surface-soft);
  border-radius: var(--radius-md);
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
}

@media (max-width: 1200px) {
  .charts-section {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .crud-page {
    padding: var(--space-3);
  }

  .hero-card {
    padding: var(--space-4);
  }

  .hero-card-inner {
    flex-direction: column;
    align-items: flex-start;
  }

  .hero-title {
    font-size: var(--font-size-xl);
  }

  .hero-actions {
    width: 100%;
  }

  .hero-actions button {
    flex: 1;
  }

  .analytics-title-card {
    padding: var(--space-4);
  }

  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}
"@
Set-Content C:\Users\raulm\Downloads\hris-frontend\src\pages\dashboard\overview\OverviewPage.css $css -Encoding UTF8
