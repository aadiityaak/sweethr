// Import specific routes as needed
import * as payrollPeriodsRoutes from '@/routes/admin/payroll-periods'
import * as payrollsRoutes from '@/routes/admin/payrolls'
import * as employeePayrollsRoutes from '@/routes/employee/payrolls'
import * as mainRoutes from '@/routes'

// Route mapping for dot notation access
const routeMap: Record<string, any> = {
    // Admin routes
    'admin.payroll-periods.index': payrollPeriodsRoutes.index,
    'admin.payroll-periods.create': payrollPeriodsRoutes.create,
    'admin.payroll-periods.store': payrollPeriodsRoutes.store,
    'admin.payroll-periods.show': payrollPeriodsRoutes.show,
    'admin.payroll-periods.edit': payrollPeriodsRoutes.edit,
    'admin.payroll-periods.update': payrollPeriodsRoutes.update,
    'admin.payroll-periods.destroy': payrollPeriodsRoutes.destroy,
    'admin.payroll-periods.set-active': payrollPeriodsRoutes.setActive,
    'admin.payroll-periods.generate-payroll': payrollPeriodsRoutes.generatePayroll,
    'admin.payroll-periods.generate-yearly': payrollPeriodsRoutes.generateYearly,

    // Admin payroll routes
    'admin.payrolls.index': payrollsRoutes.index,
    'admin.payrolls.show': payrollsRoutes.show,
    'admin.payrolls.generate': payrollsRoutes.generate,
    'admin.payrolls.regenerate': payrollsRoutes.regenerate,

    // Employee payroll routes
    'employee.payrolls.index': employeePayrollsRoutes.index,
    'employee.payrolls.show': employeePayrollsRoutes.show,

    // Main routes
    'login': mainRoutes.login,
    'logout': mainRoutes.logout,
    'home': mainRoutes.home,
    'welcome': mainRoutes.welcome,
    'dashboard': mainRoutes.dashboard,
    'register': mainRoutes.register,
}

export type RouteName = keyof typeof routeMap

// Create a route function that mimics Ziggy's route helper
export function route(name: RouteName, params?: any): string {
    const routeFn = routeMap[name]

    if (!routeFn) {
        throw new Error(`Route '${name}' not found`)
    }

    // At this point, routeFn should be the route function
    if (typeof routeFn === 'function') {
        return routeFn(params)
    }

    // If it's a URL string property (for routes without parameters)
    if (typeof routeFn === 'object' && routeFn.url) {
        return routeFn.url(params)
    }

    throw new Error(`Invalid route definition for '${name}'`)
}

// Make route available globally
declare global {
    interface Window {
        route: typeof route
    }
}

if (typeof window !== 'undefined') {
    window.route = route
}