<script setup lang="ts">
import {nextTick, ref} from 'vue';
import ActionLink from '@/Components/ActionLink.vue';
import ConnectedAccount from '@/Components/ConnectedAccount.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import {useForm} from '@inertiajs/vue3';
import {ConnectedAccount as ConnectedAccountType, Provider} from '@/types';

const providerToRemove = ref<Provider>();
const passwordInput = ref<HTMLInputElement | null>(null);
const confirmingRemoveAccount = ref<boolean>(false);

const props = defineProps<{
    hasPassword: boolean;
    connectedAccounts: ConnectedAccountType[];
    providers: Provider[];
}>();

const form = useForm({
    password: '',
});

const hasAccountForProvider = (provider: Provider): boolean => props.connectedAccounts
    .filter(account => account.provider === provider.id)
    .shift() !== undefined;

const getAccountForProvider = (provider: Provider): ConnectedAccountType => props.connectedAccounts
    .filter(account => account.provider === provider.id)
    .shift() as ConnectedAccountType;

const confirmAccountRemoval = (provider: Provider) => {
    providerToRemove.value = provider;
    confirmingRemoveAccount.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const removeAccount = () => {
    if (! providerToRemove.value) {
        return;
    }

    const id = getAccountForProvider(providerToRemove.value).id;

    form.delete(route('connected-accounts.destroy', { id }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    confirmingRemoveAccount.value = false;

    form.reset();
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Connected Accounts</h2>

            <p class="max-w-xl mt-1 text-sm text-gray-600 dark:text-gray-400">
               Connect your social media accounts to enable Sign In with OAuth.
            </p>
        </header>

       <div class="p-4 bg-red-500/10 dark:bg-red-500/5 text-red-500 border-l-4 border-red-600 dark:border-red-700 rounded font-medium text-sm">
          If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.
       </div>

        <div class="space-y-6 mt-6">
            <div v-for="provider in providers" :key="provider.id">
                <ConnectedAccount :created-at="hasAccountForProvider(provider) ? getAccountForProvider(provider)?.created_at : ''" :provider="provider">
                    <template #action>
                        <template v-if="hasAccountForProvider(provider)">
                            <DangerButton v-if="connectedAccounts.length > 1 || hasPassword"
                                          @click="confirmAccountRemoval(provider)">
                                Remove
                            </DangerButton>
                        </template>

                        <template v-else>
                            <ActionLink :href="route('oauth.redirect', { provider })">
                                Connect
                            </ActionLink>
                        </template>
                    </template>
                </ConnectedAccount>
            </div>
        </div>
    </section>

    <Modal :show="confirmingRemoveAccount" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Are you sure you want to remove this account?
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Please enter your password to confirm you would like to remove this account.
            </p>

            <div class="mt-6">
                <InputLabel class="sr-only" for="password" value="Password"/>

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    class="mt-1 block w-3/4"
                    placeholder="Password"
                    type="password"
                    @keyup.enter="removeAccount"
                />

                <InputError :message="form.errors.password" class="mt-2"/>
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal"> Cancel</SecondaryButton>

                <DangerButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    class="ms-3"
                    @click="removeAccount"
                >
                    Remove Account
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>
