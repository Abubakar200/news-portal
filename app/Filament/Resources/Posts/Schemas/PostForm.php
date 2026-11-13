<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\PostTranslation;
use App\Models\User;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Article')
                    ->columns(2)
                    ->schema([
                        Select::make('author_id')
                            ->relationship(
                                name: 'author',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereHas(
                                    'roles',
                                    fn (Builder $query): Builder => $query
                                        ->where('name', 'author')
                                        ->where('guard_name', 'web'),
                                ),
                            )
                            ->default(fn (): ?int => auth()->id())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn (): bool => ! auth()->user()?->can('posts.update'))
                            ->dehydrated(),
                        Select::make('category_id')
                            ->relationship('category', 'name_en')
                            ->searchable()
                            ->preload(),
                        Select::make('tags')
                            ->relationship('tags', 'name_en')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        FileUpload::make('featured_image')
                            ->label('Featured image')
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(5120)
                            ->rules(['image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp'])
                            ->disk('public')
                            ->directory('posts/featured')
                            ->visibility('public')
                            ->columnSpanFull(),
                        Toggle::make('is_featured')
                            ->visible(fn (): bool => auth()->user()?->can('posts.publish') ?? false),
                        Toggle::make('is_breaking')
                            ->visible(fn (): bool => auth()->user()?->can('posts.publish') ?? false),
                    ]),
                Section::make('Language versions')
                    ->description('Add an English and/or Urdu version. Slugs must be unique within each language.')
                    ->schema([
                        Repeater::make('translations')
                            ->relationship()
                            ->minItems(1)
                            ->maxItems(2)
                            ->reorderable(false)
                            ->addActionLabel('Add language version')
                            ->schema([
                                Select::make('locale')
                                    ->options([
                                        'en' => 'English',
                                        'ur' => 'Urdu',
                                    ])
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(
                                        table: 'post_translations',
                                        column: 'slug',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('locale', $get('locale')),
                                    ),
                                TextInput::make('meta_title')
                                    ->maxLength(255),
                                Textarea::make('excerpt')
                                    ->maxLength(1000)
                                    ->columnSpanFull(),
                                MarkdownEditor::make('body')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('meta_description')
                                    ->maxLength(320)
                                    ->columnSpanFull(),
                                TextInput::make('canonical_url')
                                    ->url()
                                    ->maxLength(2048),
                                TextInput::make('image_alt')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
