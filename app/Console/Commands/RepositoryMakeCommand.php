<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\ServiceProviderInjector;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;

class RepositoryMakeCommand extends GeneratorCommand
{
    use ServiceProviderInjector;

    protected $signature = 'make:repository {name}';

    protected $description = 'Create a new Repository class';

    public function handle()
    {
        $codeToAdd = "\n\t\t\$this->app->bind(\n".
                "\t\t\t\\App\\Interfaces\\".str_replace('/', '\\', $this->argument('name'))."Interface::class,\n".
                "\t\t\t\\App\\Repositories\\".str_replace('/', '\\', $this->argument('name'))."::class\n".
                "\t\t);\n";

        $appServiceProviderFile = app_path('Providers/RepositoryServiceProvider.php');

        $this->injectCodeToRegisterMethod($appServiceProviderFile, $codeToAdd);

        Artisan::call('make:interface', [
            'name' => $this->argument('name').'Interface',
        ]);

        return parent::handle();
    }

    protected function getStub()
    {
        return __DIR__.'/../../../stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Repositories';
    }
}
