<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtraResource\Pages;
use App\Filament\Resources\ExtraResource\RelationManagers;
use App\Models\Extra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Importaciones añadidas para los campos
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class ExtraResource extends Resource
{
    protected static ?string $model = Extra::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle'; // Cambiado para que sea más intuitivo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('precio')
                    ->required()
                    ->numeric()
                    ->prefix('€'), // Asumo que la moneda es Euros
                Textarea::make('descripcion')
                    ->columnSpanFull(), // Ocupa todo el ancho
                Toggle::make('es_por_dia')
                    ->label('¿El precio es por día?')
                    ->helperText('Si se marca, el precio se multiplicará por el número de días de la reserva.')
                    ->default(false), // Coincide con tu migración
                Toggle::make('es_por_alquiler')
                    ->label('¿El precio es por alquiler?')
                    ->helperText('Si se marca, el precio se cobrará una sola vez por toda la reserva.')
                    ->default(true), // Coincide con tu migración
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('precio')
                    ->money('EUR') // Formato de moneda
                    ->sortable(),
                IconColumn::make('es_por_dia')
                    ->label('Por Día')
                    ->boolean(),
                IconColumn::make('es_por_alquiler')
                    ->label('Por Alquiler')
                    ->boolean(),
                TextColumn::make('descripcion')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true), // Oculta por defecto, pero visible
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
            'index' => Pages\ListExtras::route('/'),
            'create' => Pages\CreateExtra::route('/create'),
            'edit' => Pages\EditExtra::route('/{record}/edit'),
        ];
    }
}