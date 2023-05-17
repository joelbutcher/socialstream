<script setup>
import BitbucketIcon from '@/Components/SocialstreamIcons/BitbucketIcon.vue';
import FacebookIcon from '@/Components/SocialstreamIcons/FacebookIcon.vue';
import GithubIcon from '@/Components/SocialstreamIcons/GithubIcon.vue';
import GitLabIcon from '@/Components/SocialstreamIcons/GitLabIcon.vue';
import GoogleIcon from '@/Components/SocialstreamIcons/GoogleIcon.vue';
import LinkedInIcon from '@/Components/SocialstreamIcons/LinkedInIcon.vue';
import TwitterIcon from '@/Components/SocialstreamIcons/TwitterIcon.vue';

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
                <TwitterIcon class="h-6 w-6 mr-2" v-if="['twitter', 'twitter-oauth-2'].includes(provider)" />
                <LinkedInIcon class="h-6 w-6 mr-2" v-if="provider === 'linkedin'" />
                <GithubIcon class="h-6 w-6 mr-2" v-if="provider === 'github'" />
                <GitLabIcon class="h-6 w-6 mr-2" v-if="provider === 'gitlab'" />
                <BitbucketIcon class="h-6 w-6 mr-2" v-if="provider === 'bitbucket'" />

                <div>
                    <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
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
