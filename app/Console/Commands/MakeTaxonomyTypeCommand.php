<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeTaxonomyTypeCommand extends Command
{
    protected $signature = 'taxonomy:type 
                            {model : The model name (e.g., Post)} 
                            {--types= : Comma-separated list of taxonomy types (category,tag)}';

    protected $description = 'Generate a scope-specific taxonomy type enum class for a model';

    protected Filesystem $files;

    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }

    public function handle(): int
    {
        $model = $this->argument('model');
        $enumName = "{$model}TaxonomyType";
        $namespace = "App\\Enums\\Taxonomy";
        $directory = app_path("Enums/Taxonomy");

        // Create directory if it doesn't exist
        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        $types = $this->option('types')
            ? array_map('trim', explode(',', $this->option('types')))
            : [];

        $stub = $this->buildEnumStub($namespace, $enumName, $types);

        $filePath = "{$directory}/{$enumName}.php";

        if ($this->files->exists($filePath)) {
            $this->error("Enum {$enumName} already exists!");
            return 1;
        }

        $this->files->put($filePath, $stub);
        $this->info("Enum {$enumName} created successfully at {$filePath}");

        return 0;
    }

    protected function buildEnumStub(string $namespace, string $enumName, array $types): string
    {
        $cases = '';
        foreach ($types as $type) {
            $caseName = strtoupper(Str::snake($type, '_'));
            $cases .= "    case {$caseName} = '{$type}';\n";
        }

        return <<<PHP
<?php

namespace {$namespace};

enum {$enumName}: string
{
{$cases}}
PHP;
    }
}
