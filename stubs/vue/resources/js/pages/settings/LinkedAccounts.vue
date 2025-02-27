<script setup lang="ts">
import type { BreadcrumbItem } from '@/types';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Head } from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import LinkedAccount from '@/components/LinkedAccount.vue';
import SocialstreamIcon from '@/components/SocialstreamIcon.vue';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Separator } from '@/components/ui/separator';
import { Button } from '@/components/ui/button';

interface Props {
    status?: string;
    auth: { user: User };
    socialstream: Socialstream;
}

const {
    status,
    auth,
    socialstream,
} = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const availableAccounts = socialstream.providers.filter(
    (provider) => !auth.user.connected_accounts.some((account) => account.provider === provider.id),
);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Linked Accounts" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Linked Accounts" description="View and remove your currently linked accounts." />

                <div class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                    <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                        <p class="text-sm">
                            If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your
                            password.
                        </p>
                    </div>
                </div>

                <div v-if="status" class="mt-2 text-sm font-medium text-green-600">{{ status }}</div>

                <template v-for="account in auth.user.connected_accounts" v-bind:key="account.id">
                    <LinkedAccount :account="account" :socialstream="socialstream" />
                </template>

                <div class="space-y-6">
                    <HeadingSmall
                        title="Link Account"
                        description="Link your account to any of the following services to enable additional login options."
                    />
                </div>

                <div>
                    <template v-for="(provider, index) in availableAccounts" v-bind:key="provider.id">
                        <div class="grid items-center px-3 py-6 md:grid-cols-2">
                            <div class="flex items-center space-x-4">
                                <SocialstreamIcon :provider="provider.id" class="h-6 w-6" />

                                <p class="font-medium">{{ provider.name }}</p>
                            </div>

                            <div class="justify-self-end">
                                <TooltipProvider v-if="!socialstream.hasPassword">
                                    <Tooltip delayDuration="{0}">
                                        <TooltipTrigger>
                                            <Button class="justify-self-end" :disabled="true"> Link </Button>
                                        </TooltipTrigger>
                                        <TooltipContent side="left">
                                            <p>You must set a password to link new accounts.</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>

                                <Button v-else>
                                    <a
                                        :href="route('oauth.redirect', { provider: provider.id })"
                                        class="h-full w-full"
                                    >
                                        Link
                                    </a>
                                </Button>
                            </div>
                        </div>

                        <Separator v-if="index !== availableAccounts.length - 1" />
                    </template>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
