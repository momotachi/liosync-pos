<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->partnership('user', 'name')
                    ->label('Cashier')
                    ->disabled(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->options(['cash' => 'Cash', 'qris' => 'Qris', 'debit' => 'Debit'])
                    ->default('cash')
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled'])
                    ->default('completed')
                    ->required(),
            ]);
    }
}
