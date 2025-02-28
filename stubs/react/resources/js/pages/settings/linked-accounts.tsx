import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import LinkedAccount from '@/components/linked-account';
import { type BreadcrumbItem, User } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Socialstream } from '@/types/socialstream';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { SocialstreamIcon } from '@/components/socialstream-icon';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Linked Accounts',
        href: '/settings/linked-accounts',
    },
];

interface LinkedAccountsProps {
    status?: string;
    auth: { user: User };
    socialstream: Socialstream;
}

export default function LinkedAccounts({ status, auth, socialstream }: LinkedAccountsProps) {
    const availableAccounts = socialstream.providers.filter(
        (provider) => !auth.user.connected_accounts.some((account) => account.provider.id === provider.id),
    );
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Linked Accounts" />

            <SettingsLayout>
                {/* Linked Accounts */}
                <div className="space-y-6">
                    <HeadingSmall title="Linked Accounts" description="View and remove your currently linked accounts" />

                    <Alert variant="destructive" className="border-destructive/10 bg-destructive/5">
                        <AlertDescription>
                            If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your
                            password.
                        </AlertDescription>
                    </Alert>

                    {status && <div className="mt-2 text-sm font-medium text-green-600">{status}</div>}

                    {auth.user.connected_accounts.map(account => (
                        <LinkedAccount key={account.id} account={account} socialstream={socialstream} />
                    ))}
                </div>

                {/* Unlinked accounts */}
                <div className="space-y-6">
                    <HeadingSmall title="Link Account" description="Link your account to any of the following services to enable additional login options" />

                    <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {availableAccounts.map(provider => (
                            <div key={provider.id} className="flex flex-col p-4 border rounded-xl space-y-6 md:space-y-4">
                                <div className="flex w-full justify-center py-2">
                                    <SocialstreamIcon provider={provider.id} className="h-8 w-8 mx-auto md:mx-0" />
                                </div>

                                <div className="flex w-full justify-end">
                                    {!socialstream.hasPassword ? (
                                        <Tooltip delayDuration={0}>
                                            <TooltipTrigger className="flex w-full">
                                                <Button variant="secondary" className="w-full" disabled={true}>
                                                    Link {provider.name}
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent side="left">
                                                <p>Set a password to link new accounts</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    ) : (
                                        <Button asChild variant="secondary" className="w-full">
                                            <a href={route('oauth.redirect', { provider: provider.id })}>
                                                Link {provider.name}
                                            </a>
                                        </Button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
