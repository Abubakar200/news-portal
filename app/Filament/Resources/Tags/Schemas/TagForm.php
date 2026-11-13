<?php

namespace App\Filament\Resources\Tags\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tag')
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
                    ]),
            ]);
    }
}
