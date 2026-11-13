<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name_en')
                            ->label('English name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ur')
                            ->label('Urdu name')
                            ->required()
                            ->maxLength(255)
                            ->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->default(true),
                    ]),
            ]);
    }
}
