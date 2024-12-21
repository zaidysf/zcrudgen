<?php

namespace ZaidYasyaf\Zcrudgen\Commands;

use Illuminate\Console\Command;

class ZcrudgenCommand extends Command
{
    public $signature = 'zcrudgen:make {name}
        {--relations=}
        {--middleware=}
        {--permissions}';

    public $description = 'Generate CRUD API with advanced features';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (empty($name)) {
            $this->error('Please provide a model name.');

            return self::FAILURE;
        }

        // TODO: Implement generation logic
        $this->info('CRUD generated successfully for '.$name.' model!');

        return self::SUCCESS;
    }
}
