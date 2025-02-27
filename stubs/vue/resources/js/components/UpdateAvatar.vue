<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { type ConnectedAccount } from '@/types/socialstream';

const { account } = defineProps<{
   account: ConnectedAccount;
}>();

const form = useForm({
   avatar: account.avatar,
});

const submit = () => {
   form.patch(route('profile.avatar.update'), {
      onFinish: () => {
         form.reset();
      },
   });
};
</script>

<template>
   <form @submit.prevent="submit">
      <Button variant="link" asChild :disabled="form.processing">
         <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-[color,box-shadow] disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg:not([class*='size-'])]:size-4 [&_svg]:shrink-0 outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive text-primary underline-offset-4 hover:underline">
            Use Avatar
         </button>
      </Button>
   </form>
</template>
