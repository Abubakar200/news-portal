<?php

namespace App\Filament\Resources\Comments;

use App\Filament\Resources\Comments\Pages\ListComments;
use App\Filament\Resources\Comments\Schemas\CommentForm;
use App\Filament\Resources\Comments\Tables\CommentsTable;
use App\Models\Comment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CommentResource extends Resource
{
    protected static ?string $model = Comment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Community';

    protected static ?string $navigationLabel = 'Comments';

    protected static ?string $pluralModelLabel = 'comments';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'post']);
    }

    public static function form(Schema $schema): Schema
    {
        return CommentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CommentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
        ];
    }
}
