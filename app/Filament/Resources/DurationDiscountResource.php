<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DurationDiscountResource\Pages;
use App\Filament\Resources\DurationDiscountResource\RelationManagers;
use App\Models\DurationDiscount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Imports añadidos
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class DurationDiscountResource extends Resource
{
    protected static ?string $model = DurationDiscount::class;

    // --- CONFIGURACIÓN DE NAVEGACIÓN ---
    
    // ==========================================================
    // ¡ICONO CORREGIDO!
    // ==========================================================
    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent'; // <-- Icono corregido

    protected static ?string $modelLabel = 'Descuento por Duración';
    protected static ?string $pluralModelLabel = 'Descuentos por Duración';
    // (Lo agrupamos con las Reglas de Precio, si existe ese grupo)
    protected static ?string $navigationGroup = 'Gestión de Precios'; 
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('min_nights')
                    ->label('Noches Mínimas')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Noches mínimas (incluidas) para aplicar el descuento. Ej: 7'),

                TextInput::make('max_nights')
                    ->label('Noches Máximas (Opcional)')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Dejar vacío para "X noches o más". Ej: (Min: 21, Max: vacío) = 21+ noches.')
                    // Validación: max_nights debe ser mayor que min_nights si existe
                    ->gt('min_nights') 
                    ->nullable(),

                TextInput::make('percentage_discount')
                    ->label('Porcentaje de Descuento')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->helperText('Poner 10 para un 10% de descuento.')
                    ->minValue(0)
                    ->maxValue(100),
            ])
            ->columns(3); // Mostramos los 3 campos en una sola fila
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_nights')
                    ->label('Noches Mínimas')
                    ->sortable(),
                
                TextColumn::make('max_nights')
                    ->label('Noches Máximas')
                    ->sortable()
                    // Si es nulo, muestra "Sin límite"
                    ->formatStateUsing(fn ($state) => $state ?? 'Sin límite'), 

                TextColumn::make('percentage_discount')
                    ->label('Descuento')
                    ->suffix('%')
                    ->sortable(),
                
                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Habilitamos la acción de borrar
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
            'index' => Pages\ListDurationDiscounts::route('/'),
            'create' => Pages\CreateDurationDiscount::route('/create'),
            'edit' => Pages\EditDurationDiscount::route('/{record}/edit'),
        ];
    }
}