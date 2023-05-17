<script setup>
import {computed} from 'vue';
import {usePage} from '@inertiajs/vue3';
import BitbucketIcon from '@/Components/SocialstreamIcons/BitbucketIcon.vue';
import FacebookIcon from '@/Components/SocialstreamIcons/FacebookIcon.vue';
import GithubIcon from '@/Components/SocialstreamIcons/GithubIcon.vue';
import GitLabIcon from '@/Components/SocialstreamIcons/GitLabIcon.vue';
import GoogleIcon from '@/Components/SocialstreamIcons/GoogleIcon.vue';
import InputError from '@/Components/InputError.vue';
import LinkedInIcon from '@/Components/SocialstreamIcons/LinkedInIcon.vue';
import TwitterIcon from '@/Components/SocialstreamIcons/TwitterIcon.vue';

const error = computed(() => usePage().props.errors.socialstream);
</script>

<template>
  <div>
    <div class="flex flex-row items-center justify-between py-4 text-gray-600 dark:text-gray-400">
      <hr class="w-full mr-2">
      Or
      <hr class="w-full ml-2">
    </div>

    <div class="flex items-center justify-center">
      <a v-if="$page.props.socialstream.providers.includes('facebook')" :href="route('oauth.redirect', 'facebook')">
        <FacebookIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">Facebook</span>
      </a>

      <a v-if="$page.props.socialstream.providers.includes('google')" :href="route('oauth.redirect', 'google')">
        <GoogleIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">Google</span>
      </a>

      <a
          v-if="$page.props.socialstream.providers.includes('twitter')" :href="route('oauth.redirect', 'twitter')">
        <TwitterIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">Twitter</span>
      </a>

      <a
          v-if="$page.props.socialstream.providers.includes('twitter-oauth-2')"
          :href="route('oauth.redirect', 'twitter-oauth-2')">
        <TwitterIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">Twitter</span>
      </a>

      <a v-if="$page.props.socialstream.providers.includes('linkedin')" :href="route('oauth.redirect', 'linkedin')">
        <LinkedInIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">LinkedIn</span>
      </a>

      <a v-if="$page.props.socialstream.providers.includes('github')" :href="route('oauth.redirect', 'github')">
        <GithubIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">GitHub</span>
      </a>

      <a v-if="$page.props.socialstream.providers.includes('gitlab')" :href="route('oauth.redirect', 'gitlab')">
        <GitLabIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">GitLab</span>
      </a>

      <a v-if="$page.props.socialstream.providers.includes('bitbucket')" :href="route('oauth.redirect', 'bitbucket')">
        <BitbucketIcon class="h-6 w-6 mx-2"/>
        <span class="sr-only">BitBucket</span>
      </a>
    </div>

    <InputError class="mt-b text-center" :message="error" />
  </div>
</template>
