<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Filament\Resources\MediaResource\RelationManagers;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('file_name')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('file_path')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('role')
                //     ->maxLength(255)
                //     ->default('admin'),
                // Forms\Components\TextInput::make('type')
                //     ->maxLength(255)
                //     ->default('image'),
                // Forms\Components\TextInput::make('url')
                //     ->maxLength(255),
                // Forms\Components\TextInput::make('ext')
                //     ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('file_path')
                        ->width('100%')
                        ->alignCenter()
                        ->height(150),
                    TextColumn::make('file_name')
                        ->limit(15)
                        ->size('sm')
                        ->alignCenter()
                ])->extraAttributes(["style" => 'position : relative;']),
            ])
            ->defaultSort('created_at', 'desc')
            ->contentGrid([
                'md' => 2,
                'lg' => 4,
                'xl' => 5,
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->after(function (Media $media) {
                        if ($media->file_path) {
                            Storage::disk('public')->delete($media->file_path);
                        }
                    }),
                Action::make('Get Url')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->action(function ($livewire, Media $media) {

                        $livewire->js(
                            'window.navigator.clipboard.writeText("' . $media->url . '");'
                        );

                        return Notification::make()
                            ->title('Image URL copied!')
                            ->color('info')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateIcon('heroicon-o-photo')
            ->emptyStateDescription('Upload media files to see them here.');
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
            'index' => Pages\ListMedia::route('/'),
            // 'create' => Pages\CreateMedia::route('/create'),
            // 'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
