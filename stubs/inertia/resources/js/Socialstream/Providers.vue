<template>
    <div>
        <div class="flex flex-row items-center justify-between py-4 text-gray-500">
            <hr class="w-full mr-2">
                {{ $t('Or Continue With') }}
            <hr class="w-full ml-2">
        </div>

        <div class="flex items-center justify-center">
            <a v-sh="hasProvider('facebook')" :href="route('oauth.redirect', 'facebook')">
                <facebook-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('google')" :href="route('oauth.redirect', 'google')" >
                <google-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('twitter')" :href="route('oauth.redirect', 'twitter')">
                <twitter-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('linkedin')" :href="route('oauth.redirect', 'linkedin')">
                <linked-in-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('github')" :href="route('oauth.redirect', 'github')">
                <github-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('gitlab')" :href="route('oauth.redirect', 'gitlab')">
                <git-lab-icon class="h-6 w-6 mx-2" />
            </a>

            <a v-sh="hasProvider('bitbucket')" :href="route('oauth.redirect', 'bitbucket')">
                <bitbucket-icon class="h-6 w-6 mx-2" />
            </a>
        </div>
    </div>
</template>

<script>
import FacebookIcon from '@/ProviderIcons/FacebookIcon';
import GoogleIcon from '@/ProviderIcons/GoogleIcon';
import TwitterIcon from '@/ProviderIcons/TwitterIcon';
import LinkedInIcon from '@/ProviderIcons/LinkedInIcon';
import GithubIcon from '@/ProviderIcons/GithubIcon';
import GitLabIcon from '@/ProviderIcons/GitLabIcon';
import BitbucketIcon from '@/ProviderIcons/BitbucketIcon';

    export default {
        components: {
            FacebookIcon,
            GoogleIcon,
            TwitterIcon,
            LinkedInIcon,
            GithubIcon,
            GitLabIcon,
            BitbucketIcon,
        },

        method: {
            hasProvider(provider) {
                return this.$page.socialstream.providers.includes(provider);
            }
        }
    }
</script>