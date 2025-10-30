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
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Actions\Action;

// --- Imports para los nuevos campos del formulario ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\URL; 
use Filament\Forms\Components\Placeholder; // <-- ¡AÑADIR ESTA LÍNEA!
use Filament\Forms\Get;                     // <-- ¡AÑADIR ESTA LÍNEA!

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- Grupo Principal de Datos ---
                Section::make('Datos de la Reserva')
                    ->schema([
                        Select::make('campervan_id')
                            ->relationship('campervan', 'name')
                            ->required()
                            ->reactive() // <-- Reactivo por si acaso lo necesitamos
                            ->searchable(),
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
                        Select::make('status')
                            ->label('Estado de la Reserva')
                            ->options([
                                'pending' => 'Pendiente',
                                'confirmed' => 'Confirmada',
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('confirmed')
                            ->required(),
                    ])->columns(2), // Organiza en 2 columnas

                // --- Grupo de Pagos ---
                Section::make('Datos de Pago')
                    ->schema([
                        TextInput::make('total_price')
                            ->label('Precio Total')
                            ->numeric()
                            ->prefix('€')
                            ->required(),
                        Select::make('payment_status')
                            ->label('Estado del Pago')
                            ->options([
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
                    ])->columns(3), // Organiza en 3 columnas

                // --- ================================== ---
                // --- ¡SECCIÓN DE KILOMETRAJE ACTUALIZADA! ---
                // --- ================================== ---
                Section::make('Registro de Kilometraje (RF6.4)')
                    ->schema([
                        TextInput::make('km_salida')
                            ->label('Kilometraje de Salida')
                            ->numeric()
                            ->reactive() // <-- AÑADIDO: para recalcular en vivo
                            ->placeholder('KMs al recoger'),
                        TextInput::make('km_llegada')
                            ->label('Kilometraje de Llegada')
                            ->numeric()
                            ->reactive() // <-- AÑADIDO: para recalcular en vivo
                            ->placeholder('KMs al devolver'),

                        // --- CAMPO CALCULADO (AÑADIDO) ---
                        Placeholder::make('cargo_extra_km')
                            ->label('Cargo por KM Extra')
                            ->content(function (Get $get, $record): string {
                                if (!$record) {
                                    return '0.00 € (Solo disponible al editar)';
                                }

                                $campervan = $record->campervan;
                                $kmSalida = (int)$get('km_salida');
                                $kmLlegada = (int)$get('km_llegada');
                                $kmLimit = (int)$campervan->km_limit;
                                $pricePerExtraKm = (float)$campervan->price_per_extra_km;

                                if (empty($kmLimit) || $kmLimit === 0 || empty($pricePerExtraKm)) {
                                    return '0.00 € (KM Ilimitados)';
                                }
                                
                                if (empty($kmLlegada) || $kmLlegada <= $kmSalida) {
                                    return '0.00 €';
                                }

                                $kmRecorridos = $kmLlegada - $kmSalida;
                                $kmExtra = $kmRecorridos - $kmLimit;

                                if ($kmExtra <= 0) {
                                    return '0.00 € (Límite no superado)';
                                }

                                $cargo = $kmExtra * $pricePerExtraKm;
                                
                                return number_format($cargo, 2, ',', '.') . ' € (' . $kmExtra . ' km extra)';
                            })
                            ->columnSpanFull()
                            ->helperText('Este campo se calcula automáticamente al guardar.'),
                    ])->columns(2),

                // --- Grupo de Notificaciones ---
                Section::make('Notificaciones')
                    ->schema([
                        Toggle::make('reminder_sent')
                            ->label('Recordatorio de pago enviado')
                            ->default(false),
                    ])->columns(1),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID Reserva')
                    ->sortable(),
                TextColumn::make('campervan.name')
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
                
                SelectColumn::make('status')
                    ->label('Estado Reserva') 
                    ->options([
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'cancelled' => 'Cancelada',
                    ])
                    ->sortable(),
                
                SelectColumn::make('payment_status')
                    ->label('Estado Pago')
                    ->options([
                        Booking::STATUS_PENDING => 'Pendiente',
                        Booking::STATUS_DEPOSIT_PAID => 'Señal Pagada',
                        Booking::STATUS_FULL_PAID => 'Pagado Total',
                    ])
                    ->sortable(),
                
                TextColumn::make('amount_paid')
                    ->label('Pagado')
                    ->money('EUR')
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
                
                Action::make('Descargar Contrato')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Booking $record): string => route('booking.contract.download', $record))
                    ->openUrlInNewTab(),
                
                Action::make('Cancelar')
                    ->action(fn(Booking $record) => $record->update(['status' => 'cancelled']))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->hidden(fn(Booking $record) => $record->status === 'cancelled'),
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