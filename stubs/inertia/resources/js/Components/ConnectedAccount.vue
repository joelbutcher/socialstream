<script setup>
import BitbucketIcon from '@/Components/SocialstreamIcons/BitbucketIcon.vue';
import FacebookIcon from '@/Components/SocialstreamIcons/FacebookIcon.vue';
import GithubIcon from '@/Components/SocialstreamIcons/GithubIcon.vue';
import GitLabIcon from '@/Components/SocialstreamIcons/GitLabIcon.vue';
import GoogleIcon from '@/Components/SocialstreamIcons/GoogleIcon.vue';
import LinkedInIcon from '@/Components/SocialstreamIcons/LinkedInIcon.vue';
import SlackIcon from '@/Components/SocialstreamIcons/SlackIcon.vue';
import TwitterIcon from '@/Components/SocialstreamIcons/TwitterIcon.vue';

defineProps({
    provider: String,
    createdAt: {
        type: String,
        default: null,
    }
});

const providerName =  computed(() => {
    switch(this.provider) {
        case 'twitter':
        case 'twitter-oauth-2':
            return 'Twitter'
        case 'linkedin':
        case 'linkedin-openid':
            return 'LinkedIn'
        default:
            return name.charAt(0).toUpperCase() + provider.slice(1);
    };
});
</script>

<template>
    <div>
        <div class="px-3 flex items-center justify-between">
            <div class="flex items-center">

                <BitbucketIcon class="h-6 w-6 mr-2" v-if="provider === 'bitbucket'" />
                <FacebookIcon class="h-6 w-6 mr-2" v-if="provider === 'facebook'" />
                <GithubIcon class="h-6 w-6 mr-2" v-if="provider === 'github'" />
                <GitLabIcon class="h-6 w-6 mr-2" v-if="provider === 'gitlab'" />
                <GoogleIcon class="h-6 w-6 mr-2" v-if="provider === 'google'" />
                <LinkedInIcon class="h-6 w-6 mr-2" v-if="['linkedin', 'linkedin-openid'].includes(provider)" />
                <SlackIcon class="h-6 w-6 mr-2" v-if="provider === 'slack'" />
                <TwitterIcon class="h-6 w-6 mr-2" v-if="['twitter', 'twitter-oauth-2'].includes(provider)" />

                <div>
                    <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                        {{ providerName }}
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
