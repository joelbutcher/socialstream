import { Socialstream as SocialstreamType } from '@/types/socialstream';
import { cn } from '@/lib/utils';
import { SocialstreamIcon } from '@/components/socialstream-icon';
import InputError from '@/components/input-error';
import * as React from 'react';
import { Separator } from '@/components/ui/separator';

interface SocialstreamProps extends React.ComponentProps<"div"> {
  socialstream: SocialstreamType;
}

export default function Socialstream({socialstream, ...props }: SocialstreamProps) {
  return (
      <div className={cn('grid space-y-6', props.className)} {...props}>
          <div className="inline-flex items-center w-full">
              <Separator className="shrink" />
              <span className="text-muted-foreground text-center text-sm mx-3">OR</span>
              <Separator className="shrink" />
          </div>

          <InputError message={socialstream.error} className="text-center" />

          <div className="grid grid-cols-4 gap-3">
              {socialstream.providers.map((provider) => (
                  <a
                      key={provider.id}
                      className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm h-9 px-3 py-2 border hover:border-black transition"
                      href={route('oauth.redirect', { provider: provider.id })}
                  >
                      <SocialstreamIcon provider={provider.id} className="w-5 h-5 mx-2"/>
                  </a>
              ))}
          </div>
      </div>
  );
}
