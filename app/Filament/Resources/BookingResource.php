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

// --- Imports para los nuevos campos del formulario ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle; // ¡Asegúrate de que este 'use' esté!

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Icono de calendario

    /**
     * ==========================================================
     * MÉTODO FORM() ACTUALIZADO CON LOS NUEVOS CAMPOS
     * ==========================================================
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->required(),
                TextInput::make('customer_name')
                    ->label('Nombre Cliente')
                    ->required(),
                TextInput::make('customer_email')
                    ->label('Email Cliente')
                    ->email()
                    ->required(),
                DatePicker::make('start_date')
                    ->label('Check-in')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Check-out')
                    ->required(),
                TextInput::make('total_price')
                    ->label('Precio Total')
                    ->numeric()
                    ->prefix('€')
                    ->required(),

                // --- CAMPO DE ESTADO (El que ya tenías) ---
                Select::make('status')
                    ->label('Estado de la Reserva')
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->default('confirmed')
                    ->required(),

                // --- ================================== ---
                // --- CAMPOS DE PAGO (¡AÑADIDOS!) ---
                // --- ================================== ---

                Select::make('payment_status')
                    ->label('Estado del Pago')
                    ->options([
                        // Usamos las constantes de tu Modelo Booking
                        Booking::STATUS_PENDING => 'Pendiente de Pago',
                        Booking::STATUS_DEPOSIT_PAID => 'Señal Pagada (Parcial)',
                        Booking::STATUS_FULL_PAID => 'Pago Total Completado',
                    ])
                    ->default(Booking::STATUS_PENDING)
                    ->required()
                    ->reactive(),

                TextInput::make('amount_paid')
                    ->label('Cantidad Pagada')
                    ->numeric()
                    ->prefix('€')
                    ->default(0.00)
                    ->required(),

                Toggle::make('reminder_sent')
                    ->label('Recordatorio de pago enviado')
                    ->default(false),
            ]);
    }

    /**
     * ==========================================================
     * TU MÉTODO TABLE() (SIN CAMBIOS)
     * ==========================================================
     */
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
                    ->action(fn(Booking $record) => $record->update(['status' => 'cancelled']))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation() // Pide confirmación
                    ->hidden(fn(Booking $record) => $record->status === 'cancelled'), // Oculta si ya está cancelada
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
