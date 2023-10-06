<script setup lang="ts">
import {computed, defineProps} from 'vue';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.vue';

const props = defineProps<{
    provider: string;
    createdAt?: string;
}>();

const providerName = computed(() => {
    switch (props.provider) {
        case 'twitter':
        case 'twitter-oauth-2':
            return 'Twitter';
        case 'linkedin':
        case 'linkedin-openid':
            return 'LinkedIn';
        default:
            return props.provider.charAt(0).toUpperCase() + props.provider?.slice(1);
    }
});
</script>

<template>
    <div>
        <div class="px-3 flex items-center justify-between">
            <div class="flex items-center">
                <ProviderIcon :provider="provider" classes="h-6 w-6 mr-2"/>

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
