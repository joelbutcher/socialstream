import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { ConnectedAccount } from '@/types/socialstream';

export default function UpdateAvatar({ account }: { account: ConnectedAccount }) {
    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        avatar: account.avatar,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        patch(route('profile.avatar.update'), {
            preserveScroll: true,
        });
    };

    return (
      <form onSubmit={submit}>
          <Button variant="link" asChild disabled={processing}>
              <button type="submit" className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive text-primary underline-offset-4 hover:underline">
                  Use Avatar
              </button>
          </Button>
      </form>
    );
}
