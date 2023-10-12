<script setup>
import InputError from '@/Components/InputError.vue';
import ProviderIcon from '@/Components/SocialstreamIcons/ProviderIcon.vue';

defineProps({
    prompt: {
        type: String,
        default: 'Or Login Via',
    },
    error: {
        type: String,
        default: null,
    },
    providers: {
        type: Array,
    },
    labels: {
        type: Object,
    }
});
</script>

<template>
    <div v-if="providers.length" class="space-y-6 mt-6 mb-2">
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-400 dark:border-gray-500"></div>
            <span class="flex-shrink text-gray-400 dark:text-gray-500 px-6">
                {{ prompt }}
             </span>
            <div class="flex-grow border-t border-gray-400 dark:border-gray-500"></div>
        </div>

        <InputError v-if="error" :message="error" class="text-center"/>

        <div class="grid gap-4">
            <a v-for="provider in providers" :key="provider.id"
               class="flex gap-2 items-center justify-center transition duration-200 border border-gray-400 w-full py-2.5 rounded-lg text-sm shadow-sm hover:shadow-md font-normal text-center inline-block"
               :href="route('oauth.redirect', provider.id)">
                <ProviderIcon :provider="provider" classes="h-6 w-6 mx-2"/>
                <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ provider.buttonLabel || provider.name }}</span>
            </a>
        </div>
    </div>
</template>
