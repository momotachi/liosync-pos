<?php

namespace App\Filament\Resources\StockTransactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StockTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('raw_material_id')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options(['in' => 'In', 'out' => 'Out', 'adjustment' => 'Adjustment'])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('reference_id')
                    ->numeric()
                    ->default(null),
            ]);
    }
}
