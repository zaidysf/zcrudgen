<?php

namespace ZaidYasyaf\Zcrudgen;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ZcrudgenServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('zcrudgen')
            ->hasConfigFile()
            ->hasCommand(Commands\ZcrudgenCommand::class);

        if (config('zcrudgen.swagger.enabled', true)) {
            $this->configureScramble();
        }
    }

    protected function configureScramble(): void
    {
        app()->configure('scramble');

        config([
            'scramble.openapi.info.version' => config('zcrudgen.swagger.version', '3.0.0'),
            'scramble.openapi.servers' => [
                [
                    'url' => config('app.url'),
                    'description' => 'API Server',
                ],
            ],
        ]);
    }
}
