<?php

namespace App\Services;

use ReflectionClass;
use App\HasTaxonomies;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class TaxonomyScopeService
{
    protected array $scopes = [];

    public function __construct()
    {
        $this->scanModels();
    }

    /**
     * Scan app/Models for classes using HasTaxonomies trait
     */
    protected function scanModels(): void
    {
        $modelFiles = File::allFiles(app_path('Models'));

        foreach ($modelFiles as $file) {
            $class = $this->getClassFullName($file->getRealPath());

            if (! $class || ! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if (in_array(HasTaxonomies::class, $reflection->getTraitNames())) {
                $scope = $class::$taxonomyScope ?? Str::snake(class_basename($class));
                $enum  = $class::$taxonomyTypeEnum ?? null;

                $this->scopes[$scope] = [
                    'model' => $class,
                    'enum' => $enum,
                ];
            }
        }
    }

    /**
     * Return all detected scopes
     */
    public function getScopes(): array
    {
        return array_keys($this->scopes);
    }

    /**
     * Return enum class for a given scope
     */
    public function getEnumForScope(string $scope): ?string
    {
        return $this->scopes[$scope]['enum'] ?? null;
    }

    /**
     * Return model class for a given scope
     */
    public function getModelForScope(string $scope): ?string
    {
        return $this->scopes[$scope]['model'] ?? null;
    }

    protected function getClassFullName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        $namespace = $class = null;

        // Match namespace
        if (preg_match('/^namespace\s+(.+?);/m', $content, $matches)) {
            $namespace = $matches[1];
        }

        // Match class name
        if (preg_match('/class\s+(\w+)/m', $content, $matches)) {
            $class = $matches[1];
        }

        if ($namespace && $class) {
            return $namespace . '\\' . $class;
        }

        return $class; // fallback if no namespace
    }
}
