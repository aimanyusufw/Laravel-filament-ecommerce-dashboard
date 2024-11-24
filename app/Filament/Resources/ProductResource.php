<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Product';

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()->schema([
                    Forms\Components\Section::make('Product Information')
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->maxLength(255)
                                ->required()
                                ->placeholder('Unlimited product')
                                ->live(debounce: 300)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state) {
                                    if (($get('slug') ?? '') !== Str::slug($old)) {
                                        return;
                                    }
                                    $set('slug', Str::slug($state));
                                }),
                            Forms\Components\TextInput::make('slug')
                                ->readOnly()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\Textarea::make('excerpt')
                                ->rows(5)
                                ->columnSpanFull()
                                ->maxLength(255),
                            TiptapEditor::make('description')
                                ->columnSpanFull()
                                ->required(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Prices')
                        ->schema([
                            Forms\Components\TextInput::make('price')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters('.')
                                ->numeric(),
                            Forms\Components\TextInput::make('sale_price')
                                ->prefix('Rp')
                                ->mask(RawJs::make('$money($input)'))
                                ->stripCharacters('.')
                                ->numeric(),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Specifications')
                        ->schema([
                            Forms\Components\TextInput::make('weight')
                                ->numeric()
                                ->default(0),
                            Forms\Components\TextInput::make('stock')
                                ->numeric()
                                ->default(0),
                        ])
                        ->columns(2),

                    Forms\Components\Section::make('Medias')
                        ->schema([
                            CuratorPicker::make('media_id')
                                ->label("Media")
                                ->multiple()
                                ->relationship('productPictures', 'id')
                                ->columnSpanFull(),
                        ])
                        ->columnSpanFull(),
                ])
                    ->columns(['sm' => 2])->columnSpan(2),
                Forms\Components\Section::make("Categories & timestamps")
                    ->schema([
                        Forms\Components\Select::make('Categories')
                            ->multiple()
                            ->relationship("categories", "title"),
                        Forms\Components\Placeholder::make("Creted at")
                            ->content(fn(?Product $record): String => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make("updated at")
                            ->content(fn(?Product $record): String => $record ? $record->updated_at->diffForHumans() : '-')
                    ])
                    ->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('productPictures.path')
                    ->label('Product Image')
                    ->getStateUsing(fn($record) => optional($record->productPictures->first())->path)
                    ->size(50),
                Tables\Columns\TextColumn::make('title')
                    ->limit(0)
                    ->searchable(),
                Tables\Columns\TextColumn::make('excerpt')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('categories.title')
                    ->default("Uncategories")
                    ->badge(),
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateIcon('heroicon-o-gift')
            ->emptyStateDescription('Create product and detail data.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create product')
                    ->url(ProductResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
            ]);;;
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
