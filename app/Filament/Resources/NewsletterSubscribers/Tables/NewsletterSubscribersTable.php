<?php

namespace App\Filament\Resources\NewsletterSubscribers\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NewsletterSubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                TextColumn::make('subscribed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
