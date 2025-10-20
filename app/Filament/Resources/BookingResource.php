<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Selector para elegir la autocaravana
                Select::make('campervan_id')
                    ->label('Autocaravana')
                    ->relationship('campervan', 'name') // Muestra el 'name' de la 'campervan'
                    ->required(),

                // Selector de fechas
                DatePicker::make('start_date')
                    ->label('Desde')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Hasta')
                    ->required(),

                // Datos del cliente
                TextInput::make('customer_name')
                    ->label('Nombre Cliente')
                    ->required(),
                TextInput::make('customer_email')
                    ->label('Email Cliente')
                    ->email()
                    ->required(),
                TextInput::make('customer_phone')
                    ->label('Teléfono Cliente')
                    ->tel(),

                // Precio y Estado
                TextInput::make('total_price')
                    ->label('Precio Total')
                    ->numeric()
                    ->prefix('€')
                    ->required(),

                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->required()
                    ->default('pending'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                // Columna para la Autocaravana (con relación)
                TextColumn::make('campervan.name')
                    ->label('Autocaravana')
                    ->searchable()
                    ->sortable(),

                // Columna para el Cliente
                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                // Columna para el Estado (con colores)
                BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pending',   // Amarillo para pendiente
                        'success' => 'confirmed', // Verde para confirmada
                        'danger' => 'cancelled',  // Rojo para cancelada
                    ])
                    ->sortable(),

                // Columna de Fechas
                TextColumn::make('start_date')
                    ->label('Desde')
                    ->date('d/m/Y') // Formato de fecha
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->sortable(),

                // Columna de Precio
                TextColumn::make('total_price')
                    ->label('Precio Total')
                    ->money('EUR') // Formato de dinero
                    ->sortable(),

                // Columna de Creación (oculta)
                TextColumn::make('created_at')
                    ->label('Fecha Reserva')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculta por defecto
            ])
            ->filters([
                // Añadimos un FILTRO por estado
                SelectFilter::make('status')
                    ->label('Filtrar por Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(), // Añadimos un botón de "Ver"
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_date', 'desc'); // Ordena por defecto (las más nuevas primero)
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
