<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use App\Models\Building;
use App\Models\Shop;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'الوثائق والملفات';
    
    protected static ?string $pluralModelLabel = 'الوثائق والملفات';
    
    protected static ?string $modelLabel = 'وثيقة';
    
    protected static ?string $navigationGroup = 'إدارة المباني والمحلات';
    
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الوثيقة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('عنوان الوثيقة')
                                    ->required()
                                    ->maxLength(255),
                                    
                                Forms\Components\Select::make('document_type')
                                    ->label('نوع الوثيقة')
                                    ->options(Document::getDocumentTypes())
                                    ->required()
                                    ->searchable(),
                            ]),
                            
                        Forms\Components\Textarea::make('description')
                            ->label('وصف الوثيقة')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('ربط الوثيقة')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('documentable_type')
                                    ->label('نوع المرفق')
                                    ->options([
                                        'App\\Models\\Building' => 'مبنى',
                                        'App\\Models\\Shop' => 'محل',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn (Forms\Set $set) => $set('documentable_id', null)),
                                    
                                Forms\Components\Select::make('documentable_id')
                                    ->label('المرفق')
                                    ->options(function (Forms\Get $get) {
                                        $type = $get('documentable_type');
                                        
                                        if ($type === 'App\\Models\\Building') {
                                            return Building::query()
                                                ->with('district.city')
                                                ->get()
                                                ->mapWithKeys(function ($building) {
                                                    return [$building->id => "{$building->name} - {$building->district?->city?->name}"];
                                                });
                                        }
                                        
                                        if ($type === 'App\\Models\\Shop') {
                                            return Shop::query()
                                                ->with('building.district.city')
                                                ->get()
                                                ->mapWithKeys(function ($shop) {
                                                    return [$shop->id => "محل {$shop->shop_number} - {$shop->building?->name}"];
                                                });
                                        }
                                        
                                        return [];
                                    })
                                    ->required()
                                    ->searchable(),
                            ]),
                    ]),
                    
                Forms\Components\Section::make('ملف الوثيقة')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('ملف الوثيقة')
                            ->directory('documents')
                            ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240) // 10MB
                            ->required()
                            ->downloadable()
                            ->previewable(false)
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $file = $state->getClientOriginalName();
                                    $size = $state->getSize();
                                    $mime = $state->getMimeType();
                                    
                                    $set('file_name', $file);
                                    $set('file_size', $size);
                                    $set('mime_type', $mime);
                                }
                            }),
                            
                        Forms\Components\Hidden::make('file_name'),
                        Forms\Components\Hidden::make('file_size'),
                        Forms\Components\Hidden::make('mime_type'),
                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(Auth::id()),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('ترتيب العرض')
                                    ->numeric()
                                    ->default(0),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->label('فعالة')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('عنوان الوثيقة')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('document_type')
                    ->label('نوع الوثيقة')
                    ->formatStateUsing(fn (string $state): string => Document::getDocumentTypes()[$state] ?? $state)
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('documentable_type')
                    ->label('مرفق بـ')
                    ->formatStateUsing(function (string $state): string {
                        return match ($state) {
                            'App\\Models\\Building' => 'مبنى',
                            'App\\Models\\Shop' => 'محل',
                            default => $state,
                        };
                    })
                    ->badge()
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('documentable.name')
                    ->label('اسم المرفق')
                    ->formatStateUsing(function ($record) {
                        if ($record->documentable_type === 'App\\Models\\Building') {
                            return $record->documentable?->name ?? '-';
                        } elseif ($record->documentable_type === 'App\\Models\\Shop') {
                            return "محل {$record->documentable?->shop_number}" ?? '-';
                        }
                        return '-';
                    })
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('file_name')
                    ->label('اسم الملف')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) > 30) {
                            return $state;
                        }
                        return null;
                    }),
                    
                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('حجم الملف')
                    ->sortable('file_size'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعالة')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('رافع الوثيقة')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('نوع الوثيقة')
                    ->options(Document::getDocumentTypes()),
                    
                Tables\Filters\SelectFilter::make('documentable_type')
                    ->label('مرفق بـ')
                    ->options([
                        'App\\Models\\Building' => 'مبنى',
                        'App\\Models\\Shop' => 'محل',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعالة'),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('تحميل')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record): string => $record->getFileUrl())
                    ->openUrlInNewTab(),
                    
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
}
