import InputError from '@/Components/InputError.jsx';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.jsx';

export default function Socialstream({ providers, error }) {
    const providerName = (provider) => {
        switch (provider) {
            case 'twitter':
            case 'twitter-oauth-2':
                return 'Twitter';
            case 'linkedin':
            case 'linkedin-openid':
                return 'LinkedIn';
            default:
                return provider.charAt(0).toUpperCase() + provider?.slice(1);
        }
    };

    return (
        <div>
            <div className="flex flex-row items-center justify-between py-4 text-gray-600 dark:text-gray-400">
                <hr className="w-full mr-2" />
                Or
                <hr className="w-full ml-2" />
            </div>

            {error && <InputError message={error} className="mb-2 text-center" />}

            <div className="flex items-center justify-center gap-x-2">
                {providers.map((provider, i) => {
                    return (
                        <a href={route('oauth.redirect', provider)} key={provider}>
                            <ProviderIcon provider={provider} className="h-6 w-6" />

                            <span className="sr-only">{providerName(provider)}</span>
                        </a>
                    );
                })}
            </div>
        </div>
    );
}
