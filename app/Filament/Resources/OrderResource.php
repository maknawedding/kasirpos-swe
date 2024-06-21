<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;
use Closure;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class OrderResource extends Resource
{

    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Utama')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('no_tenant')
                                    ->label('Nomor Tenant')
                                    ->required(),
                                Forms\Components\TextInput::make('notes')
                                    ->label('Rekanan')
                                    ->placeholder('The Sultan : Makna Wedding')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Info Tambahan')
                            ->schema([
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('phone')
                                    ->tel()
                                    ->maxLength(255),
                                Forms\Components\DatePicker::make('birthday'),
                            ]),
                    ]),
                Forms\Components\Section::make('Produk dipesan')->schema([
                    self::getItemsRepeater(),
                ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Pembayaran')
                        ->schema([
                            Forms\Components\TextInput::make('total_price')
                                ->numeric()
                                ->readOnly(),
                            Forms\Components\Select::make('payment_method_id')
                                ->required()
                                ->label('Metode Pembayaran')
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                    $paymentMethod = PaymentMethod::find($state);
                                    $set('is_cash', $paymentMethod?->is_cash ?? false);

                                    $set('change_amount', 0);
                                    $set('paid_amount', $get('total_price'));


                                })
                                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                                    $paymentMethod = PaymentMethod::find($state);
                                    if (!$paymentMethod?->is_cash) {
                                        $set('paid_amount', $get('total_price'));
                                        $set('change_amount', 0);
                                    }

                                    $set('is_cash', $paymentMethod?->is_cash ?? false);
                                })
                                ->relationship('paymentmethod', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('id', 'asc')),
                            Forms\Components\Hidden::make('is_cash')
                                ->dehydrated(false),
                            
                            Forms\Components\TextInput::make('promo')
                                ->numeric()
                                ->label('Promo Makna'),
                            Forms\Components\TextInput::make('paid_amount')
                                ->numeric()
                                ->reactive()
                                ->label('Nominal Bayar')
                                ->readOnly(fn (Forms\Get $get) => $get('is_cash') == false)

                                ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {

                                    self::updateExchangePaid($get, $set);
                                }),
                            Forms\Components\TextInput::make('change_amount')
                                ->numeric()
                                ->label('Sisa Pembayaran')
                                ->readOnly()

                    
                            ]),
                    ]),
                    Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Waktu Pembayaran')
                            ->schema([
                                Forms\Components\DatePicker::make('issued_date')
                                    ->required(),
                                Forms\Components\DatePicker::make('due_date')
                                    ->required(),
                                Forms\Components\DatePicker::make('paid_date'),
                                Forms\Components\TextInput::make('keterangan')
                                    ->placeholder('Jika ada barter dll'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn (Order $record): string => ($record->notes ?? '-'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('no_tenant')
                    ->label('No. Tenant')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total (Rp)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Uang Dibayar (Rp)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('promo')
                    ->label('Promo (Rp)')
                    ->numeric()
                    ->default(0)
                    ->sortable(),
                Tables\Columns\TextColumn::make('change_amount')
                    ->label('Sisa Pembayaran (Rp)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issued_date')
                    ->date(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
                Tables\Columns\TextColumn::make('paid_date')
                    ->date(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Add an action to print invoice
                // This action will redirect user to the invoice page
                //
                // @return \Filament\Tables\Actions\Action
                \Filament\Tables\Actions\Action::make('View')
                    ->icon('heroicon-s-magnifying-glass-circle')
                    ->url(fn (Order $record): string => route('preview-invoice', $record))                    
                    ->openUrlInNewTab()
                    ->color('success')
                    ->form([]),
                // \Filament\Tables\Actions\Action::make('Download')
                //     ->icon('heroicon-o-printer')
                //     ->url(fn (Order $record): string => route('download-invoice', $record))                    
                //     ->openUrlInNewTab()
                //     ->color('danger')
                //     ->form([]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getItemsRepeater(): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->options(Product::query()->where('stock', '>', 1)->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get, $state) {
                        $product = Product::find($state);
                        $set('unit_price', $product?->price ?? 0);
                        $set('stock', $product?->stock ?? 0);

                    })
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        $set('unit_price', $product?->price ?? 0);
                        $set('stock', $product?->stock ?? 0);
                        $quantity = $get('quantity') ?? 1; // Get quantity or default to 1
                        $stock = $get('stock');
                        self::updateTotalPrice($get, $set);


                    })

                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric()
                    ->default(1)
                    ->columnSpan([
                        'md' => 1,
                    ])
                    ->minValue(1)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $stock = $get('stock');
                        if ($state > $stock) {
                            $set('quantity', $stock);
                            Notification::make()
                                ->title('Stock tidak mencukupi')
                                ->warning()
                                ->send();
                        }
                        self::updateTotalPrice($get, $set);
                    }),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 1,
                    ]),
                Forms\Components\TextInput::make('unit_price')
                    ->label('Unit Price')
                    ->disabled()
                    ->dehydrated()
                    ->numeric()
                    ->required()
                    ->columnSpan([
                        'md' => 3,
                    ]),
            ])
            ->extraItemActions([
                Action::make('openProduct')
                    ->tooltip('Open product')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(function (array $arguments, Repeater $component): ?string {
                        $itemData = $component->getRawItemState($arguments['item']);
                        $product = Product::find($itemData['product_id']);
                        if (! $product) {
                            return null;
                        }
                        return ProductResource::getUrl('edit', ['record' => $product]);
                    }, shouldOpenInNewTab: true)
                    ->hidden(fn (array $arguments, Repeater $component): bool => blank($component->getRawItemState($arguments['item'])['product_id'])),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->live()
            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                self::updateTotalPrice($get, $set);
            });
    }

    protected static function updateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $selectedProducts = collect($get('items'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));

        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
        $total = $selectedProducts->reduce(function ($total, $product) use ($prices) {
            return $total + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);

        $set('total_price', $total);
    }

    protected static function updateExchangePaid(Forms\Get $get, Forms\Set $set): void
    {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $totalPrice = (int) $get('total_price') ?? 0;
        $promoPrice = (int) $get('promo') ?? 0;
        $exchangePaid = $totalPrice - $paidAmount - $promoPrice;
        $set('change_amount', $exchangePaid);
    }

}
