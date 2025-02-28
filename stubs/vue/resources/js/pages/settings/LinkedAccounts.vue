<script setup lang="ts">
import type { BreadcrumbItem, User } from '@/types';
import type { Socialstream } from '@/types/socialstream';
import HeadingSmall from '@/components/HeadingSmall.vue';
import {Head} from '@inertiajs/vue3';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import LinkedAccount from '@/components/LinkedAccount.vue';
import SocialstreamIcon from '@/components/SocialstreamIcon.vue';
import {Tooltip, TooltipContent, TooltipProvider, TooltipTrigger} from '@/components/ui/tooltip';
import {Button} from '@/components/ui/button';

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
   (provider) => !auth.user.connected_accounts.some((account) => account.provider.id === provider.id),
);
</script>

<template>
   <AppLayout :breadcrumbs="breadcrumbs">
      <Head title="Linked Accounts"/>

      <SettingsLayout>
         <!-- Linked accounts -->
         <div class="space-y-6">
            <HeadingSmall title="Linked Accounts" description="View and remove your currently linked accounts."/>

            <div
               class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
               <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                  <p class="text-sm">
                     If you feel any of your connected accounts have been compromised, you should disconnect them
                     immediately and change your
                     password.
                  </p>
               </div>
            </div>

            <div v-if="status" class="mt-2 text-sm font-medium text-green-600">{{ status }}</div>

            <template v-for="account in auth.user.connected_accounts" v-bind:key="account.id">
               <LinkedAccount :account="account" :socialstream="socialstream"/>
            </template>
         </div>

         <div class="space-y-6">
            <HeadingSmall
               title="Link Account"
               description="Link your account to any of the following services to enable additional login options."
            />

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
               <template v-for="provider in availableAccounts" v-bind:key="provider.id">
                  <div class="flex flex-col p-4 border rounded-xl space-y-6 md:space-y-4">
                     <div class="flex w-full justify-center py-2">
                        <SocialstreamIcon :provider="provider.id" class="h-8 w-8 mx-auto md:mx-0"/>
                     </div>

                     <div class="flex w-full justify-end">
                        <TooltipProvider v-if="!socialstream.hasPassword">
                           <Tooltip :delay-duration="0">
                              <TooltipTrigger class="flex w-full">
                                 <Button variant="secondary" className="w-full" disabled>
                                    Link {{ provider.name }}
                                 </Button>
                              </TooltipTrigger>
                              <TooltipContent side="left">
                                 <p>Set a password to link new accounts.</p>
                              </TooltipContent>
                           </Tooltip>
                        </TooltipProvider>

                        <Button v-else asChild variant="secondary" class="w-full">
                           <a :href="route('oauth.redirect', { provider: provider.id })">
                              Link {{ provider.name }}
                           </a>
                        </Button>
                     </div>
                  </div>
               </template>
            </div>
         </div>
      </SettingsLayout>
   </AppLayout>
</template>
