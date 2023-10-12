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
    provider: {
        type: Object
    },
    classes: {
        type: String,
    }
});
</script>

<template>
    <div class="text-gray-900 dark:text-gray-100">
        <BitbucketIcon v-if="provider.id === 'bitbucket'" :class="classes"/>
        <FacebookIcon v-if="provider.id === 'facebook'" :class="classes"/>
        <GithubIcon v-if="provider.id === 'github'" :class="classes"/>
        <GitLabIcon v-if="provider.id === 'gitlab'" :class="classes"/>
        <GoogleIcon v-if="provider.id === 'google'" :class="classes"/>
        <LinkedInIcon v-if="provider.id === 'linkedin' || provider.id === 'linkedin-openid'" :class="classes"/>
        <SlackIcon v-if="provider.id === 'slack'" :class="classes"/>
        <TwitterIcon v-if="provider.id === 'twitter' || provider.id === 'twitter-oauth-2'" :class="classes"/>
    </div>
</template>
