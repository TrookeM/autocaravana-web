<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking; // Importar Booking
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

// Imports para la tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn; // 1. Importar SelectColumn
use Filament\Tables\Actions\Action; // 2. Importar Action

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Icono de calendario

    public static function form(Form $form): Form
    {
        // El formulario de creación/edición puede ser simple por ahora
        return $form
            ->schema([
                Forms\Components\Select::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->required(),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nombre Cliente')
                    ->required(),
                Forms\Components\TextInput::make('customer_email')
                    ->label('Email Cliente')
                    ->email()
                    ->required(),
                Forms\Components\DatePicker::make('start_date')
                    ->label('Check-in')
                    ->required(),
                Forms\Components\DatePicker::make('end_date')
                    ->label('Check-out')
                    ->required(),
                Forms\Components\TextInput::make('total_price')
                    ->label('Precio Total')
                    ->numeric()
                    ->prefix('€')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente', // Si decides cambiar el 'confirmed' por defecto
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->default('confirmed')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID Reserva')
                    ->sortable(),
                TextColumn::make('campervan.name') // Muestra el nombre usando la relación
                    ->label('Autocaravana')
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->label('Check-in')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Check-out')
                    ->date('d/m/Y')
                    ->sortable(),
                
                // 3. COLUMNA DE ESTADO (Editable)
                SelectColumn::make('status')
                    ->label('Estado')
                    ->options([
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->sortable(),
                
                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // 4. ACCIÓN DE CANCELAR
                Action::make('Cancelar')
                    ->action(fn (Booking $record) => $record->update(['status' => 'cancelled']))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation() // Pide confirmación
                    ->hidden(fn (Booking $record) => $record->status === 'cancelled'), // Oculta si ya está cancelada
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
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }    
}
