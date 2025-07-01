<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateResourceBladeFile extends Command
{
    protected $signature = 'make:resource-blade {model} {path}';

    protected $description = 'Create resource blade files (index, create, or edit) from stubs for a specific model class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = strtolower($this->argument('model'));
        $path = $this->argument('path');
        $modelLower = strtolower($model);

        // Define paths
        $stubsPath = resource_path('stubs');
        $viewsPath = resource_path("views/{$path}");

        // Create the directory if it doesn't exist
        if (! File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Define stub files
        $stubFiles = [
            'index.stub' => 'index.blade.php',
            'create.stub' => 'create.blade.php',
            'edit.stub' => 'edit.blade.php',
        ];

        // Create the stub files and replace placeholders in the blade files
        foreach ($stubFiles as $stub => $blade) {
            $stubPath = "{$stubsPath}/{$stub}";
            $bladePath = "{$viewsPath}/{$blade}";

            if (File::exists($stubPath)) {
                $stubContent = File::get($stubPath);

                // Replace placeholder with model name
                $stubContent = str_replace('{{ model }}', $model, $stubContent);

                File::put($bladePath, $stubContent);
                $this->info("Created: {$bladePath}");
            } else {
                $this->error("Stub file does not exist: {$stubPath}");
            }
        }
    }
}
