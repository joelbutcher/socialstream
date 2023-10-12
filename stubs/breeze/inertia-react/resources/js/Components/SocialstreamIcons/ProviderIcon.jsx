import Bitbucket from '@/Components/SocialstreamIcons/Bitbucket.jsx';
import Facebook from '@/Components/SocialstreamIcons/Facebook.jsx';
import Github from '@/Components/SocialstreamIcons/Github.jsx';
import Gitlab from '@/Components/SocialstreamIcons/Gitlab.jsx';
import Google from '@/Components/SocialstreamIcons/Google.jsx';
import Linkedin from '@/Components/SocialstreamIcons/Linkedin.jsx';
import Slack from '@/Components/SocialstreamIcons/Slack.jsx';
import Twitter from '@/Components/SocialstreamIcons/Twitter.jsx';

export default function ProviderIcon({ className = '', provider, ...props }) {
    const Icon = () => {
        switch (provider.id) {
            case 'bitbucket':
                return <Bitbucket {...props} className={className} />;
            case 'facebook':
                return <Facebook {...props} className={className} />;
            case 'github':
                return <Github {...props} className={className} />;
            case 'gitlab':
                return <Gitlab {...props} className={className} />;
            case 'google':
                return <Google {...props} className={className} />;
            case 'linkedin':
            case 'linkedin-openid':
                return <Linkedin {...props} className={className} />;
            case 'slack':
                return <Slack {...props} className={className} />;
            case 'twitter':
            case 'twitter-oauth-2':
                return <Twitter {...props} className={className} />;
        }
    };

    return (
        <div className="text-gray-900 dark:text-gray-100">
            <Icon />
        </div>
    );
}
