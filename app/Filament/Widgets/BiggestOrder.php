<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class BiggestOrder extends BaseWidget
{
    protected static ?int $sort = 6;
    protected static ?string $heading = 'Order Terbesar';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->orderBy('total_price', 'desc')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('change_amount')
                    ->label('Sisa Pembayaran')
                    ->money('IDR. ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn (Order $record): string => ($record->notes ?? '-'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->money('IDR. ')
                    ->sortable(),
            ]);
    }
}
