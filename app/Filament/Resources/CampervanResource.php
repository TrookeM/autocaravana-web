<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampervanResource\Pages;
use App\Models\Campervan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload; // 👈 Importación necesaria
use Filament\Forms\Components\Section;   // 👈 Importación necesaria
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class CampervanResource extends Resource
{
    protected static ?string $model = Campervan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. Columna izquierda (Información Principal y Descripción) - Ocupa 2/3
                Forms\Components\Group::make() // Usamos Group para envolver secciones
                    ->schema([
                        Forms\Components\Section::make('Información Principal')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre Modelo')
                                    ->required(),

                                RichEditor::make('description')
                                    ->label('Descripción Detallada')
                                    ->columnSpanFull(),
                            ]),
                        
                        // 2. NUEVA SECCIÓN DE IMÁGENES (Ocupa el ancho de 2 columnas)
                        Forms\Components\Section::make('Gestión de Imágenes')
                            ->description('Sube la imagen principal para la lista y las imágenes de la galería de detalle.')
                            ->schema([
                                // CAMPO 1: IMAGEN PRINCIPAL (Guarda en 'main_image_path')
                                FileUpload::make('main_image_path')
                                    ->label('Imagen Principal (Foto de Portada)')
                                    ->disk('public') // Usa el disco 'public'
                                    ->directory('campervan_images') // Carpeta dentro de storage/app/public
                                    ->image() // Validación de que es una imagen
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('675')
                                    ->columnSpan(1),

                                // CAMPO 2: IMÁGENES SECUNDARIAS (Guarda en 'secondary_images_json')
                                FileUpload::make('secondary_images_json') // Debe coincidir con el campo casteado en el modelo
                                    ->label('Galería de Imágenes Secundarias')
                                    ->disk('public') 
                                    ->directory('campervan_images') 
                                    ->multiple() // Permite múltiples archivos
                                    ->maxFiles(5) // Límite de 5 imágenes
                                    ->image()
                                    ->reorderable() // Permite reordenar
                                    ->columnSpan(1),
                            ])->columns(2), // Organiza los dos campos de subida en dos columnas
                    ])->columnSpan(2), // Este grupo de secciones ocupa 2/3 del formulario principal

                // 3. Columna derecha (Configuración) - Ocupa 1/3
                Forms\Components\Section::make('Configuración')
                    ->schema([
                        TextInput::make('price_per_night')
                            ->label('Precio por noche')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        Toggle::make('is_visible')
                            ->label('Visible para alquilar')
                            ->default(true)
                            ->helperText('Si está apagado, no aparecerá en la web pública'),
                    ])->columnSpan(1),
            ])->columns(3); // El formulario principal se divide en 3 columnas
    }

    // ... (El resto de los métodos table, getRelations, getPages permanecen sin cambios)
    
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('main_image_path') // 👈 Nuevo campo de tabla para ver la ruta
                    ->label('Portada')
                    ->limit(25),
                TextColumn::make('name')
                    ->label('Nombre Modelo')
                    ->searchable(),
                TextColumn::make('price_per_night')
                    ->label('Precio/noche')
                    ->money('EUR')
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(), 
                TextColumn::make('created_at')
                    ->label('Fecha de alta')
                    ->dateTime('d/m/Y')
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCampervans::route('/'),
            'create' => Pages\CreateCampervan::route('/create'),
            'edit' => Pages\EditCampervan::route('/{record}/edit'),
        ];
    }
}