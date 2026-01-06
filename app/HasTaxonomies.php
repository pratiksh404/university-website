<?php

namespace App;

use App\Models\Taxonomy;
use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use RuntimeException;

trait HasTaxonomies
{
    /**
     * MUST be implemented by the model
     *
     * [
     *   ScopeClass::class => TypeEnum::class
     * ]
     */
    abstract public static function taxonomyScopes(): array;

    /**
     * All taxonomies attached to this model
     */
    public function taxonomies(): MorphToMany
    {
        return $this->morphToMany(Taxonomy::class, 'taxonomable');
    }

    /**
     * Get taxonomies for a given scope & type
     */
    public function taxonomy(string $scopeClass, string|BackedEnum $type)
    {
        $this->assertScopeExists($scopeClass);

        $scopeKey = $scopeClass::key();
        $typeValue = $type instanceof BackedEnum ? $type->value : $type;

        return $this->taxonomies()
            ->where('scope', $scopeKey)
            ->where('type', $typeValue);
    }

    /**
     * Attach taxonomy safely
     */
    public function attachTaxonomy(
        int $taxonomyId,
        string $scopeClass,
        string|BackedEnum $type
    ): void {
        $this->assertScopeExists($scopeClass);

        $taxonomy = Taxonomy::findOrFail($taxonomyId);

        $scopeKey  = $scopeClass::key();
        $typeValue = $type instanceof BackedEnum ? $type->value : $type;

        if ($taxonomy->scope !== $scopeKey) {
            throw new RuntimeException('Invalid taxonomy scope.');
        }

        if ($taxonomy->type !== $typeValue) {
            throw new RuntimeException('Invalid taxonomy type.');
        }

        $this->taxonomies()->syncWithoutDetaching($taxonomyId);
    }

    /**
     * Validate scope + enum pairing
     */
    protected function assertScopeExists(string $scopeClass): void
    {
        $scopes = static::taxonomyScopes();

        if (! isset($scopes[$scopeClass])) {
            throw new RuntimeException(
                "Scope [$scopeClass] is not registered on model " . static::class
            );
        }
    }

    /**
     * Get enum class for a scope
     */
    public static function taxonomyEnumFor(string $scopeClass): string
    {
        return static::taxonomyScopes()[$scopeClass];
    }
}
