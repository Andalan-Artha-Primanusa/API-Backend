$css = @"
.sidebar-logo {
  height: var(--header-height);
  min-height: var(--header-height);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 var(--padding-lg);
  border-bottom: 1px solid var(--color-border);
  background: var(--color-white);
  flex-shrink: 0;
}

.dashboard-sidebar {
  border-right: 1px solid var(--color-border);
  background-color: var(--color-white);
  z-index: 20;
}

.dashboard-sidebar.collapsed .sidebar-logo {
  justify-content: center;
  padding: 0 var(--padding-sm);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.brand-text {
  font-family: var(--font-family-heading);
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-bold);
  color: var(--color-primary-dark);
  letter-spacing: var(--letter-spacing-tight);
}

.brand-text-collapsed {
  font-family: var(--font-family-heading);
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--color-primary-dark);
}

.sidebar-menu-container {
  padding: var(--padding-md) var(--padding-sm) var(--padding-lg);
  overflow-y: auto;
  overflow-x: hidden;
  flex: 1;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.sidebar-menu-container::-webkit-scrollbar {
  display: none;
}

.menu-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
}

.menu-item-wrapper {
  /* wrapper */
}

.menu-item {
  display: flex;
  align-items: center;
  padding: var(--padding-sm) var(--padding-md);
  border-radius: var(--radius-md);
  color: var(--color-text-secondary);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  transition: var(--transition-fast);
  position: relative;
  overflow: hidden;
}

.menu-icon {
  flex-shrink: 0;
  color: var(--color-text-tertiary);
  transition: var(--transition-fast);
}

.menu-item:hover {
  background-color: var(--color-surface-soft);
  color: var(--color-text-primary);
}

.menu-item:hover .menu-icon {
  color: var(--color-primary);
}

.menu-item.active {
  background-color: var(--color-primary-lighter);
  color: var(--color-primary-dark);
  font-weight: var(--font-weight-semibold);
}

.menu-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  height: 60%;
  width: 3px;
  background-color: var(--color-primary);
  border-radius: 0 3px 3px 0;
}

.menu-item.active .menu-icon {
  color: var(--color-primary-dark);
}

.dashboard-sidebar.collapsed .menu-item.active {
  background-color: var(--color-primary-lighter);
}

.menu-label {
  margin-left: var(--space-3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  flex: 1;
}

.dashboard-sidebar.collapsed .menu-item {
  justify-content: center;
  padding: var(--padding-md) 0;
}

.dashboard-sidebar.collapsed .menu-icon {
  margin: 0;
}

.menu-item.has-submenu {
  cursor: pointer;
  user-select: none;
}

.menu-chevron {
  flex-shrink: 0;
  margin-left: auto;
  display: flex;
  align-items: center;
  color: var(--color-text-tertiary);
  transition: transform 0.2s ease;
}

.menu-item.has-submenu:hover .menu-chevron {
  color: var(--color-primary);
}

.menu-chevron.expanded {
  transform: rotate(90deg);
  color: var(--color-primary);
}

.submenu-wrapper {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.25s ease;
}

.submenu-wrapper.expanded {
  grid-template-rows: 1fr;
}

.submenu-list {
  min-height: 0;
  overflow: hidden;
  list-style: none;
}

.submenu-item {
  display: flex;
  align-items: center;
  padding: var(--padding-xs) var(--padding-md);
  border-radius: var(--radius-md);
  color: var(--color-text-secondary);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  transition: var(--transition-fast);
  text-decoration: none;
  margin-top: 2px;
}

.submenu-item:hover {
  color: var(--color-primary-dark);
  background-color: var(--color-surface-soft);
}

.submenu-item.active {
  color: var(--color-primary-dark);
  font-weight: var(--font-weight-semibold);
  background-color: var(--color-primary-lighter);
}

.submenu-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background-color: var(--color-border);
  margin-right: 0;
  flex-shrink: 0;
  transition: var(--transition-fast);
  opacity: 0.7;
}

.submenu-item.active .submenu-dot {
  background-color: var(--color-primary);
  opacity: 1;
}

.submenu-item:hover .submenu-dot {
  background-color: var(--color-primary);
  transform: scale(1.5);
  opacity: 1;
}
"@
Set-Content C:\Users\raulm\Downloads\hris-frontend\src\widgets\layout\Sidebar.css $css -Encoding UTF8
