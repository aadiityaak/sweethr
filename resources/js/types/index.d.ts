import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href?: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
    items?: NavItem[];
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    employee_id?: string;
    phone?: string;
    date_of_birth?: string;
    gender?: string;
    address?: string;
    hire_date?: string;
    department_id?: number;
    position_id?: number;
    manager_id?: number;
    employment_status?: string;
    emergency_contact?: any;
    is_admin: boolean;
}

export type BreadcrumbItemType = BreadcrumbItem;
