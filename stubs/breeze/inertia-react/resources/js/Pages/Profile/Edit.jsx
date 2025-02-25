import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ConnectedAccountsForm from './Partials/ConnectedAccountsForm';
import DeleteUserForm from './Partials/DeleteUserForm';
import SetPasswordForm from './Partials/SetPasswordForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import { Head } from '@inertiajs/react';

export default function Edit({ auth, mustVerifyEmail, status, socialstream }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Profile</h2>}
        >
            <Head title="Profile" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        {socialstream.hasPassword ? (
                            <UpdatePasswordForm className="max-w-xl" />
                        ) : (
                            <SetPasswordForm className="max-w-xl" />
                        )}
                    </div>

                    {socialstream.show && (
                        <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <ConnectedAccountsForm
                                providers={socialstream.providers}
                                connectedAccounts={socialstream.connectedAccounts}
                                hasPassword={socialstream.hasPassword}
                            />
                        </div>
                    )}

                    {socialstream.hasPassword && (
                        <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                            <DeleteUserForm className="max-w-xl" />
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
