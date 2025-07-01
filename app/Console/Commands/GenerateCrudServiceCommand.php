<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class GenerateCrudServiceCommand extends GeneratorCommand
{
    protected $name = 'make:crud';

    protected $description = 'Generate CRUD operations including controller, requests, service, interface, views, and routes for a given model';

    public function handle(): bool
    {
        $model = $this->argument('model');
        $path = $this->option('path') ?? '';
        $modelLower = strtolower($model);

        // Generate controller
        // $this->generateController($model, $path = 'WEB');

        // Generate request classes
        $this->generateRequestClass($model, 'Store', $path = $model);
        $this->generateRequestClass($model, 'Update', $path = $model);
        // $this->generateRequestClass($model, 'Delete', $path = $model);

        // Generate service and interface
        $this->generateService($model, $path = $model);
        $this->generateInterface($model, $path = $model);

        // Generate views
        // $this->generateView($model, 'index');
        // $this->generateView($model, 'create');
        // $this->generateView($model, 'edit');
        // $this->generateView($model, 'show');

        // Generate route file
        // $this->generateRouteFile($model, $path);

        // Include the route file in web.php
        // $this->includeRouteFileInWeb($model);

        // Now, add API specific generation
        $this->generateApiController($model);
        $this->generateApiRouteFile($model);
        $this->includeApiRouteFile();

        return true;
    }

    protected function generateController($model, $path)
    {
        $controllerName = "{$model}Controller";
        $namespace = 'App\\Http\\Controllers\\' . ($path ? Str::replace('/', '\\', $path) . '\\' : '');

        $this->call('make:controller', [
            'name' => $namespace . $controllerName,
            '--model' => $model,
            '--path' => $path,
        ]);
    }

    protected function generateApiController($model)
    {
        $controllerName = "{$model}Controller";
        $namespace = 'App\\Http\\Controllers\\API';
        $controllerPath = app_path("Http/Controllers/API/{$controllerName}.php");

        // Load the custom API controller stub
        $stub = file_get_contents(resource_path('stubs/api-controller.stub'));

        // Replace placeholder values in the stub
        $content = str_replace(
            ['DummyNamespace', 'DummyFullModelClass', 'DummyClass', 'DummyModel', 'model'],
            [
                $namespace,
                "App\\Models\\{$model}",
                "{$model}Controller",
                "{$model}",
                strtolower($model),
            ],
            $stub
        );

        // Write the controller file to the existing directory
        file_put_contents($controllerPath, $content);
    }

    protected function generateRequestClass($model, $action, $path)
    {
        $name = "{$model}{$action}Request";
        $namespace = 'App\\Http\\Requests\\' . ($path ? Str::replace('/', '\\', $path) . '\\' : '');

        $this->call('make:request', [
            'name' => $namespace . $name,
        ]);
    }

    protected function generateService($model, $path)
    {
        $serviceName = "{$model}Service";
        $namespace = 'App\\Services\\' . ($path ? Str::replace('/', '\\', $path) . '\\' : '');

        $this->call('make:service-file', [
            'name' => $namespace . $serviceName,
            '--model' => $model,
        ]);
    }

    protected function generateInterface($model, $path)
    {
        $interfaceName = "{$model}Interface";
        $namespace = 'App\\Interfaces\\' . ($path ? Str::replace('/', '\\', $path) . '\\' : '');

        $this->call('make:interface-file', [
            'name' => $namespace . $interfaceName,
        ]);
    }

    protected function generateView($model, $view)
    {
        $modelLower = strtolower($model);
        $viewPath = resource_path("views/pages/{$modelLower}/{$view}.blade.php");

        if (! file_exists($viewPath)) {
            $this->makeDirectory($viewPath);
            file_put_contents($viewPath, $this->getViewStub($view, $model));
        }
    }

    protected function getViewStub($view, $model)
    {
        $stub = file_get_contents(resource_path("stubs/{$view}.stub"));

        // Replace DummyModel with the actual model name
        return str_replace(['DummyModel', 'model'], [$model, strtolower($model)], $stub);
    }

    protected function generateRouteFile($model, $path)
    {
        $routeFileName = strtolower($model) . '_routes.php';
        $routeFilePath = base_path("routes//web_route/{$routeFileName}");
        $modelLower = strtolower($model);
        $content = <<<EOL
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\\WEB\\{$model}Controller;

Route::resource('{$modelLower}s', {$model}Controller::class);
EOL;

        file_put_contents($routeFilePath, $content);
    }

    protected function generateApiRouteFile($model)
    {
        $routeDirectory = base_path("routes/api_route");

        // Check if the directory exists, if not, create it
        if (!file_exists($routeDirectory)) {
            mkdir($routeDirectory, 0755, true);
        }

        $routeFileName = strtolower($model) . '_api_route.php';
        $routeFilePath = $routeDirectory . '/' . $routeFileName;
        $modelLower = strtolower($model);
        $content = <<<EOL
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\\{$model}Controller;

Route::get('all-{$modelLower}', [{$model}Controller::class, 'all']);

Route::middleware(['auth:api'])->group(function () {
    Route::put('{$modelLower}s-status/{id}', [{$model}Controller::class, 'status']);
    Route::apiResource('{$modelLower}s', {$model}Controller::class);
});
EOL;

        file_put_contents($routeFilePath, $content);
    }

    protected function includeRouteFileInWeb($model)
    {
        $webRouteFile = base_path('routes/web.php');
        $routeFileName = strtolower($model) . '_routes.php';
        $includeStatement = "require __DIR__ . '/web_route/{$routeFileName}';";

        $content = file_get_contents($webRouteFile);

        if (strpos($content, $includeStatement) === false) {
            file_put_contents($webRouteFile, $content . PHP_EOL . $includeStatement);
        }
    }

    protected function includeApiRouteFile()
    {
        $apiRouteFile = base_path('routes/api.php');
        $routeFileName = strtolower($this->argument('model')) . '_api_route.php';
        $includeStatement = "require __DIR__ . '/api_route/{$routeFileName}';";

        // Check if routes/api.php exists, if not create it
        if (!file_exists($apiRouteFile)) {
            file_put_contents($apiRouteFile, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        $content = file_get_contents($apiRouteFile);

        if (strpos($content, $includeStatement) === false) {
            file_put_contents($apiRouteFile, $content . PHP_EOL . $includeStatement);
        }
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
