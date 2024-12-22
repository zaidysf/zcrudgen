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
            ->hasCommands([
                Commands\ZcrudgenCommand::class,
                Commands\SetupCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        if (config('zcrudgen.swagger.enabled', true)) {
            $this->configureScramble();
        }
    }

    protected function configureScramble(): void
    {
        // Instead of using configure(), directly set the config
        config([
            'scramble' => [
                'openapi' => [
                    'info' => [
                        'version' => config('zcrudgen.swagger.version', '3.0.0'),
                    ],
                    'servers' => [
                        [
                            'url' => config('app.url'),
                            'description' => 'API Server',
                        ],
                    ],
                ],
            ],
        ]);
    }
}
