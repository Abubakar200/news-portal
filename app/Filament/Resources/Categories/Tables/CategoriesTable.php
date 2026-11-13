<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoriesTable
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
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (Category $record): bool => ! $record->trashed()),
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
