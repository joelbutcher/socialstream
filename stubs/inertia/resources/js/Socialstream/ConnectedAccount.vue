<template>
    <div>
        <div class="px-3 flex items-center justify-between">
            <div class="flex items-center">

                <facebook-icon class="h-6 w-6 mr-2" v-if="provider === 'facebook'"/>
                <google-icon class="h-6 w-6 mr-2" v-if="provider === 'google'"/>
                <twitter-icon class="h-6 w-6 mr-2" v-if="provider === 'twitter'"/>
                <linked-in-icon class="h-6 w-6 mr-2" v-if="provider === 'linkedin'"/>
                <github-icon class="h-6 w-6 mr-2" v-if="provider === 'github'"/>
                <git-lab-icon class="h-6 w-6 mr-2" v-if="provider === 'gitlab'"/>
                <bitbucket-icon class="h-6 w-6 mr-2" v-if="provider === 'bitbucket'"/>

                <div>
                    <div class="text-sm font-semibold text-gray-600">
                        {{ provider.charAt(0).toUpperCase() + provider.slice(1)  }}
                    </div>

                    <div v-if="createdAt !== null" class="text-xs text-gray-500">
                        Connected {{ createdAt }}
                    </div>

                    <div v-else class="text-xs text-gray-500">
                        Not connected.
                    </div>
                </div>
            </div>

            <slot name="action"></slot>
        </div>
    </div>
</template>

<script>
    import FacebookIcon from './ProviderIcons/FacebookIcon';
    import GoogleIcon from './ProviderIcons/GoogleIcon';
    import TwitterIcon from './ProviderIcons/TwitterIcon';
    import LinkedInIcon from './ProviderIcons/LinkedInIcon';
    import GithubIcon from './ProviderIcons/GithubIcon';
    import GitLabIcon from './ProviderIcons/GitLabIcon';
    import BitbucketIcon from './ProviderIcons/BitbucketIcon';

    export default {
        props: {
            provider: String,
            createdAt: {
                type: String,
                default: null,
            }
        },

        components: {
            FacebookIcon,
            GoogleIcon,
            TwitterIcon,
            LinkedInIcon,
            GithubIcon,
            GitLabIcon,
            BitbucketIcon
        }
    }
</script>
