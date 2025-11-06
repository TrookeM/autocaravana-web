<?php

namespace App\Filament\Resources\CampervanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Imports añadidos
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GuidesRelationManager extends RelationManager
{
    protected static string $relationship = 'guides';

    // Traducciones para la interfaz
    protected static ?string $title = 'Guías y Manuales';
    protected static ?string $modelLabel = 'Guía';
    protected static ?string $pluralModelLabel = 'Guías';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(), // Ocupa todo el ancho

                Forms\Components\RichEditor::make('content')
                    ->label('Contenido (Opcional)')
                    ->columnSpanFull(), // Ocupa todo el ancho

                Forms\Components\FileUpload::make('pdf_path')
                    ->label('Archivo PDF (Opcional)')
                    ->directory('campervan-guides') // Se guardará en storage/app/public/campervan-guides
                    ->disk('public') // Usamos el disco público
                    ->visibility('public') // El archivo será públicamente accesible
                    ->acceptedFileTypes(['application/pdf'])
                    ->preserveFilenames()
                    ->columnSpanFull(), // Ocupa todo el ancho
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),
                
                // Columna de icono para ver rápido si tiene PDF
                IconColumn::make('pdf_path')
                    ->label('PDF')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle') // Icono si 'pdf_path' no está vacío
                    ->falseIcon('heroicon-o-x-circle') // Icono si 'pdf_path' está vacío
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última modificación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultable
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    // Añadimos un hook para borrar el PDF después de eliminar el registro
                    ->after(fn ($record) => static::deletePdf($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        // Hacemos lo mismo para el borrado en masa
                        ->after(fn ($records) => $records->each(fn($record) => static::deletePdf($record))),
                ]),
            ]);
    }

    /**
     * Helper para borrar el PDF del almacenamiento.
     */
    protected static function deletePdf(Model $record): void
    {
        if ($record->pdf_path) {
            Storage::disk('public')->delete($record->pdf_path);
        }
    }
}