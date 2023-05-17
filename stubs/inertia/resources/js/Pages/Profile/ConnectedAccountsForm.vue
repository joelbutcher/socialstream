<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'
import ActionLink from '@/Components/ActionLink.vue';
import ActionSection from '@/Components/ActionSection.vue';
import ConnectedAccount from '@/Components/ConnectedAccount.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const confirmingRemove = ref(false);

const accountId = ref(null);

const inertiaProps = computed(() => usePage().props);

const form = useForm({
    _method: 'DELETE',
    bag: 'removeConnectedAccount'
});

function confirmRemove(id) {
    form.transform((data) => ({
        ...data,
        password: ''
    }));
    accountId.value = id;
    confirmingRemove.value = true;
}

function hasAccountForProvider(provider) {
    return (
        inertiaProps.value.socialstream.connectedAccounts.filter(
            account => account.provider === provider
        ).length > 0
    );
}

function getAccountForProvider(provider) {
    if (hasAccountForProvider(provider)) {
        const tes = inertiaProps.value.socialstream.connectedAccounts
            .filter(account => account.provider === provider)
            .shift();
        return tes;
    };
    return undefined;
}

function setProfilePhoto(id) {
    form.put(route('user-profile-photo.set', { id }), {
        preserveScroll: true
    });
}

function removeConnectedAccount(id) {
    form.post(route('connected-accounts.destroy', { id }), {
        preserveScroll: true,
        onSuccess: () => (confirmingRemove.value = false)
    });
}

</script>

<template>
    <ActionSection>
        <template #title>
            Connected Accounts
        </template>

        <template #description>
            Manage and remove your connected accounts.
        </template>

        <template #content>
            <h3 class="text-lg font-medium text-gray-900"
                v-if="$page.props.socialstream.connectedAccounts.length === 0">
                You have no connected accounts.
            </h3>
            <h3 class="text-lg font-medium text-gray-900" v-else>
                Your connected accounts.
            </h3>

            <div class="mt-3 ax-w-xl text-sm text-gray-600 dark:text-gray-400">
                You are free to connect any social accounts to your profile and may remove any connected accounts at any
                time. If you feel any of your connected accounts have been compromised, you should disconnect them
                immediately and change your password.
            </div>

            <div class="mt-5 space-y-6">
                <div v-for="(provider) in $page.props.socialstream.providers" :key="provider">
                    <ConnectedAccount :provider="provider"
                        :created-at="hasAccountForProvider(provider) ? getAccountForProvider(provider).created_at : null">
                        <template #action>
                            <template v-if="hasAccountForProvider(provider)">
                                <div class="flex items-center space-x-6">
                                    <button
                                        v-if="$page.props.jetstream.managesProfilePhotos && getAccountForProvider(provider).avatar_path"
                                        @click="setProfilePhoto(getAccountForProvider(provider).id)"
                                        class="cursor-pointer ml-6 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                        Use Avatar as Profile Photo
                                    </button>

                                    <DangerButton @click="confirmRemove(getAccountForProvider(provider).id)"
                                        v-if="$page.props.socialstream.connectedAccounts.length > 1 || $page.props.socialstream.hasPassword">
                                        Remove
                                    </DangerButton>
                                </div>
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

            <!-- Confirmation Modal -->
            <DialogModal :show="confirmingRemove" @close="confirmingRemove = false">
                <template #title>
                    Remove Connected Account
                </template>

                <template #content>
                    Please confirm your removal of this account - this action cannot be undone.
                </template>

                <template #footer>
                    <SecondaryButton @click="confirmingRemove = false">
                        Nevermind
                    </SecondaryButton>

                    <PrimaryButton class="ml-2" @click="removeConnectedAccount(accountId)"
                        :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Remove Connected Account
                    </PrimaryButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
