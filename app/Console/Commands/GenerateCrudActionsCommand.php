<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class GenerateCrudActionsCommand extends GeneratorCommand
{
    protected $name = 'make:crud-actions';

    protected $description = 'Generate CRUD action classes, a resource controller, request classes, and views for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $path = $this->option('path') ?? '';

        // Generate CRUD action classes
        $this->generateActionClass($model, 'Create', $path);
        $this->generateActionClass($model, 'Read', $path);
        $this->generateActionClass($model, 'Update', $path);
        $this->generateActionClass($model, 'Delete', $path);

        // Generate the resource controller
        $this->generateController($model, $path);

        // Generate request classes
        $this->generateRequestClass($model, 'Store', $path);
        $this->generateRequestClass($model, 'Update', $path);
        $this->generateRequestClass($model, 'Delete', $path);

        // Generate views
        $this->generateView($model, 'index');
        $this->generateView($model, 'create');
        $this->generateView($model, 'edit');
        $this->generateView($model, 'show');

        return true;
    }

    protected function generateActionClass($model, $action, $path)
    {
        $name = "{$model}{$action}Action";
        $namespace = 'App\\Actions\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:action', [
            'name' => $namespace.$name,
            '--model' => $model,
            '--action' => $action,
        ]);
    }

    protected function generateController($model, $path)
    {
        $controllerName = "{$model}Controller";
        $namespace = 'App\\Http\\Controllers\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:controller', [
            'name' => $namespace.$controllerName,
            '--model' => $model,
            '--path' => $path,
        ]);
    }

    protected function generateRequestClass($model, $action, $path)
    {
        $name = "{$model}{$action}Request";
        $namespace = 'App\\Http\\Requests\\'.($path ? Str::replace('/', '\\', $path).'\\' : '');

        $this->call('make:request', [
            'name' => $namespace.$name,
        ]);
    }

    protected function generateView($model, $view)
    {
        $viewPath = resource_path("views/{$model}/{$view}.blade.php");

        if (! file_exists($viewPath)) {
            $this->makeDirectory($viewPath);
            file_put_contents($viewPath, $this->getViewStub($view));
        }
    }

    protected function getViewStub($view)
    {
        return file_get_contents(resource_path("stubs/{$view}.stub"));
    }

    protected function getStub()
    {
        return '';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return '';
    }

    protected function getArguments()
    {
        return [
            ['model', InputOption::VALUE_REQUIRED, 'The name of the model'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, 'The optional path for the generated files'],
        ];
    }
}
