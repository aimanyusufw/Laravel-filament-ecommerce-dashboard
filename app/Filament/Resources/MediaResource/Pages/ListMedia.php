<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use App\Models\Media;
use Filament\Actions;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('image')
                ->label("Upload Media")
                ->form([
                    Forms\Components\FileUpload::make('image')
                        ->disk('public')
                        ->directory('medias')
                        ->maxSize(5120)
                        ->imageEditorAspectRatios([
                            null,
                            '16:9',
                            '4:3',
                            '1:1',
                        ])
                        ->image()
                        ->getUploadedFileNameForStorageUsing(
                            fn(TemporaryUploadedFile $file): string => Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '-'  . now()->timestamp . '.' . $file->getClientOriginalExtension()
                        )
                        ->imageEditor()
                        ->required()
                ])
                ->action(function (array $data): void {
                    $record = new \App\Models\Media;

                    if (isset($data['image'])) {
                        $filePath = $data['image'];
                        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
                        $extension = explode('.', $data['image'])[1];
                        $url = asset(Storage::url($filePath));
                        $record->file_name = $fileName;
                        $record->file_path = $filePath;
                        $record->url = $url;
                        $record->ext = $extension;
                        $record->role = 'admin';
                        $record->type = 'image';
                        $record->save();
                    }
                })
        ];
    }
}
