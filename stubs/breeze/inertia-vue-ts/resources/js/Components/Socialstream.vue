<script setup lang="ts">
import InputError from '@/Components/InputError.vue';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.vue';

defineProps<{
    error?: string;
    providers: string[];
}>();

const providerName = (provider: string) => {
    switch (provider) {
        case 'twitter':
        case 'twitter-oauth-2':
            return 'Twitter';
        case 'linkedin':
        case 'linkedin-openid':
            return 'LinkedIn';
        default:
            return provider.charAt(0).toUpperCase() + provider?.slice(1);
    }
};
</script>

<template>
    <div v-if="$page.props.socialstream.providers.length">
        <div class="flex flex-row items-center justify-between py-4 text-gray-600 dark:text-gray-400">
            <hr class="w-full mr-2">
            Or
            <hr class="w-full ml-2">
        </div>

        <InputError v-if="error" :message="error" class="mb-2 text-center"/>

        <div class="flex items-center justify-center gap-x-2">
            <a v-for="provider in providers" :key="provider" :href="route('oauth.redirect', provider)">
                <ProviderIcon :provider="provider" classes="h-6 w-6 mx-2"/>
                <span class="sr-only">{{ providerName(provider) }}</span>
            </a>
        </div>
    </div>
</template>
