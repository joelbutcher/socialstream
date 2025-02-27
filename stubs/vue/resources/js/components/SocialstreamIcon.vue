<script setup lang="ts">
import BitbucketIcon from '@/components/SocialstreamIcons/BitbucketIcon.vue';
import FacebookIcon from '@/components/SocialstreamIcons/FacebookIcon.vue';
import GitHubIcon from '@/components/SocialstreamIcons/GitHubIcon.vue';
import GitlabIcon from '@/components/SocialstreamIcons/GitlabIcon.vue';
import GoogleIcon from '@/components/SocialstreamIcons/GoogleIcon.vue';
import LinkedInIcon from '@/components/SocialstreamIcons/LinkedInIcon.vue';
import SlackIcon from '@/components/SocialstreamIcons/SlackIcon.vue';
import TwitchIcon from '@/components/SocialstreamIcons/TwitchIcon.vue';
import TwitterIcon from '@/components/SocialstreamIcons/TwitterIcon.vue';
import XIcon from '@/components/SocialstreamIcons/XIcon.vue';
import {type ProviderId} from '@/types/socialstream';

defineProps<{
    provider: ProviderId;
}>();
</script>

<template>
   <BitbucketIcon v-bind="$attrs" v-if="provider === 'bitbucket'"/>
   <FacebookIcon v-bind="$attrs" v-if="provider === 'facebook'"/>
   <GitHubIcon v-bind="$attrs" v-if="provider === 'github'"/>
   <GitlabIcon v-bind="$attrs" v-if="provider === 'gitlab'"/>
   <GoogleIcon v-bind="$attrs" v-if="provider === 'google'"/>
   <LinkedInIcon v-bind="$attrs" v-if="provider === 'linkedin' || provider === 'linkedin-openid'"/>
   <SlackIcon v-bind="$attrs" v-if="provider === 'slack' || provider === 'slack-openid'"/>
   <TwitchIcon v-bind="$attrs" v-if="provider === 'twitch'"/>
   <TwitterIcon v-bind="$attrs" v-if="provider === 'twitter' || provider === 'twitter-oauth-2'"/>
   <XIcon v-bind="$attrs" v-if="provider === 'x'"/>
</template>
