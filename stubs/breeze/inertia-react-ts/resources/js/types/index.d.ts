export interface ConnectedAccount {
    id: number;
    provider: string;
    provider_id: number;
    created_at: string
}

export declare type ProviderId = 'bitbucket' | 'facebook' | 'github' | 'gitlab' | 'google' | 'linkedin' | 'linkedin-openid' | 'slack' | 'twitter' | 'twitter-oauth-2';

export interface Provider {
    id: ProviderId;
    name: string;
    buttonLabel?: string;
}

export interface Socialstream {
    show: boolean;
    prompt: string;
    hasPassword: boolean;
    providers: Provider[];
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
