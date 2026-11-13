<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Notifications\CommentStatusChangedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes([SoftDeletingScope::class]))
            ->columns([
                TextColumn::make('body')
                    ->label('Comment')
                    ->limit(100)
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->default('Former member')
                    ->searchable(),
                TextColumn::make('post.id')
                    ->label('Article')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (CommentStatus $state): string => ucfirst($state->value)),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(CommentStatus::options()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Pending)
                    ->action(function (Comment $record): void {
                        Gate::authorize('update', $record);
                        $record->update(['status' => CommentStatus::Approved]);

                        $record->user?->notify(new CommentStatusChangedNotification(CommentStatus::Approved));
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->visible(fn (Comment $record): bool => $record->status === CommentStatus::Pending)
                    ->action(function (Comment $record): void {
                        Gate::authorize('update', $record);
                        $record->update(['status' => CommentStatus::Rejected]);

                        $record->user?->notify(new CommentStatusChangedNotification(CommentStatus::Rejected));
                    }),
                DeleteAction::make(),
                RestoreAction::make(),
            ]);
    }
}
