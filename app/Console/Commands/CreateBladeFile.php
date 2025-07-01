<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateBladeFile extends Command
{
    protected $signature = 'make:blade {model} {fileType} {path}';

    protected $description = 'Create a blade file (index, create, or edit) from stubs for a specific model class';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $model = strtolower($this->argument('model'));
        $fileType = $this->argument('fileType');
        $path = $this->argument('path');
        $modelLower = strtolower($model);

        // Define paths
        $stubsPath = resource_path('stubs');
        $viewsPath = resource_path("views/{$path}");

        // Create the directory if it doesn't exist
        if (! File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }

        // Define stub file based on file type
        $stubFiles = [
            'index' => 'index.stub',
            'create' => 'create.stub',
            'edit' => 'edit.stub',
        ];

        if (! array_key_exists($fileType, $stubFiles)) {
            $this->error("Invalid file type: {$fileType}. Valid types are: index, create, edit.");

            return;
        }

        $stubFile = $stubFiles[$fileType];
        $bladeFile = "{$fileType}.blade.php";

        $stubPath = "{$stubsPath}/{$stubFile}";
        $bladePath = "{$viewsPath}/{$bladeFile}";

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
