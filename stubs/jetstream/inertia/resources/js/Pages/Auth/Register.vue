<script setup>
import {Head, Link, useForm} from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Socialstream from '@/Components/Socialstream.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register"/>

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo/>
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name"/>
                <TextInput
                    id="name"
                    v-model="form.name"
                    autocomplete="name"
                    autofocus
                    class="mt-1 block w-full"
                    required
                    type="text"
                />
                <InputError :message="form.errors.name" class="mt-2"/>
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email"/>
                <TextInput
                    id="email"
                    v-model="form.email"
                    autocomplete="username"
                    class="mt-1 block w-full"
                    required
                    type="email"
                />
                <InputError :message="form.errors.email" class="mt-2"/>
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password"/>
                <TextInput
                    id="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    required
                    type="password"
                />
                <InputError :message="form.errors.password" class="mt-2"/>
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password"/>
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    required
                    type="password"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-2"/>
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required/>

                        <div class="ml-2">
                            I agree to the <a :href="route('terms.show')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                              target="_blank">Terms
                            of Service</a> and <a :href="route('policy.show')" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                                                  target="_blank">Privacy
                            Policy</a>
                        </div>
                    </div>
                    <InputError :message="form.errors.terms" class="mt-2"/>
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')"
                      class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                    Already registered?
                </Link>

                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="ml-4">
                    Register
                </PrimaryButton>
            </div>
        </form>

        <Socialstream v-if="$page.props.socialstream.show && $page.props.socialstream.providers.length"
                      :error="$page.props?.errors?.socialstream || null" :prompt="$page.props.socialstream.prompt"
                      :labels="$page.props.socialstream.labels" :providers="$page.props.socialstream.providers"/>
    </AuthenticationCard>
</template>
