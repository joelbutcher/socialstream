import { PropsWithChildren } from 'react';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.jsx';
import { ConnectedAccount as ConnectedAccountType } from '@/types';

export default function ConnectedAccount({ children, provider, connectedAccount }: PropsWithChildren<{
    provider: string,
    connectedAccount?: ConnectedAccountType
}>) {
    const providerName = (): string => {
        switch(provider) {
            case 'twitter':
            case 'twitter-oauth-2':
                return 'Twitter'
            case 'linkedin':
            case 'linkedin-openid':
                return 'LinkedIn'
            default:
                return provider.charAt(0).toUpperCase() + provider?.slice(1);
        }
    }

    return (
        <div>
            <div className="px-3 flex items-center justify-between">
                <div className="flex items-center">
                    <ProviderIcon provider={provider} className="h-6 w-6"/>

                    <div className="ml-2">
                        <div className="text-sm font-semibold text-gray-600 dark:text-gray-400">
                            {providerName()}
                        </div>

                        {connectedAccount?.created_at ? (
                            <div className="text-xs text-gray-500">
                                Connected {connectedAccount.created_at}
                            </div>
                        ) : (
                            <div className="text-xs text-gray-500">
                                Not connected.
                            </div>
                        )}
                    </div>
                </div>

                {children}
            </div>
        </div>
    )
}
