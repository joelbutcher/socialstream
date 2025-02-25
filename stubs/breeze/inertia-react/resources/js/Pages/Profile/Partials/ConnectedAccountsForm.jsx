import React, { useRef, useState } from 'react';
import DangerButton from '@/Components/DangerButton';
import InputError from '@/Components/InputError.jsx';
import Modal from '@/Components/Modal';
import ConnectedAccount from '@/Components/ConnectedAccount.jsx';
import InputLabel from '@/Components/InputLabel.jsx';
import TextInput from '@/Components/TextInput.jsx';
import SecondaryButton from '@/Components/SecondaryButton.jsx';
import { useForm } from '@inertiajs/react';
import ActionLink from '@/Components/ActionLink.jsx';

export default function ConnectedAccountsForm({ className = '', hasPassword, providers, connectedAccounts }) {
    const [confirmingAccountDeletion, setConfirmingAccountDeletion] = useState(false);
    const passwordInput = useRef();

    const {
        data,
        setData,
        delete: destroy,
        processing,
        reset,
        errors,
    } = useForm({
        password: '',
    });

    const confirmAccountDeletion = (e) => {
        e.preventDefault();

        setConfirmingAccountDeletion(true);
    };

    const deleteAccount = (e, connectedAccount) => {
        e.preventDefault();

        destroy(route('connected-accounts.destroy', connectedAccount.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        setConfirmingAccountDeletion(false);

        reset();
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">Connected Accounts</h2>

                <p className="max-w-xl mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Connect your social media accounts to enable Sign In with OAuth.
                </p>
            </header>

            <div className="p-4 bg-red-500/10 dark:bg-red-500/5 text-red-500 border-l-4 border-red-600 dark:border-red-700 rounded font-medium text-sm">
                If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.
            </div>

            <div className="space-y-6 mt-6">
                {providers.map((provider) => {
                    const connectedAccount = connectedAccounts
                        .filter((account) => account.provider === provider.id)
                        .shift();

                    return (
                        <React.Fragment key={provider.id}>
                            <ConnectedAccount provider={provider} connectedAccount={connectedAccount}>
                                {connectedAccount ? (
                                    connectedAccounts.length > 1 ||
                                    (hasPassword && (
                                        <DangerButton onClick={confirmAccountDeletion}>Remove</DangerButton>
                                    ))
                                ) : (
                                    <ActionLink href={route('oauth.redirect', { provider })}>Connect</ActionLink>
                                )}
                            </ConnectedAccount>

                            {connectedAccount && (
                                <Modal show={confirmingAccountDeletion} onClose={closeModal}>
                                    <form onSubmit={(e) => deleteAccount(e, connectedAccount)} className="p-6">
                                        <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">
                                            Are you sure you want to remove this account?
                                        </h2>

                                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            Please enter your password to confirm you would like to remove this account.
                                        </p>

                                        <div className="mt-6">
                                            <InputLabel htmlFor="password" value="Password" className="sr-only" />

                                            <TextInput
                                                id="password"
                                                type="password"
                                                name="password"
                                                ref={passwordInput}
                                                value={data.password}
                                                onChange={(e) => setData('password', e.target.value)}
                                                className="mt-1 block w-3/4"
                                                isFocused
                                                placeholder="Password"
                                            />

                                            <InputError message={errors.password} className="mt-2" />
                                        </div>

                                        <div className="mt-6 flex justify-end">
                                            <SecondaryButton onClick={closeModal}>Cancel</SecondaryButton>

                                            <DangerButton className="ms-3" disabled={processing}>
                                                Remove Account
                                            </DangerButton>
                                        </div>
                                    </form>
                                </Modal>
                            )}
                        </React.Fragment>
                    );
                })}
            </div>
        </section>
    );
}
