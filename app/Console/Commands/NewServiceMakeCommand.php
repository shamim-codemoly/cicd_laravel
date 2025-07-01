<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class NewServiceMakeCommand extends GeneratorCommand
{
    protected $signature = 'make:new-service {model} {path?}';

    protected $description = 'Create a new Service class and its Interface';

    public function handle(): bool
    {
        $model = $this->argument('model');
        $path = $this->argument('path') ?? '';

        $interfaceName = $model.'Interface';
        $serviceName = $model.'Service';
        $modelName = $model;

        $interfaceNamespace = '\\App\\Interfaces\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');
        $serviceNamespace = '\\App\\Services\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        // Generate the Interface
        Artisan::call('make:interface-file', [
            'name' => $interfaceNamespace.$interfaceName,
        ]);

        // Generate the Service
        $this->call('make:service-file', [
            'name' => $serviceNamespace.$serviceName,
            '--model' => $modelName,
        ]);

        // Register in RepositoryServiceProvider
        $codeToAdd = "\n\t\t\$this->app->bind(\n".
            "\t\t\t{$interfaceNamespace}{$interfaceName}::class,\n".
            "\t\t\t{$serviceNamespace}{$serviceName}::class\n".
            "\t\t);\n";

        $appServiceProviderFile = app_path('Providers/RepositoryServiceProvider.php');
        $this->injectCodeToRegisterMethod($appServiceProviderFile, $codeToAdd);

        return true;
    }

    protected function getStub()
    {
        return resource_path('stubs/service.stub');
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Services';
    }

    protected function injectCodeToRegisterMethod($file, $code)
    {
        $contents = file_get_contents($file);
        $search = 'public function register(){';
        $replace = $search."\n\t".$code;
        $contents = str_replace($search, $replace, $contents);
        file_put_contents($file, $contents);
    }
}
