<?php

namespace JoelButcher\Socialstream\Concerns;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

trait InteractsWithComposer
{
    /**
     * Determine if the given Composer package is installed.
     */
    protected function hasComposerPackage(string $package): bool
    {
        if (! file_exists(base_path('composer.json'))) {
            return false;
        }

        $packages = json_decode(file_get_contents(base_path('composer.json')), true);

        return array_key_exists($package, $packages['require'] ?? [])
            || array_key_exists($package, $packages['require-dev'] ?? []);
    }

    /**
     * Installs the given Composer Packages into the application.
     */
    protected function requireComposerPackages(array $packages, string $composerBinary = 'global'): bool
    {
        $outputStyle = new BufferedOutput;

        $command = $this->buildBaseComposerCommand('require', $packages, $composerBinary);

        return ! (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            });
    }

    /**
     * Removes the given Composer Packages as "dev" dependencies.
     */
    protected function removeComposerDevPackages(array $packages, string $composerBinary = 'global'): bool
    {
        $outputStyle = new BufferedOutput;

        $command = $this->buildBaseComposerCommand('remove', $packages, $composerBinary, dev: true);

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            }) === 0;
    }

    /**
     * Install the given Composer Packages as "dev" dependencies.
     */
    protected function requireComposerDevPackages(array $packages, string $composerBinary = 'global'): bool
    {
        $outputStyle = new BufferedOutput;

        $command = $this->buildBaseComposerCommand('require', $packages, $composerBinary, dev: true);

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) use ($outputStyle) {
                $outputStyle->write($output);
            }) === 0;
    }

    protected function buildBaseComposerCommand(string $command, array $packages, string $composerBinary = 'global', bool $dev = false): array
    {
        $command = $composerBinary !== 'global'
            ? [$this->phpBinary(), $composerBinary, $command]
            : ['composer', $command];

        return array_merge(
            array_filter($command),
            $dev ? ['--dev'] : [],
            $packages
        );
    }

    /**
     * Get the path to the appropriate PHP binary.
     */
    protected function phpBinary(): string
    {
        return (new PhpExecutableFinder)->find(false) ?: 'php';
    }
}
