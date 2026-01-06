<?php

namespace App\Filament\Resources\Taxonomies\Schemas;

use App\Models\Taxonomy;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Services\TaxonomyScopeService;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Utilities\Set;

class TaxonomyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                /**
                 * Scope selection (auto-detected)
                 */
                Select::make('scope')
                    ->label('Scope (Model)')
                    ->required()
                    ->options(
                        fn() =>
                        app(TaxonomyScopeService::class)->getScopes()
                    )
                    ->reactive(),

                /**
                 * Type selection (enum-based, scope-dependent)
                 */
                Select::make('type')
                    ->label('Type')
                    ->required()
                    ->options(function (callable $get) {
                        $scope = $get('scope');

                        if (! $scope) {
                            return [];
                        }

                        $enum = app(TaxonomyScopeService::class)
                            ->getEnumForScope($scope);

                        return $enum
                            ? collect($enum::cases())
                            ->mapWithKeys(fn($case) => [
                                $case->value => ucfirst($case->value),
                            ])
                            ->toArray()
                            : [];
                    })
                    ->reactive(),

                Select::make('parent_id')
                    ->label('Parent Taxonomy')
                    ->searchable()
                    ->placeholder('None')
                    ->options(function (callable $get) {
                        $scope = $get('scope');
                        $type  = $get('type');

                        if (! $scope || ! $type) {
                            return [];
                        }

                        return Taxonomy::query()
                            ->ofScope($scope)
                            ->ofType($type)
                            ->topLevel()
                            ->sorted()
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->columnSpanFull(),

                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required(),
                RichEditor::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
