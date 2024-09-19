<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import SetPasswordForm from './Partials/SetPasswordForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import {Head} from '@inertiajs/vue3';
import ConnectedAccountsForm from '@/Pages/Profile/Partials/ConnectedAccountsForm.vue';
import type {Socialstream} from '@/types';

defineProps<{
   mustVerifyEmail?: boolean;
   status?: string;
   socialstream: Socialstream
}>();
</script>

<template>
   <Head title="Profile"/>

   <AuthenticatedLayout>
      <template #header>
         <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>
      </template>

      <div class="py-12">
         <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
               <UpdateProfileInformationForm
                  :must-verify-email="mustVerifyEmail"
                  :status="status"
                  class="max-w-xl"
               />
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
               <UpdatePasswordForm v-if="socialstream.hasPassword" class="max-w-xl"/>
               <SetPasswordForm v-else class="max-w-xl"/>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
               <ConnectedAccountsForm
                  :connected-accounts="socialstream.connectedAccounts"
                  :has-password="socialstream.hasPassword"
                  :providers="socialstream.providers"
               />
            </div>

            <div v-if="socialstream.hasPassword" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
               <DeleteUserForm class="max-w-xl"/>
            </div>
         </div>
      </div>
   </AuthenticatedLayout>
</template>
