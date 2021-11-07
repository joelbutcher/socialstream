<template>
    <app-layout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                 <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <update-profile-information-form :user="$page.props.user" />

                    <jet-section-border />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword && $page.props.socialstream.hasPassword">
                    <update-password-form class="mt-10 sm:mt-0" />

                    <jet-section-border />
                </div>

                <div v-else>
                    <set-password-form class="mt-10 sm:mt-0" />

                    <jet-section-border />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication && $page.props.socialstream.hasPassword">
                    <two-factor-authentication-form class="mt-10 sm:mt-0" />

                    <jet-section-border />
                </div>

                <div v-if="$page.props.socialstream.show">
                    <connected-accounts-form class="mt-10 sm:mt-0" />
                </div>

                <div v-if="$page.props.socialstream.hasPassword">
                    <jet-section-border />

                    <logout-other-browser-sessions-form  :sessions="sessions" class="mt-10 sm:mt-0" />
                </div>

                <div v-if="$page.props.jetstream.hasAccountDeletionFeatures && $page.props.socialstream.hasPassword">
                    <jet-section-border />

                    <delete-user-form class="mt-10 sm:mt-0" />
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script>
    import AppLayout from '@/Layouts/AppLayout'
    import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm'
    import JetSectionBorder from '@/Jetstream/SectionBorder'
    import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm'
    import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm'
    import SetPasswordForm from '@/Pages/Profile/Partials/SetPasswordForm'
    import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm'
    import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm'
    import ConnectedAccountsForm from '@/Pages/Profile/Partials/ConnectedAccountsForm';

    export default {
        props: ['sessions'],

        components: {
            ConnectedAccountsForm,
            AppLayout,
            DeleteUserForm,
            JetSectionBorder,
            LogoutOtherBrowserSessionsForm,
            TwoFactorAuthenticationForm,
            SetPasswordForm,
            UpdatePasswordForm,
            UpdateProfileInformationForm,
        },
    }
</script>
