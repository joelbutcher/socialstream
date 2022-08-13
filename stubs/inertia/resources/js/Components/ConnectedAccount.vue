<script setup>
import FacebookIcon from '@/Components/SocialstreamProviderIcons/FacebookIcon.vue';
import GoogleIcon from '@/Components/SocialstreamProviderIcons/GoogleIcon.vue';
import TwitterIcon from '@/Components/SocialstreamProviderIcons/TwitterIcon.vue';
import LinkedInIcon from '@/Components/SocialstreamProviderIcons/LinkedInIcon.vue';
import GithubIcon from '@/Components/SocialstreamProviderIcons/GithubIcon.vue';
import GitLabIcon from '@/Components/SocialstreamProviderIcons/GitLabIcon.vue';
import BitbucketIcon from '@/Components/SocialstreamProviderIcons/BitbucketIcon.vue';

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
                <TwitterIcon class="h-6 w-6 mr-2" v-if="provider === 'twitter' || 'twitter-oauth-2'" />
                <LinkedInIcon class="h-6 w-6 mr-2" v-if="provider === 'linkedin'" />
                <GithubIcon class="h-6 w-6 mr-2" v-if="provider === 'github'" />
                <GitLabIcon class="h-6 w-6 mr-2" v-if="provider === 'gitlab'" />
                <BitbucketIcon class="h-6 w-6 mr-2" v-if="provider === 'bitbucket'" />

                <div>
                    <div class="text-sm font-semibold text-gray-600">
                        {{ provider === 'twitter-oauth-2' ? 'Twitter' : provider.charAt(0).toUpperCase() + provider.slice(1) }}
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
