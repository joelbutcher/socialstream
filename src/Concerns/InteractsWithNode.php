<?php

namespace JoelButcher\Socialstream\Concerns;

trait InteractsWithNode
{
    /**
     * Determine if the given node package is installed.
     */
    protected function hasNodePackage(string $package): bool
    {
        if (! file_exists(base_path('package.json'))) {
            return false;
        }

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        return array_key_exists($package, $packages['dependencies'] ?? [])
            || array_key_exists($package, $packages['devDependencies'] ?? []);
    }
}
