<script setup lang="ts">
import { type ConnectedAccount } from '@/types/socialstream';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SocialstreamIcon from '@/components/SocialstreamIcon.vue';
import UserInfo from '@/components/UserInfo.vue';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import {
   Dialog, DialogClose,
   DialogContent,
   DialogDescription,
   DialogFooter,
   DialogTitle,
   DialogTrigger
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import InputError from '@/components/InputError.vue';
import UpdateAvatar from '@/components/UpdateAvatar.vue';

interface Props {
   account: ConnectedAccount;
   socialstream: Socialstream;
}

const { account, socialstream } = defineProps<Props>();

const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
   password: '',
});

const unlinkAccount = (e: Event) => {
   e.preventDefault();

   form.delete(route('linked-accounts.destroy', { account }), {
      preserveScroll: true,
      onSuccess: () => closeModal(),
      onError: () => passwordInput.value?.focus(),
      onFinish: () => form.reset(),
   });
};

const closeModal = () => {
   form.clearErrors();
   form.reset();
};
</script>

<template>
   <div class="flex px-3 py-6 gap-4">
      <SocialstreamIcon :provider="account.provider.id" class="h-5 w-5"/>

      <div class="grid items-center gap-6 grid-cols-2 w-full">
         <div class="flex items-center justify-between gap-3 md:justify-start">
            <UserInfo :user="account" :showEmail="true" />
         </div>
      </div>

      <div class="grid items-center gap-4 md:flex md:justify-end">
         <UpdateAvatar :account="account" />
      </div>

      <TooltipProvider v-if="!socialstream.hasPassword">
         <Tooltip :delay-duration="0">
            <TooltipTrigger>
               <Button variant="destructive" :disabled="!socialstream.hasPassword">
                  Unlink
               </Button>
            </TooltipTrigger>
            <TooltipContent side="left">
               <p>You must set a password to unlink this account.</p>
            </TooltipContent>
         </Tooltip>
      </TooltipProvider>

      <template v-if="account && socialstream.hasPassword">
         <Dialog>
            <DialogTrigger asChild>
               <Button variant="destructive" :disabled="! socialstream.hasPassword">
                  Unlink
               </Button>
            </DialogTrigger>
            <DialogContent>
               <DialogTitle>Are you sure you want to unlink this account?</DialogTitle>
               <DialogDescription>Once unlinked, you will no longer be able to sign in with this account.</DialogDescription>

               <form @submit.prevent="unlinkAccount">
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

                     <DialogFooter>
                        <DialogClose>
                           <Button variant="link" :onclick="closeModal">
                              Cancel
                           </Button>
                        </DialogClose>

                        <Button variant="destructive" :disabled="form.processing" as-child>
                           <button type="submit">Unlink Account</button>
                        </Button>
                     </DialogFooter>
                  </div>
               </form>
            </DialogContent>
         </Dialog>
      </template>
   </div>
</template>
