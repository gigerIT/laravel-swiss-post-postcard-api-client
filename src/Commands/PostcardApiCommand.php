<?php

namespace Gigerit\PostcardApi\Commands;

use Illuminate\Console\Command;

class PostcardApiCommand extends Command
{
    public $signature = 'laravel-swiss-post-postcard-api-client';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
