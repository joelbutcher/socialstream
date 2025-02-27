import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import LinkedAccount from '@/components/linked-account';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Socialstream } from '@/types/socialstream';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { User } from '@/types';
import { Button } from '@/components/ui/button';
import { SocialstreamIcon } from '@/components/socialstream-icon';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Linked Accounts',
    href: '/settings/linked-accounts',
  },
];

interface LinkedAccountsProps {
  status?: string
  auth: { user: User },
  socialstream: Socialstream,
}

export default function LinkedAccounts({ status, auth, socialstream }: LinkedAccountsProps) {
  const availableAccounts = socialstream.providers.filter(provider => !auth.user.connected_accounts.some(account => account.provider === provider.id));
  return (
    <AppLayout breadcrumbs={breadcrumbs}>
      <Head title="Linked Accounts" />

      <SettingsLayout>
        {/* Linked Accounts */}
        <div className="flex flex-col space-y-6">
          <HeadingSmall title="Linked Accounts" description="View and remove your currently linked accounts." />

          <Alert variant="destructive" className="border-destructive/10 bg-destructive/5">
            <AlertDescription>
              If you feel any of your connected accounts have been compromised, you should disconnect them
              immediately and change your password.
            </AlertDescription>
          </Alert>

          {status && <div className="mt-2 text-sm font-medium text-green-600">{status}</div>}

          {auth.user.connected_accounts.map((account, index) => (
            <div key={account.id}>
              <LinkedAccount
                account={account}
                socialstream={socialstream}
              />

              {(index !== auth.user.connected_accounts.length -1) && <Separator />}
            </div>
          ))}
        </div>

        {/* Unlinked accounts */}
        <div className="space-y-6">
          <HeadingSmall title="Link Account" description="Link your account to any of the following services to enable additional login options." />
        </div>

        <div>
          {availableAccounts.map((provider, index) => (
            <div
              key={provider.id}
            >
              <div className="grid items-center px-3 py-6 md:grid-cols-2">
                <div className="flex items-center space-x-4">
                  <SocialstreamIcon provider={provider.id} className="h-6 w-6" />

                  <p className="font-medium">{provider.name}</p>
                </div>

                <div className="justify-self-end">
                  {!socialstream.hasPassword ? (
                      <Tooltip delayDuration={0}>
                        <TooltipTrigger>
                          <Button className="justify-self-end" disabled={true}>
                            Link
                          </Button>
                        </TooltipTrigger>
                        <TooltipContent side="left">
                          {!socialstream.hasPassword && (
                              <p>You must set a password to link new accounts.</p>
                          )}
                        </TooltipContent>
                      </Tooltip>
                  ) : (
                      <Button>
                        <a href={route('oauth.redirect', { provider: provider.id })}>Link</a>
                      </Button>
                  )}
                </div>
              </div>

              {(index !== availableAccounts.length -1) && <Separator />}
            </div>
          ))}
        </div>
      </SettingsLayout>
    </AppLayout>
  );
}
