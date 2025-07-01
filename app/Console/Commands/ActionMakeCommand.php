<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class ActionMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:action {name}';

    protected $description = 'Create a new Action class';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/action.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Actions';
    }
}
