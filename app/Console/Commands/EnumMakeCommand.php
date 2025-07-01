<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class EnumMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:enum {name}';

    protected $description = 'Create a new Enum class';

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/enum.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Enums';
    }
}
