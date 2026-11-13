<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['translations:id,post_id,locale,title']))
            ->columns([
                TextColumn::make('admin_title')
                    ->label('Title')
                    ->state(fn (Post $record): string => $record->translations->firstWhere('locale', 'en')?->title
                        ?? $record->translations->first()?->title
                        ?? 'Untitled')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'translations',
                        fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"),
                    ))
                    ->limit(50),
                TextColumn::make('author.name')
                    ->placeholder('Unassigned')
                    ->sortable(),
                TextColumn::make('category.name_en')
                    ->label('Category')
                    ->placeholder('Uncategorized')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (PostStatus $state): string => Str::headline($state->value))
                    ->color(fn (PostStatus $state): string => match ($state) {
                        PostStatus::Draft => 'gray',
                        PostStatus::Scheduled => 'warning',
                        PostStatus::Published => 'success',
                    }),
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                IconColumn::make('is_breaking')
                    ->label('Breaking')
                    ->boolean(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        PostStatus::Draft->value => 'Draft',
                        PostStatus::Scheduled->value => 'Scheduled',
                        PostStatus::Published->value => 'Published',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
