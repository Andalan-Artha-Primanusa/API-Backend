$css = @"
/* ========================================
   AUTH LAYOUT - Modern Enterprise Layout
   ======================================== */

.auth-page {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 55% 45%;
  background: var(--color-white);
}

/* Left Visual Panel */
.auth-visual-panel {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(2rem, 4vw, 4rem);
  background: var(--color-surface-soft);
  overflow: hidden;
}

.auth-visual-content {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 1.5rem;
  max-width: 480px;
}

.auth-visual-brand {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-xl);
  background: var(--color-primary);
  color: var(--color-white);
  display: flex;
  align-items: center;
  justify-content: center;
}

.auth-visual-headline {
  font-family: var(--font-family-heading);
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  font-weight: var(--font-weight-bold);
  line-height: 1.1;
  letter-spacing: var(--letter-spacing-tight);
  color: var(--color-primary-dark);
  margin: 0;
}

.auth-visual-desc {
  font-size: clamp(0.9rem, 1.2vw, 1.1rem);
  line-height: 1.6;
  color: var(--color-text-secondary);
  margin: 0;
  max-width: 400px;
}

.auth-visual-decoration {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
}

.auth-deco-circle {
  position: absolute;
  border-radius: 50%;
  background: var(--color-primary);
  opacity: 0.03;
}

.auth-deco-1 {
  width: 500px;
  height: 500px;
  top: -200px;
  right: -150px;
}

.auth-deco-2 {
  width: 300px;
  height: 300px;
  bottom: -100px;
  left: -100px;
}

/* Right Form Panel */
.auth-form-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(1.5rem, 3vw, 3rem);
  background: var(--color-white);
}

.auth-card {
  width: min(420px, 100%);
  padding: clamp(1.5rem, 2.5vw, 2.5rem);
}

.auth-card-header {
  margin-bottom: 2rem;
}

.auth-card-logo {
  display: none; /* Hide logo in the form to avoid duplication with visual panel */
}

.auth-title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-size: clamp(1.5rem, 2vw, 2rem);
  font-weight: var(--font-weight-bold);
  line-height: 1.2;
  letter-spacing: var(--letter-spacing-tight);
  color: var(--color-text-primary);
}

.auth-subtitle {
  margin: 0.5rem 0 0;
  color: var(--color-text-secondary);
  font-size: var(--font-size-md);
}

/* Form */
.auth-form {
  display: flex;
  flex-direction: column;
}

.auth-field {
  margin-bottom: 1.25rem;
}

.auth-label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  color: var(--color-text-primary);
}

.auth-input {
  width: 100%;
  height: 44px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background-color: var(--color-white);
  color: var(--color-text-primary);
  font-size: var(--font-size-md);
  padding: 0 1rem;
  transition: all var(--transition-fast);
}

.auth-input::placeholder {
  color: var(--color-text-tertiary);
}

.auth-input:focus {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(22, 160, 133, 0.1);
}

.auth-input-error {
  border-color: var(--color-error);
  background-color: #FFFAFA;
}

.auth-input-error:focus {
  border-color: var(--color-error);
  box-shadow: 0 0 0 3px rgba(217, 76, 76, 0.12);
}

.auth-field-error {
  margin-top: 0.375rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--color-error);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.auth-password-wrap {
  position: relative;
}

.auth-password-wrap .auth-input {
  padding-right: 2.75rem;
}

.auth-password-toggle {
  position: absolute;
  top: 50%;
  right: 0.625rem;
  transform: translateY(-50%);
  border: none;
  background: transparent;
  color: var(--color-text-tertiary);
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  border-radius: var(--radius-sm);
}

.auth-password-toggle:hover {
  color: var(--color-text-primary);
}

.auth-form-error {
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: var(--color-error-light);
  color: var(--color-error);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  border-radius: var(--radius-md);
  border: 1px solid rgba(217, 76, 76, 0.2);
}

.auth-primary-button {
  height: 44px;
  border: none;
  border-radius: var(--radius-md);
  background: var(--color-primary);
  color: var(--color-white);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.auth-primary-button:hover {
  background: var(--color-primary-hover);
}

.auth-primary-button:active {
  transform: translateY(1px);
}

.auth-primary-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.auth-button-spinner {
  animation: authSpin 0.8s linear infinite;
}

.auth-social-button {
  margin-top: 1rem;
  height: 44px;
  border-radius: var(--radius-md);
  border: 1px solid var(--color-border);
  background: var(--color-white);
  color: var(--color-text-primary);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-medium);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all var(--transition-fast);
}

.auth-social-button:hover {
  background-color: var(--color-surface-soft);
}

.auth-footer {
  margin-top: 1.5rem;
  text-align: center;
  color: var(--color-text-secondary);
  font-size: var(--font-size-sm);
}

.auth-footer-link {
  border: none;
  background: transparent;
  color: var(--color-primary-dark);
  font-size: inherit;
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  padding: 0;
}

.auth-footer-link:hover {
  text-decoration: underline;
}

.auth-copyright {
  margin-top: 2rem;
  text-align: center;
  color: var(--color-text-tertiary);
  font-size: 11px;
}

@keyframes authSpin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 920px) {
  .auth-page {
    grid-template-columns: 1fr;
  }
  .auth-visual-panel {
    display: none;
  }
  .auth-form-panel {
    min-height: 100dvh;
    padding: 1.5rem;
  }
  .auth-card-logo {
    display: flex;
  }
}

@media (max-width: 480px) {
  .auth-form-panel {
    min-height: 100svh;
    padding: 1rem;
    align-items: flex-start;
    padding-top: 3rem;
  }
  .auth-card {
    width: 100%;
    padding: 1.25rem;
  }
  .auth-title {
    font-size: 1.5rem;
  }
}
"@
Set-Content C:\Users\raulm\Downloads\hris-frontend\src\pages\auth\AuthLayout.css $css -Encoding UTF8
