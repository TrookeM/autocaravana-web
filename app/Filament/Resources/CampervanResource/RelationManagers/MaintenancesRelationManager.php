<?php

namespace App\Filament\Resources\CampervanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
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

class MaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';
    
    // --- Metadata Añadida ---
    protected static ?string $modelLabel = 'Mantenimiento';
    protected static ?string $pluralModelLabel = 'Mantenimientos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // NOTA: No necesitamos 'campervan_id', Filament lo añade automáticamente.
                
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
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service_type')
            ->columns([
                // NOTA: No necesitamos la columna 'Campervan', porque ya estamos en ella.
                
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // Botón para "Nuevo Mantenimiento"
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
            ->defaultSort('date', 'desc'); // Ordenar por fecha más reciente
    }
}