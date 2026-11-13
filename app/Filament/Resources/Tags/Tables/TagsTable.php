<?php

namespace App\Filament\Resources\Tags\Tables;

use App\Models\Tag;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
                TextColumn::make('name_en')
                    ->label('English name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name_ur')
                    ->label('Urdu name')
                    ->searchable()
                    ->extraAttributes(['dir' => 'rtl']),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (Tag $record): bool => ! $record->trashed()),
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
