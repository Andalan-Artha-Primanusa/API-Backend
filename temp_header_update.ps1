$css = @"
/* ========================================
   HEADER - Modern Enterprise Layout
   ======================================== */

.dashboard-header {
  height: 64px;
  background: var(--color-white);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 var(--padding-xl);
  position: sticky;
  top: 0;
  z-index: 200;
}

.header-left, .header-right {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.header-right {
  margin-left: auto;
  flex-wrap: nowrap;
  min-width: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: var(--space-1);
  flex-wrap: nowrap;
  flex-shrink: 0;
}

.icon-button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  min-width: 40px;
  min-height: 40px;
  flex-shrink: 0;
  border-radius: var(--radius-full);
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  cursor: pointer;
  transition: all var(--transition-fast);
}

.icon-button:hover {
  background-color: var(--color-surface-soft);
  color: var(--color-primary-dark);
}

/* Notification */
.notification-wrapper {
  position: relative;
  flex-shrink: 0;
}

.notification-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  background: var(--color-error);
  color: var(--color-white);
  font-size: 10px;
  font-weight: var(--font-weight-bold);
  min-width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-full);
  border: 2px solid var(--color-white);
}

.notification-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: var(--space-2);
  width: 360px;
  background: var(--color-white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--color-border);
  z-index: 300;
  overflow: hidden;
  animation: slideDown 0.15s ease-out;
}

@keyframes slideDown {
  from { opacity: 0; transform: translateY(-8px); }
  to { opacity: 1; transform: translateY(0); }
}

.notification-dropdown-header {
  padding: var(--padding-lg);
  border-bottom: 1px solid var(--color-border-light);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-dropdown-header h3 {
  margin: 0;
  font-family: var(--font-family-heading);
  font-size: var(--font-size-md);
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-primary);
}

.unread-count {
  background: var(--color-primary-lighter);
  color: var(--color-primary-dark);
  padding: 2px 8px;
  border-radius: var(--radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
}

.notification-list {
  max-height: 360px;
  overflow-y: auto;
}

.notification-item {
  display: flex;
  gap: var(--space-3);
  padding: var(--padding-md) var(--padding-lg);
  cursor: pointer;
  transition: all var(--transition-fast);
  border-bottom: 1px solid var(--color-border-light);
  position: relative;
  text-align: left;
}

.notification-item:hover {
  background: var(--color-surface-soft);
}

.notification-item.unread {
  background: var(--color-primary-lighter);
}

.notification-item-icon {
  width: 32px;
  height: 32px;
  background: var(--color-primary-light);
  color: var(--color-primary-dark);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.notification-item-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  margin: 0;
  font-family: var(--font-family-heading);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-semibold);
  color: var(--color-text-primary);
  line-height: 1.3;
}

.notification-message {
  margin: 2px 0 0 0;
  font-size: var(--font-size-xs);
  color: var(--color-text-secondary);
  line-height: 1.4;
}

.notification-time {
  display: block;
  font-size: 11px;
  color: var(--color-text-tertiary);
  margin-top: 4px;
}

.unread-dot {
  width: 7px;
  height: 7px;
  background: var(--color-primary);
  border-radius: 50%;
  position: absolute;
  top: 1.25rem;
  right: 1rem;
}

.notification-empty {
  padding: 2.5rem 1rem;
  text-align: center;
  color: var(--color-text-tertiary);
  font-size: var(--font-size-sm);
}

.notification-dropdown-footer {
  padding: var(--padding-sm);
  text-align: center;
  background: var(--color-surface-soft);
  border-top: 1px solid var(--color-border-light);
}

.notification-dropdown-footer button {
  background: transparent;
  border: none;
  color: var(--color-primary-dark);
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  cursor: pointer;
}

.notification-dropdown-footer button:hover {
  text-decoration: underline;
}

/* User Profile */
.user-profile {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  padding-left: var(--padding-md);
  border-left: 1px solid var(--color-border);
  cursor: pointer;
  position: relative;
}

.avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-full);
  background-color: var(--color-primary-lighter);
  color: var(--color-primary-dark);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: var(--font-weight-semibold);
  font-size: var(--font-size-sm);
  border: 1px solid var(--color-primary-light);
}

.user-info {
  display: flex;
  flex-direction: column;
}

.user-name {
  font-family: var(--font-family-heading);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  color: var(--color-text-primary);
  line-height: 1.2;
}

.user-role {
  font-size: 11px;
  color: var(--color-text-tertiary);
}

.user-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: var(--space-2);
  background-color: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-lg);
  padding: var(--padding-xs);
  display: flex;
  flex-direction: column;
  min-width: 180px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-8px);
  transition: all var(--transition-fast);
  z-index: 300;
}

.user-dropdown.visible {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--padding-sm) var(--padding-md);
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  color: var(--color-text-secondary);
  cursor: pointer;
  font-size: var(--font-size-sm);
  transition: background var(--transition-fast);
}

.dropdown-item:hover {
  background: var(--color-surface-soft);
  color: var(--color-text-primary);
}

.logout-item {
  color: var(--color-error);
}

.logout-item:hover {
  background: var(--color-error-light);
  color: var(--color-error);
}

/* Rotating animation */
@keyframes rotate {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.rotating {
  animation: rotate 1s linear infinite;
}

/* Header action icon */
.header-action-icon {
  width: 20px;
  height: 20px;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    padding: 0 1rem;
  }
  .user-info {
    display: none;
  }
  .notification-dropdown {
    width: 300px;
    right: -1rem;
  }
}

@media (max-width: 480px) {
  .header-actions {
    gap: 0;
  }
  .icon-button {
    width: 36px;
    height: 36px;
    min-width: 36px;
    min-height: 36px;
  }
}
"@
Set-Content C:\Users\raulm\Downloads\hris-frontend\src\widgets\layout\Header.css $css -Encoding UTF8
