<script setup lang="ts">
import {defineProps} from 'vue';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.vue';
import {Provider} from '@/types';

const props = defineProps<{
    provider: Provider;
    createdAt?: string;
}>();
</script>

<template>
    <div>
        <div class="px-3 flex items-center justify-between">
            <div class="flex items-center">
                <ProviderIcon :provider="provider" classes="h-6 w-6 me-2"/>

                <div class="ms-2">
                    <div class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                        {{ provider.name }}
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
