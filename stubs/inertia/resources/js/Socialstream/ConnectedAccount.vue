<script setup>
import FacebookIcon from './ProviderIcons/FacebookIcon';
import GoogleIcon from './ProviderIcons/GoogleIcon';
import TwitterIcon from './ProviderIcons/TwitterIcon';
import LinkedInIcon from './ProviderIcons/LinkedInIcon';
import GithubIcon from './ProviderIcons/GithubIcon';
import GitLabIcon from './ProviderIcons/GitLabIcon';
import BitbucketIcon from './ProviderIcons/BitbucketIcon';

defineProps({
    provider: String,
    createdAt: {
        type: String,
        default: null,
    }
});
</script>

<template>
    <div>
        <div class="px-3 flex items-center justify-between">
            <div class="flex items-center">

                <FacebookIcon class="h-6 w-6 mr-2" v-if="provider === 'facebook'" />
                <GoogleIcon class="h-6 w-6 mr-2" v-if="provider === 'google'" />
                <TwitterIcon class="h-6 w-6 mr-2" v-if="provider === 'twitter'" />
                <LinkedInIcon class="h-6 w-6 mr-2" v-if="provider === 'linkedin'" />
                <GithubIcon class="h-6 w-6 mr-2" v-if="provider === 'github'" />
                <GitLabIcon class="h-6 w-6 mr-2" v-if="provider === 'gitlab'" />
                <BitbucketIcon class="h-6 w-6 mr-2" v-if="provider === 'bitbucket'" />

                <div>
                    <div class="text-sm font-semibold text-gray-600">
                        {{ provider.charAt(0).toUpperCase() + provider.slice(1) }}
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