import { SVGAttributes } from 'react';
import Bitbucket from './Bitbucket';
import Facebook from './Facebook';
import Github from './Github';
import Gitlab from './Gitlab';
import Google from './Google';
import Linkedin from './Linkedin';
import Slack from './Slack';
import Twitter from './Twitter';

export default function ProviderIcon({ className = '', provider, ...props }: SVGAttributes<SVGElement> & {
    provider: string
}) {
    const Icon = () => {
        switch(provider) {
            case 'bitbucket':
                return <Bitbucket {...props} className={className} />
            case 'facebook':
                return <Facebook {...props} className={className} />
            case 'github':
                return <Github {...props} className={className} />
            case 'gitlab':
                return <Gitlab {...props} className={className} />
            case 'google':
                return <Google {...props} className={className} />
            case 'linkedin':
            case 'linkedin-openid':
                return <Linkedin {...props} className={className} />
            case 'slack':
                return <Slack {...props} className={className} />
            case 'twitter':
            case 'twitter-oauth-2':
                return <Twitter {...props} className={className} />
        }
    };

    return (
        <div className="text-gray-900 dark:text-gray-100">
            <Icon />
        </div>
    )
}
