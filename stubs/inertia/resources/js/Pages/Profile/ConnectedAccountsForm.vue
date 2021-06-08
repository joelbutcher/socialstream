<template>
    <jet-action-section>
        <template #title>
            Connected Accounts
        </template>

        <template #description>
            Manage and remove your connected accounts.
        </template>

        <template #content>
            <h3 class="text-lg font-medium text-gray-900" v-if="$page.props.socialstream.connectedAccounts.length === 0">
                You have no connected accounts.
            </h3>
            <h3 class="text-lg font-medium text-gray-900" v-else>
                Your connected accounts.
            </h3>

            <div class="mt-3 ax-w-xl text-sm text-gray-600">
                You are free to connect any social accounts to your profile and may remove any connected accounts at any time. If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.
            </div>

            <div class="mt-5 space-y-6">
                <div v-for="(provider) in $page.props.socialstream.providers" :key="provider">
                    <connected-account :provider="provider" :created-at="hasAccountForProvider(provider) ? getAccountForProvider(provider).created_at : null">
                        <template #action>
                            <template v-if="hasAccountForProvider(provider)">
                                <div class="flex items-center space-x-6">
                                    <button
                                        v-if="$page.props.jetstream.managesProfilePhotos && getAccountForProvider(provider).avatar_path"
                                        @click="setProfilePhoto(getAccountForProvider(provider).id)"
                                        class="cursor-pointer ml-6 text-sm text-gray-500 focus:outline-none">
                                        Use Avatar as Profile Photo
                                    </button>

                                    <jet-danger-button @click="confirmRemove(getAccountForProvider(provider).id)" v-if="$page.props.socialstream.connectedAccounts.length > 1 || $page.props.socialstream.hasPassword">
                                        Remove
                                    </jet-danger-button>
                                </div>
                            </template>

                            <template v-else>
                                <action-link :href="route('oauth.redirect', { provider })">
                                    Connect
                                </action-link>
                            </template>
                        </template>
                    </connected-account>
                </div>
            </div>

            <!-- Confirmation Modal -->
            <jet-dialog-modal :show="confirmingRemove" @close="confirmingRemove = false">
                <template #title>
                    Remove Connected Account
                </template>

                <template #content>
                    Please confirm your removal of this account - this action cannot be undone.
                </template>

                <template #footer>
                    <jet-secondary-button @click="confirmingRemove = false">
                        Nevermind
                    </jet-secondary-button>

                    <jet-button class="ml-2"
                                @click="removeConnectedAccount(accountId)"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Remove Connected Account
                    </jet-button>
                </template>
            </jet-dialog-modal>
        </template>
    </jet-action-section>
</template>

<script>
    import JetActionMessage from '@/Jetstream/ActionMessage';
    import JetActionSection from '@/Jetstream/ActionSection';
    import JetButton from '@/Jetstream/Button';
    import JetDangerButton from '@/Jetstream/DangerButton';
    import JetDialogModal from '@/Jetstream/DialogModal';
    import JetInput from '@/Jetstream/Input';
    import JetInputError from '@/Jetstream/InputError';
    import JetSecondaryButton from '@/Jetstream/SecondaryButton';
    import ConnectedAccount from '@/Socialstream/ConnectedAccount';
    import ActionLink from '@/Socialstream/ActionLink';

    export default {
        components: {
            JetActionMessage,
            JetActionSection,
            JetButton,
            JetDangerButton,
            JetDialogModal,
            JetInput,
            JetInputError,
            JetSecondaryButton,
            ConnectedAccount,
            ActionLink,
        },

        data() {
            return {
                confirmingRemove: false,
                accountId: null,

                form: this.$inertia.form({
                    '_method': 'DELETE',
                }, {
                    bag: 'removeConnectedAccount'
                })
            };
        },

        methods: {
            confirmRemove(id) {
                this.form.password = '';

                this.accountId = id;

                this.confirmingRemove = true;
            },

            hasAccountForProvider(provider) {
                return this.$page.props.socialstream.connectedAccounts.filter(account => account.provider === provider).length > 0;
            },

            getAccountForProvider(provider) {
                if (this.hasAccountForProvider(provider)) {
                    return this.$page.props.socialstream.connectedAccounts.filter(account => account.provider === provider).shift();
                }

                return null;
            },

            setProfilePhoto(id) {
                this.form.put(route('user-profile-photo.set', {id}), {
                    preserveScroll: true,
                });
            },

            removeConnectedAccount(id) {
                this.form.post(route('connected-accounts.destroy', {id}), {
                    preserveScroll: true,
                    onSuccess: () => (this.confirmingRemove = false),
                });
            },
        }
    }
</script>
