<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction as ActionsCreateAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()->schema([
                    Forms\Components\FileUpload::make('profile_image')
                        ->maxSize(5120)
                        ->disk('public')
                        ->imageCropAspectRatio('1:1')
                        ->imageEditor()
                        ->directory('users')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->maxLength(255)
                        ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                        ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                        ->visible(fn($livewire) => $livewire instanceof Pages\CreateUser || $livewire instanceof Pages\EditUser)
                        ->dehydrated(fn($state) => filled($state)),
                    Forms\Components\TextInput::make('confirm_password')
                        ->password()
                        ->required(
                            fn($livewire) =>
                            $livewire instanceof Pages\CreateUser ||
                                ($livewire instanceof Pages\EditUser && filled($livewire->data['password']))
                        )
                        ->same('password')
                        ->revealable()
                        ->maxLength(255),
                    PhoneInput::make('phone_number'),
                    Forms\Components\DateTimePicker::make('email_verified_at'),
                    Forms\Components\CheckboxList::make('technologies')
                        ->relationship(name: 'roles', titleAttribute: 'name')
                        ->columns(2)
                ])->columns(['sm' => 2])->columnSpan(2),
                Forms\Components\Section::make("Time Stamps")->schema(
                    [
                        Forms\Components\Placeholder::make('created_at')
                            ->content(fn(?User $record) => $record ? $record->created_at->diffForHumans() : '-'),
                        Forms\Components\Placeholder::make('updated_at')
                            ->content(fn(?User $record) => $record ? $record->updated_at->diffForHumans() : '-')
                    ]
                )->columnSpan(1)
            ])->columns(3);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->where('id', '!=', Auth::id()))
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image')
                    ->circular()
                    ->defaultImageUrl(asset('/images/placeholder.svg')),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->icon('heroicon-m-envelope'),
                PhoneColumn::make('phone_number')
                    ->icon('heroicon-m-phone'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateDescription('Create user and detail data.')
            ->emptyStateActions([
                Action::make('create')
                    ->label('Create user')
                    ->url(UserResource::getUrl('create'))
                    ->icon('heroicon-m-plus')
                    ->button(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
