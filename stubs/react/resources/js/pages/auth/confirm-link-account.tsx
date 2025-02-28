// Components
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { Provider } from '@/types/socialstream';

export default function ConfirmLinkAccount({ provider }: { provider: Provider }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        provider: provider.id,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('oauth.confirm', { provider: provider.id }), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title={`Link ${provider.name}`}
            description={`Please confirm your password to connect your ${provider.name} account.`}
        >
            <Head title={`Link ${provider.name}`} />

            <form onSubmit={submit}>
                <div className="space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Password"
                            autoComplete="current-password"
                            value={data.password}
                            autoFocus
                            onChange={(e) => setData('password', e.target.value)}
                        />

                        <InputError message={errors.password} />
                    </div>

                    <div className="flex items-center">
                        <Button className="w-full" disabled={processing}>
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                            Confirm password
                        </Button>
                    </div>
                </div>
            </form>
        </AuthLayout>
    );
}
