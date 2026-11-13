<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('system_roles')
                    ->label('System roles')
                    ->state(fn (User $record): string => $record->roles->isEmpty()
                        ? 'Registered user'
                        : $record->roles
                            ->pluck('name')
                            ->map(fn (string $role): string => Str::headline($role))
                            ->implode(', '))
                    ->badge(),
                TextColumn::make('email_verified_at')
                    ->label('Verified at')
                    ->dateTime()
                    ->placeholder('Not verified')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->withoutGlobalScopes([SoftDeletingScope::class])
                ->with('roles'))
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()->visible(fn (User $record): bool => ! $record->trashed()),
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
