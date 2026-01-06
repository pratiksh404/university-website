<?php

namespace App\Filament\Resources\Taxonomies\Schemas;

use App\Models\Taxonomy;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TaxonomyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('slug'),
                TextEntry::make('type'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('parent.name')
                    ->label('Parent')
                    ->placeholder('-'),
                TextEntry::make('sort_order')
                    ->numeric(),
                TextEntry::make('lft')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('rgt')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('depth')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Taxonomy $record): bool => $record->trashed()),
                TextEntry::make('scope'),
            ]);
    }
}
