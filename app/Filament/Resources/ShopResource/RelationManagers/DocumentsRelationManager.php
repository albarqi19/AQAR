<?php

namespace App\Filament\Resources\ShopResource\RelationManagers;

use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    
    protected static ?string $title = 'وثائق المحل';
    
    protected static ?string $modelLabel = 'وثيقة';
    
    protected static ?string $pluralModelLabel = 'الوثائق';

    public function form(Form $form): Form
    {
        return $form
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
                    
                FileUpload::make('file_path')
                    ->label('ملف الوثيقة')
                    ->directory('documents/shops')
                    ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                    ->maxSize(10240) // 10MB
                    ->required()
                    ->downloadable()
                    ->previewable(false)
                    ->columnSpanFull(),
                    
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
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
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
                    
                Tables\Columns\TextColumn::make('file_name')
                    ->label('اسم الملف')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('formatted_file_size')
                    ->label('حجم الملف'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('فعالة')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('نوع الوثيقة')
                    ->options(Document::getDocumentTypes()),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('فعالة'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة وثيقة')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['uploaded_by'] = Auth::id();
                        return $data;
                    }),
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
            ->defaultSort('sort_order');
    }
}
