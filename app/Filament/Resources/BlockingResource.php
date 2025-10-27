<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockingResource\Pages;
use App\Models\Blocking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Imports necesarios para los campos y columnas
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;

class BlockingResource extends Resource
{
    protected static ?string $model = Blocking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Icono actualizado
    protected static ?string $navigationGroup = 'Gestión de Campervans'; // Agrupar con Campervans
    protected static ?string $modelLabel = 'Bloqueo de Mantenimiento';
    protected static ?string $pluralModelLabel = 'Bloqueos de Mantenimiento';
    protected static ?int $navigationSort = 3; // Orden en el menú

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('campervan_id')
                    ->relationship('campervan', 'name') // Usa la relación 'campervan' y muestra el campo 'name'
                    ->searchable() // Permite buscar campervans
                    ->preload() // Precarga las opciones para mejorar la experiencia
                    ->required()
                    ->label('Campervan'), // Etiqueta clara
                DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->required()
                    ->native(false) // Usar el selector de fecha de Filament en lugar del nativo del navegador
                    ->minDate(now()->startOfDay()) // No permitir seleccionar fechas pasadas
                    ->displayFormat('d/m/Y'), // Formato de visualización
                DatePicker::make('end_date')
                    ->label('Fecha de Fin (incluida)') // Aclarar que se incluye
                    ->required()
                    ->native(false)
                     // La fecha de fin debe ser igual o posterior a la fecha de inicio seleccionada
                    ->minDate(fn (Forms\Get $get): ?string => $get('start_date') ? Carbon::parse($get('start_date'))->startOfDay() : now()->startOfDay())
                    ->displayFormat('d/m/Y'),
                Textarea::make('reason')
                    ->label('Motivo del Bloqueo (Opcional)')
                    ->maxLength(65535) // Límite del campo TEXT
                    ->columnSpanFull(), // Ocupa todo el ancho
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campervan.name') // Acceder al nombre a través de la relación
                    ->label('Campervan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Fecha de Inicio')
                    ->date('d/m/Y') // Formatear fecha
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fecha de Fin')
                    ->date('d/m/Y') // Formatear fecha
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(50) // Limitar longitud en la tabla
                    ->tooltip(fn (Blocking $record): ?string => $record->reason), // Mostrar completo al pasar el ratón
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i') // Formato de fecha y hora
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultable por defecto
            ])
            ->filters([
                // Puedes añadir filtros si lo necesitas, por ejemplo, por campervan
                Tables\Filters\SelectFilter::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->label('Campervan')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc'); // Ordenar por defecto por fecha de inicio más reciente
    }

    public static function getRelations(): array
    {
        return [
            // Si necesitas añadir relation managers en el futuro, irían aquí
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlockings::route('/'),
            'create' => Pages\CreateBlocking::route('/create'),
            'edit' => Pages\EditBlocking::route('/{record}/edit'),
        ];
    }
}

