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
use Filament\Tables\Columns\BadgeColumn; // <-- ¡AÑADIDA!

// --- Imports para los nuevos campos del formulario ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\URL;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;

// --- Imports para la nueva acción "Finalizar Reserva" ---
use App\Mail\BookingClosedMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification; 
use Carbon\Carbon;

class BookingResource extends Resource
{
    // ... (tu $model, $navigationIcon, y el método form() se quedan igual) ...
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
                            ->reactive() 
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
                                'completed' => 'Completada', 
                                'cancelled' => 'Cancelada',
                            ])
                            ->default('confirmed')
                            ->required(),
                    ])->columns(2), 

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
                    ])->columns(3), 

                // --- Sección de Kilometraje ---
                Section::make('Registro de Kilometraje (RF6.4)')
                    ->schema([
                        TextInput::make('km_salida')
                            ->label('Kilometraje de Salida')
                            ->numeric()
                            ->reactive() 
                            ->placeholder('KMs al recoger'),
                        TextInput::make('km_llegada')
                            ->label('Kilometraje de Llegada')
                            ->numeric()
                            ->reactive() 
                            ->placeholder('KMs al devolver'),

                        Placeholder::make('cargo_extra_km')
                            ->label('Cargo por KM Extra')
                            ->content(function (Get $get, $record): string {
                                if (!$record) {
                                    return '0.00 € (Solo disponible al editar)';
                                }
                                $campervan = $record->campervan;
                                $kmSalida = (int)$get('km_salida');
                                $kmLlegada = (int)$get('km_llegada');
                                $pricePerExtraKm = (float)$campervan->price_per_extra_km;
                                $kmLimitPerDay = (int)$campervan->km_limit;
                                if (empty($kmLimitPerDay) || $kmLimitPerDay === 0 || empty($pricePerExtraKm)) {
                                    return '0.00 € (KM Ilimitados)';
                                }
                                if (empty($kmLlegada) || $kmLlegada <= $kmSalida) {
                                    return '0.00 €';
                                }
                                $nights = Carbon::parse($record->start_date)->diffInDays(Carbon::parse($record->end_date));
                                $totalNights = $nights > 0 ? $nights : 1;
                                $totalKmLimit = $kmLimitPerDay * $totalNights;
                                $kmRecorridos = $kmLlegada - $kmSalida;
                                $kmExtra = $kmRecorridos - $totalKmLimit;
                                if ($kmExtra <= 0) {
                                    return '0.00 € (Límite no superado: ' . $totalKmLimit . ' km)';
                                }
                                $cargo = $kmExtra * $pricePerExtraKm;
                                return number_format($cargo, 2, ',', '.') . ' € (' . $kmExtra . ' km extra)';
                            })
                            ->columnSpanFull()
                            ->helperText('Este campo se calcula automáticamente.'),
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
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // OCULTO

                TextColumn::make('campervan.name')
                    ->label('Autocaravana')
                    ->searchable(),

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // OCULTO

                TextColumn::make('start_date')
                    ->label('Check-in')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Check-out')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // OCULTO

                // --- ================================== ---
                // --- ¡COLUMNA DE ESTADO REEMPLAZADA! ---
                // --- ================================== ---
                BadgeColumn::make('status')
                    ->label('Estado Reserva')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => [
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ][$state] ?? $state) // Convierte 'confirmed' en 'Confirmada'
                    ->sortable(), // <-- VISIBLE

                SelectColumn::make('payment_status')
                    ->label('Estado Pago')
                    ->options([
                        Booking::STATUS_PENDING => 'Pendiente',
                        Booking::STATUS_DEPOSIT_PAID => 'Señal Pagada',
                        Booking::STATUS_FULL_PAID => 'Pagado Total',
                    ])
                    ->sortable(), // <-- VISIBLE

                TextColumn::make('amount_paid')
                    ->label('Pagado')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // <-- OCULTO

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('EUR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // <-- OCULTO
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Action::make('Descargar Contrato')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn(Booking $record): string => route('booking.contract.download', $record))
                    ->openUrlInNewTab(),

                Action::make('Finalizar Reserva')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation() 
                    ->modalHeading('Finalizar Reserva y Notificar Cliente')
                    ->modalDescription('¿Estás seguro de que quieres finalizar esta reserva? Se calcularán los KM extra y se enviará un email de cierre al cliente.')
                    ->hidden(fn(Booking $record): bool => $record->status !== 'confirmed') 
                    ->action(function (Booking $record) {
                        
                        $record->load('campervan');
                        
                        if (empty($record->km_llegada) || empty($record->km_salida)) {
                            Notification::make()
                                ->title('Error: Faltan datos')
                                ->body('Por favor, edita la reserva e introduce el Kilometraje de Salida y Llegada antes de finalizarla.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Calcular KM extra
                        $campervan = $record->campervan;
                        $kmSalida = (int)$record->km_salida;
                        $kmLlegada = (int)$record->km_llegada;
                        $pricePerExtraKm = (float)$campervan->price_per_extra_km;
                        $kmLimitPerDay = (int)$campervan->km_limit;
                        $cargo = 0.00;
                        $kmExtra = 0;

                        if ($kmLimitPerDay > 0 && $pricePerExtraKm > 0 && $kmLlegada > $kmSalida) {
                            $nights = Carbon::parse($record->start_date)->diffInDays(Carbon::parse($record->end_date));
                            $totalNights = $nights > 0 ? $nights : 1;
                            $totalKmLimit = $kmLimitPerDay * $totalNights;
                            $kmRecorridos = $kmLlegada - $kmSalida;
                            $kmExtra = $kmRecorridos - $totalKmLimit;
                            if ($kmExtra > 0) {
                                $cargo = $kmExtra * $pricePerExtraKm;
                            } else {
                                $kmExtra = 0; 
                            }
                        }

                        // Actualizar el estado
                        $record->update(['status' => 'completed']); 

                        // Enviar el email
                        try {
                            Mail::to($record->customer_email)->send(new BookingClosedMail($record, $cargo, $kmExtra));
                            Notification::make()
                                ->title('¡Reserva Finalizada!')
                                ->body('El email de cierre con los cargos extra ha sido enviado al cliente.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al enviar email')
                                ->body('La reserva se ha finalizado, pero ha fallado el envío del email: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

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