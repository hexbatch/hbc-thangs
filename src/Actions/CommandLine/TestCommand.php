<?php
namespace Hexbatch\Thangs\Actions\CommandLine;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class TestCommand
{
    use AsAction;

    public string $commandSignature = 'thangs:test-command';
    public string $commandDescription = 'Used when making lib';


    public function handle(): void
    {

    }

    public function asCommand(Command $command): void
    {
        $command->info('Thang test command running');
        $this->handle();
        $command->info('Done! '. config('hbc-thangs.middleware.auth_alias'));
    }
}
