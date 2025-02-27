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
import UpdateAvatar from '@/components/update-avatar';

interface LinkedAccountProps {
    account: ConnectedAccount;
    socialstream: Socialstream;
}

export default function LinkedAccount({ account, socialstream }: LinkedAccountProps) {
    const passwordInput = useRef<HTMLInputElement>(null);

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
      <div className="flex px-3 py-6 gap-4">
          <SocialstreamIcon provider={account.provider.id} className="w-5"/>

          <div className="grid items-center gap-6 grid-cols-2 w-full">
              <div className="flex items-center justify-between gap-3 md:justify-start">
                  <UserInfo user={account} showEmail={true} />
              </div>

              <div className="grid items-center gap-4 md:flex md:justify-end">
                  <UpdateAvatar account={account} />

                  {!socialstream.hasPassword && (
                    <Tooltip delayDuration={0}>
                        <TooltipTrigger>
                            <Button variant="destructive" disabled={!socialstream.hasPassword}>
                                Unlink
                            </Button>
                        </TooltipTrigger>
                        <TooltipContent side="left">
                            {!socialstream.hasPassword && (
                              <p>You must set a password to unlink this account.</p>
                            )}
                        </TooltipContent>
                    </Tooltip>
                  )}

                  {account && socialstream.hasPassword && (
                    <Dialog>
                        <DialogTrigger asChild>
                            <Button variant="destructive" disabled={!socialstream.hasPassword}>
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
