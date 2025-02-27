import { LucideIcon } from 'lucide-react';
import { ConnectedAccount } from '@/types/socialstream';

export interface Auth {
    user: User;
    connectedAccounts: ConnectedAccount[],
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    connected_accounts: ConnectedAccount[],
    [key: string]: unknown; // This allows for additional properties...
}
