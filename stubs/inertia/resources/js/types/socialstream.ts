export declare type ProviderId = 'bitbucket' | 'facebook' | 'github' | 'gitlab' | 'google' | 'linkedin' | 'linkedin-openid' | 'slack' | 'slack-openid' | 'twitch' | 'twitter' | 'twitter-oauth-2' | 'x';

export interface Provider {
    id: ProviderId;
    name: string;
    buttonLabel?: string;
}

export interface Socialstream {
    show: boolean;
    divideText: string;
    hasPassword: boolean;
    providers: Provider[];
    error?: string
}

export interface ConnectedAccount {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    nickname: string
    provider: Provider;
    provider_id: number;
    [key: string]: unknown; // This allows for additional properties...
}
