<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// --- Imports Añadidos ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    // --- Metadata Añadida ---
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver'; // Icono de mantenimiento
    protected static ?string $navigationGroup = 'Gestión de Campervans'; // Agrupado
    protected static ?string $modelLabel = 'Mantenimiento';
    protected static ?string $pluralModelLabel = 'Mantenimientos';
    protected static ?int $navigationSort = 4; // Orden en el menú

    /**
     * ==========================================================
     * MÉTODO FORM() ACTUALIZADO
     * ==========================================================
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Campervan'),
                
                DatePicker::make('date')
                    ->label('Fecha del Mantenimiento')
                    ->required()
                    ->default(now())
                    ->native(false), // Para usar el selector de Filament
                
                TextInput::make('service_type')
                    ->label('Tipo de Servicio')
                    ->placeholder('Ej: Cambio de aceite, Revisión de frenos...')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2), // Ocupa más espacio
                
                TextInput::make('cost')
                    ->label('Coste')
                    ->numeric()
                    ->prefix('€')
                    ->default(0.00)
                    ->nullable(),
                
                Textarea::make('notes')
                    ->label('Notas Adicionales')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2); // Organiza el formulario en 2 columnas
    }

    /**
     * ==========================================================
     * MÉTODO TABLE() ACTUALIZADO
     * ==========================================================
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('campervan.name')
                    ->label('Campervan')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('service_type')
                    ->label('Servicio')
                    ->searchable()
                    ->limit(40),
                
                TextColumn::make('date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
                
                TextColumn::make('cost')
                    ->label('Coste')
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto
            ])
            ->filters([
                // Filtro para ver mantenimientos por campervan
                Tables\Filters\SelectFilter::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->label('Campervan')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Añadido para poder borrar
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc'); // Ordenar por fecha más reciente
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }
}