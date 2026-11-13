<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! auth()->user()->can('posts.update')) {
            $data['author_id'] = $this->record->author_id;
        }

        if (! auth()->user()->can('posts.publish')) {
            $data['is_featured'] = $this->record->is_featured;
            $data['is_breaking'] = $this->record->is_breaking;
        }

        unset($data['status'], $data['scheduled_at'], $data['published_at']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publish now')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()->can('posts.publish'))
                ->action(function (): void {
                    Gate::authorize('publish', $this->record);
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->publish();
                    $this->refreshFormData(['status', 'scheduled_at', 'published_at']);
                }),
            Action::make('schedule')
                ->schema([
                    DateTimePicker::make('scheduled_at')
                        ->required()
                        ->after('now'),
                ])
                ->visible(fn (): bool => auth()->user()->can('posts.schedule'))
                ->action(function (array $data): void {
                    Gate::authorize('schedule', $this->record);

                    $scheduledAt = CarbonImmutable::parse($data['scheduled_at']);

                    if (! $scheduledAt->isFuture()) {
                        throw ValidationException::withMessages([
                            'scheduled_at' => 'The scheduled time must be in the future.',
                        ]);
                    }

                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->scheduleFor($scheduledAt);
                    $this->refreshFormData(['status', 'scheduled_at', 'published_at']);
                }),
            Action::make('saveAsDraft')
                ->label('Save as draft')
                ->requiresConfirmation()
                ->visible(fn (): bool => auth()->user()->can('posts.update-own') || auth()->user()->can('posts.update'))
                ->action(function (): void {
                    Gate::authorize('draft', $this->record);
                    $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
                    $this->record->saveAsDraft();
                    $this->refreshFormData(['status', 'scheduled_at', 'published_at']);
                }),
            DeleteAction::make(),
        ];
    }
}
