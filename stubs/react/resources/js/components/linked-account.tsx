import { ConnectedAccount, Socialstream } from '@/types/socialstream';
import { SocialstreamIcon } from '@/components/socialstream-icon';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/input-error';
import { FormEventHandler, useRef } from 'react';
import { useForm } from '@inertiajs/react';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { UserInfo } from '@/components/user-info';

interface LinkedAccountProps {
    account: ConnectedAccount;
    socialstream: Socialstream;
}

export default function LinkedAccount({ account, socialstream }: LinkedAccountProps) {
    const passwordInput = useRef<HTMLInputElement>(null);

    const { patch, processing: updatingAvatar } = useForm({
        avatar: account.avatar,
    });

    const updateAvatar: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.avatar.update'), {
            preserveScroll: true,
        });
    };


    const { data, setData, delete: destroy, processing, reset, errors, clearErrors } = useForm({
        password: '',
    });

    const unlinkAccount: FormEventHandler = (e) => {
        e.preventDefault();

        destroy(route('linked-accounts.destroy', { account }), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onError: () => passwordInput.current?.focus(),
            onFinish: () => reset(),
        });
    };

    const closeModal = () => {
        clearErrors();
        reset();
    };

    return (
      <div className="flex px-6 py-4 gap-4 border rounded-xl">
          <div className="grid w-full md:grid-cols-2 items-center gap-6">
              <div className="flex flex-row-reverse md:flex-row justify-between md:justify-start md:items-center gap-3">
                  <SocialstreamIcon provider={account.provider.id} className="h-6 w-6 md:h-8 md:w-8"/>

                  <div className="flex items-center justify-center gap-2">
                      <UserInfo user={account} showEmail={true} />
                  </div>
              </div>

              <div className="flex flex-col md:flex-row items-center justify-between md:justify-end gap-3 md:gap-2">
                  <form onSubmit={updateAvatar} className="flex w-full md:w-auto">
                      <Button variant="link" type="submit" disabled={updatingAvatar} className="w-full">
                          Use Avatar
                      </Button>
                  </form>

                  {!socialstream.hasPassword && (
                    <Tooltip delayDuration={0}>
                        <TooltipTrigger className="flex w-full md:w-auto">
                            <Button variant="destructive" disabled={!socialstream.hasPassword} className="w-full">
                                Unlink
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="left">
                            <p>Set a password to unlink this account</p>
                        </TooltipContent>
                    </Tooltip>
                  )}

                  {account && socialstream.hasPassword && (
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button variant="destructive" disabled={!socialstream.hasPassword} className="w-full md:w-auto">
                                Unlink
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogTitle>Are you sure you want to unlink this account?</DialogTitle>
                            <DialogDescription>Once unlinked, you will no longer be able to sign in with this account.</DialogDescription>
                            <form className="space-y-6" onSubmit={unlinkAccount}>
                                <div className="grid gap-2">
                                    <Label htmlFor="password" className="sr-only">
                                        Password
                                    </Label>

                                    <Input
                                      id="password"
                                      type="password"
                                      name="password"
                                      ref={passwordInput}
                                      value={data.password}
                                      onChange={(e) => setData('password', e.target.value)}
                                      placeholder="Password"
                                      autoComplete="current-password"
                                    />

                                    <InputError message={errors.password} />
                                </div>

                                <DialogFooter className="gap-2">
                                    <DialogClose asChild>
                                        <Button variant="secondary" onClick={closeModal}>
                                            Cancel
                                        </Button>
                                    </DialogClose>

                                    <Button variant="destructive" disabled={processing} asChild>
                                        <button type="submit">Delete account</button>
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                  )}
              </div>
          </div>
      </div>
    );
}
