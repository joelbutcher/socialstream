<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { Provider } from '@/types/socialstream';

const { provider } = defineProps<{
   provider: Provider;
}>()

const form = useForm({
   password: '',
});

const submit = () => {
   form.post(route('oauth.confirm', { provider: provider.id }), {
      onFinish: () => {
         form.reset();
      },
   });
};
</script>

<template>
   <AuthLayout :title="`Link ${provider.name}`" :description="`Please confirm your password to connect your ${provider.name} account.`">
      <Head :title="`Link ${provider.name}`" />

      <form @submit.prevent="submit">
         <div class="space-y-6">
            <div class="grid gap-2">
               <Label htmlFor="password">Password</Label>
               <Input
                  id="password"
                  type="password"
                  class="mt-1 block w-full"
                  v-model="form.password"
                  required
                  autocomplete="current-password"
                  autofocus
               />

               <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center">
               <Button class="w-full" :disabled="form.processing">
                  <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                  Confirm Password
               </Button>
            </div>
         </div>
      </form>
   </AuthLayout>
</template>
