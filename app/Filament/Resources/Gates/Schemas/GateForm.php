<?php

namespace App\Filament\Resources\Gates\Schemas;

use App\Models\Gate;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Категория')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type')
                    ->label('Тип')
                    ->options([
                        Gate::TYPE_GATE => 'Ворота',
                        Gate::TYPE_WICKET => 'Калитка',
                    ])
                    ->default(Gate::TYPE_GATE)
                    ->required(),
                TextInput::make('name')
                    ->label('Название')
                    ->required(),
                FileUpload::make('image')
                    ->label('Изображение ворот (PNG с прозрачностью)')
                    ->image()
                    ->directory('gates')
                    ->disk('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->maxSize(5120)
                    ->required(),
                Textarea::make('description')
                    ->label('Описание')
                    ->columnSpanFull(),
                KeyValue::make('colors')
                    ->label('Цвета (название → hex)')
                    ->keyLabel('Название цвета')
                    ->valueLabel('HEX код (#ffffff)')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Активны')
                    ->default(true),
                TextInput::make('sort_order')
                    ->label('Порядок сортировки')
                    ->numeric()
                    ->default(0),
            ]);
    }
}
