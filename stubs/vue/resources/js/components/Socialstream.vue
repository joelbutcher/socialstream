<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Separator } from '@/components/ui/separator';
import SocialstreamIcon from '@/components/SocialstreamIcon.vue';
import type { Socialstream } from '@/types/socialstream';

defineProps<{
   socialstream: Socialstream;
}>();
</script>

<template>
   <div class="grid space-y-6">
      <div class="inline-flex w-full items-center">
         <Separator class="shrink" />
         <span class="mx-3 text-center text-sm text-muted-foreground">OR</span>
         <Separator class="shrink" />
      </div>

      <InputError :message="socialstream.error" class="text-center" />

      <div class="grid grid-cols-4 gap-3">
         <a
            v-for="provider in socialstream.providers"
            :key="provider.id"
            class="inline-flex items-center justify-center rounded-md px-4 py-2 border hover:border-black transition duration-300 ease-out"
            :href="route('oauth.redirect', { provider: provider.id })"
         >
            <SocialstreamIcon :provider="provider.id" class="h-6 w-6" />
         </a>
      </div>
   </div>
</template>
