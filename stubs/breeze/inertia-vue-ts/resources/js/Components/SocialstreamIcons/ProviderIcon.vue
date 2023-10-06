<script setup lang="ts">
import BitbucketIcon from '@/Components/SocialstreamIcons/BitbucketIcon.vue';
import FacebookIcon from '@/Components/SocialstreamIcons/FacebookIcon.vue';
import GithubIcon from '@/Components/SocialstreamIcons/GithubIcon.vue';
import GitLabIcon from '@/Components/SocialstreamIcons/GitLabIcon.vue';
import GoogleIcon from '@/Components/SocialstreamIcons/GoogleIcon.vue';
import LinkedInIcon from '@/Components/SocialstreamIcons/LinkedInIcon.vue';
import SlackIcon from '@/Components/SocialstreamIcons/SlackIcon.vue';
import TwitterIcon from '@/Components/SocialstreamIcons/TwitterIcon.vue';

defineProps<{
    provider: string;
    classes: string;
}>();
</script>

<template>
    <div class="text-gray-900 dark:text-gray-100">
        <BitbucketIcon v-if="provider === 'bitbucket'" :class="classes"/>
        <FacebookIcon v-if="provider === 'facebook'" :class="classes"/>
        <GithubIcon v-if="provider === 'github'" :class="classes"/>
        <GitLabIcon v-if="provider === 'gitlab'" :class="classes"/>
        <GoogleIcon v-if="provider === 'google'" :class="classes"/>
        <LinkedInIcon v-if="provider === 'linkedin' || provider === 'linkedin-openid'" :class="classes"/>
        <SlackIcon v-if="provider === 'slack'" :class="classes"/>
        <TwitterIcon v-if="provider === 'twitter' || provider === 'twitter-oauth-2'" :class="classes"/>
    </div>
</template>
