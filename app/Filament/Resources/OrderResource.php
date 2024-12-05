<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\View;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\RawJs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Order detail')
                            ->schema([
                                Forms\Components\TextInput::make('subtotal')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_cost')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('tax')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\TextInput::make('grand_total')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->readOnly(),
                                Forms\Components\Select::make('status')
                                    ->options([
                                        '1' => 'Pending payment',
                                        '2' => 'Processing',
                                        '3' => 'Ready to ship',
                                        '4' => 'Shipped',
                                        '5' => 'Completed',
                                        '6' => 'Cancelled',
                                    ]),
                                Forms\Components\TextInput::make('order_date')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('order_code')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('resi_code')
                                    ->string()
                                    ->maxLength(255),
                                Forms\Components\Textarea::make('notes')
                                    ->rows(5)
                                    ->readOnly(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Shipping Address detail')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_address_detail.title')
                                    ->label("Title")
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_address_detail.shipping_name')
                                    ->label("Name")
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_address_detail.shipping_phone')
                                    ->label("Phone")
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_address_detail.shipping_province.name')
                                    ->label("Province")
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_address_detail.shipping_city.name')
                                    ->label("City")
                                    ->readOnly(),
                                Forms\Components\Textarea::make('shipping_address_detail.shipping_address')
                                    ->rows(4)
                                    ->maxLength(125)
                                    ->label("Address")
                                    ->readOnly(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Shipping detail')
                            ->schema([
                                Forms\Components\TextInput::make('shipping_detail.service')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_detail.description')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_detail.etd')
                                    ->readOnly(),
                                Forms\Components\TextInput::make('shipping_detail.cost')
                                    ->prefix('Rp')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->stripCharacters(',')
                                    ->numeric()
                                    ->readOnly(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Invoice')
                            ->schema([
                                Forms\Components\Section::make("invoice")
                                    ->relationship('invoice')
                                    ->schema([
                                        Forms\Components\TextInput::make('payment_url')
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('amount')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('paid_at')
                                            ->readOnly(),
                                        Forms\Components\Select::make('status')
                                            ->disabled()
                                            ->options([
                                                '1' => 'Unpaid',
                                                '2' => 'Paid',
                                                '3' => 'Expired',
                                                '4' => 'Cancle',
                                            ]),
                                    ])->columns(2),
                            ]),
                        Forms\Components\Tabs\Tab::make('Product items')
                            ->schema([
                                Forms\Components\Repeater::make('orderItems')
                                    ->relationship('orderItems')
                                    ->schema([
                                        Forms\Components\TextInput::make('product_title')
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('qty')
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('product_price')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            ->readOnly(),
                                        Forms\Components\TextInput::make('sub_total')
                                            ->prefix('Rp')
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->numeric()
                                            ->readOnly(),
                                        Forms\Components\Placeholder::make('image')
                                            ->label('Product Thumbnail')
                                            ->content(
                                                function ($record) {
                                                    return new HtmlString('<img src="' . $record->product_thumbnail . '" style="max-width: 100%; height: auto; border-radius: 5px;">');
                                                }
                                            ),
                                    ])->columns(['sm' => 2])->columnSpanFull()
                            ]),
                    ])->columns(
                        ['sm' => 2]
                    )->columnSpan(2),
                Forms\Components\Section::make("Time stamps")->schema([
                    Forms\Components\Placeholder::make("created_at")
                        ->content(fn(?Order $record): String => $record ? $record->created_at->diffForHumans() : "-"),
                    Forms\Components\Placeholder::make("updated_at")
                        ->content(fn(?Order $record): String => $record ? $record->updated_at->diffForHumans() : "-")
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            '1' => 'Pending payment',
                            '2' => 'Processing',
                            '3' => 'Ready to ship',
                            '4' => 'Shipped',
                            '5' => 'Completed',
                            '6' => 'Cancelled',
                            default => 'Unknown',
                        };
                    })
                    ->badge()
                    ->colors([
                        'primary' => '1',
                        'gray' => '2',
                        'warning' => '3',
                        'info' => '4',
                        'success' => '5',
                        'danger' => '6'
                    ]),
                Tables\Columns\TextColumn::make('grand_total')
                    ->prefix('Rp ')
                    ->money('Rp', locale: 'id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->limit(50)
                    ->searchable()
                    ->default("-"),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateIcon('heroicon-o-truck')
            ->emptyStateDescription('You just only can update and delete data.');
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
            // 'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
