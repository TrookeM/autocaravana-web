<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampervanResource\Pages;
use App\Filament\Resources\CampervanResource\RelationManagers;
use App\Models\Campervan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
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
                // Columna izquierda
                Forms\Components\Section::make('Información Principal')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre Modelo')
                            ->required(),

                        RichEditor::make('description')
                            ->label('Descripción Detallada')
                            ->columnSpanFull(), // Ocupa todo el ancho
                    ])->columnSpan(2), // Esta sección ocupa 2/3

                // Columna derecha
                Forms\Components\Section::make('Configuración')
                    ->schema([
                        TextInput::make('price_per_night')
                            ->label('Precio por noche')
                            ->numeric()
                            ->prefix('€') // Añade el símbolo del euro
                            ->required(),

                        Toggle::make('is_visible')
                            ->label('Visible para alquilar')
                            ->default(true)
                            ->helperText('Si está apagado, no aparecerá en la web pública'),
                    ])->columnSpan(1), // Esta sección ocupa 1/3
            ])->columns(3); // El formulario se divide en 3 columnas
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre Modelo')
                    ->searchable(), // Añade búsqueda por nombre

                TextColumn::make('price_per_night')
                    ->label('Precio/noche')
                    ->money('EUR') // Formatea como dinero
                    ->sortable(), // Permite ordenar por precio

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(), // Muestra un icono (tick/cruz) 
                TextColumn::make('created_at')
                    ->label('Fecha de alta')
                    ->dateTime('d/m/Y') // Formato de fecha
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculta por defecto
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
