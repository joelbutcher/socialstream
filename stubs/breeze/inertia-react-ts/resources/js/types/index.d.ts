export interface ConnectedAccount {
    id: number;
    provider: string;
    provider_id: number;
    created_at: string
}

export interface Socialstream {
    show: boolean;
    hasPassword: boolean;
    providers: string[];
    connectedAccounts: ConnectedAccount[];
}

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};
