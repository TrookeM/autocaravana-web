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

// Imports para la tabla
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\BadgeColumn; 
use Filament\Tables\Columns\ActionsColumn; // ¡IMPORTANTE! Añadir esto

// --- Imports para los nuevos campos del formulario ---
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\URL;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Components\CheckboxList; 

// --- Imports para las nuevas Acciones ---
use Filament\Tables\Actions\Action; 
use Filament\Tables\Actions\ActionGroup; 
use Filament\Forms\Components\Textarea; 
use App\Mail\BookingClosedMail;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification; 
use Carbon\Carbon; 
use App\Models\Campervan; 

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    // Opciones para el Nivel de Combustible
    protected static function getFuelLevelOptions(): array
    {
        return [
            '8/8' => '8/8 (Lleno)',
            '7/8' => '7/8',
            '6/8' => '6/8',
            '5/8' => '5/8',
            '4/8' => '4/8 (Medio)',
            '3/8' => '3/8',
            '2/8' => '2/8',
            '1/8' => '1/8 (Reserva)',
            '0/8' => '0/8 (Vacío)',
        ];
    }

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
                                'active' => 'Activa (En Curso)', 
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
                            ->numeric()->prefix('€')->required(),
                        Select::make('payment_status')
                            ->label('Estado del Pago')
                            ->options([
                                Booking::STATUS_PENDING => 'Pendiente',
                                Booking::STATUS_DEPOSIT_PAID => 'Señal Pagada',
                                Booking::STATUS_FULL_PAID => 'Pago Total',
                            ])
                            ->default(Booking::STATUS_PENDING)
                            ->required(),
                        TextInput::make('amount_paid')
                            ->label('Cantidad Pagada')
                            ->numeric()->prefix('€')->default(0.00)->required(),
                    ])->columns(3), 

                // --- Sección de Kilometraje (ACTUALIZADA) ---
                Section::make('Registro de Entrega y Devolución')
                    ->schema([
                        TextInput::make('km_salida')
                            ->label('Kilometraje de Salida')
                            ->numeric()
                            ->helperText('Usar la acción "Iniciar Check-in" para rellenar esto.'),
                        TextInput::make('km_llegada')
                            ->label('Kilometraje de Llegada')
                            ->numeric()
                            ->helperText('Usar la acción "Finalizar Check-out" para rellenar esto.'),
                        Select::make('fuel_level_out')
                            ->label('Nivel Combustible (Salida)')
                            ->options(self::getFuelLevelOptions()),
                        Select::make('fuel_level_in')
                            ->label('Nivel Combustible (Llegada)')
                            ->options(self::getFuelLevelOptions()),
                        
                        CheckboxList::make('inventory_checklist_out')
                            ->label('Checklist de Inventario (Salida)')
                            ->options(function (?Booking $record) {
                                if (!$record?->campervan_id) return [];
                                $campervan = Campervan::with('inventoryItems')->find($record->campervan_id);
                                if (!$campervan || $campervan->inventoryItems->isEmpty()) return [];
                                
                                return $campervan->inventoryItems
                                    ->mapWithKeys(fn($item) => [
                                        $item->name => "{$item->name} (x{$item->quantity})"
                                    ])
                                    ->toArray();
                            })
                            ->columns(2)
                            ->columnSpanFull(),

                    ])->columns(2),
                
                // --- Sección de Cargos Extra ---
                Section::make('Cargos Extra')
                    ->schema([
                        TextInput::make('extra_charge_km')
                            ->label('Cargo por KM Extra')
                            ->numeric()->prefix('€'),
                        TextInput::make('extra_charge_fuel')
                            ->label('Cargo por Combustible')
                            ->numeric()->prefix('€'),
                        TextInput::make('extra_charge_other')
                            ->label('Otros Cargos')
                            ->numeric()->prefix('€'),
                        Textarea::make('checkout_notes')
                            ->label('Notas de Check-out (Daños, Limpieza, etc.)')
                            ->columnSpanFull(),
                    ])->columns(3),

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
                TextColumn::make('campervan.name')
                    ->label('Autocaravana')
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label('Check-in')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Estado Reserva')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'confirmed',
                        'blue' => 'active', 
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => [
                        'pending' => 'Pendiente',
                        'confirmed' => 'Confirmada',
                        'active' => 'Activa', 
                        'completed' => 'Completada',
                        'cancelled' => 'Cancelada',
                    ][$state] ?? $state)
                    ->sortable(),

                SelectColumn::make('payment_status')
                    ->label('Estado Pago')
                    ->options([
                        Booking::STATUS_PENDING => 'Pendiente',
                        Booking::STATUS_DEPOSIT_PAID => 'Señal Pagada',
                        Booking::STATUS_FULL_PAID => 'Pago Total',
                    ])
                    ->sortable(),
                
                TextColumn::make('end_date')
                    ->label('Check-out')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), 

                TextColumn::make('customer_name')
                    ->label('Cliente')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), 
            ])
            ->filters([
                //
            ])
            ->actions([
                // MOVEMOS LAS ACCIONES AQUÍ en lugar de en una columna
                ActionGroup::make([
                    self::getCheckInAction(),
                    self::getCheckOutAction(),
                    
                    Action::make('Descargar Contrato')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn(Booking $record): string => route('booking.contract.download', $record))
                        ->openUrlInNewTab(),
                    
                    Action::make('Cancelar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn(Booking $record) => $record->update(['status' => 'cancelled']))
                        ->requiresConfirmation()
                        ->hidden(fn(Booking $record) => $record->status === 'cancelled'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    // ================================================================
    // --- ACCIÓN DE CHECK-IN (CORREGIDA) ---
    // ================================================================
    protected static function getCheckInAction(): Action
    {
        return Action::make('Iniciar Check-in')
            ->icon('heroicon-o-play-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Iniciar Check-in de la Reserva')
            ->modalDescription('Rellena los datos de salida y verifica el inventario para activar la reserva.')
            ->visible(function (Booking $record): bool {
                // SOLO verifica el estado - sin verificación de fecha
                return $record->status === 'confirmed';
            })
            ->form([
                // --- Grupo 1: Datos ---
                Section::make('Datos de Salida')
                    ->schema([
                        TextInput::make('km_salida')
                            ->label('Kilometraje de Salida')
                            ->numeric()
                            ->required()
                            ->placeholder('Ej: 85000')
                            ->default(function (Booking $record) {
                                $lastBooking = Booking::where('campervan_id', $record->campervan_id)
                                    ->where('status', 'completed')
                                    ->whereNotNull('km_llegada')
                                    ->orderBy('end_date', 'desc')
                                    ->first();
                                return $lastBooking?->km_llegada;
                            }),
                        Select::make('fuel_level_out')
                            ->label('Nivel Combustible (Salida)')
                            ->options(self::getFuelLevelOptions())
                            ->required(),
                    ])->columns(2),

                // --- Grupo 2: Checklist de Inventario (RF9.2) ---
                Section::make('Checklist de Inventario')
                    ->schema([
                        CheckboxList::make('inventory_checklist_out')
                            ->label('Marcar items entregados')
                            ->options(function (Booking $record) {
                                $record->load('campervan.inventoryItems');
                                
                                if (!$record->campervan || $record->campervan->inventoryItems->isEmpty()) {
                                    return ['ninguno' => 'Esta camper no tiene items de inventario configurados.'];
                                }

                                return $record->campervan->inventoryItems
                                    ->mapWithKeys(fn($item) => [
                                        $item->name => "{$item->name} (x{$item->quantity})"
                                    ])
                                    ->toArray();
                            })
                            ->helperText('Marcar todos los items que se entregan al cliente.')
                            ->columns(2) 
                            ->required(), 
                    ]),
            ])
            ->action(function (Booking $record, array $data) {
                $record->update([
                    'km_salida' => $data['km_salida'],
                    'fuel_level_out' => $data['fuel_level_out'],
                    'inventory_checklist_out' => $data['inventory_checklist_out'], 
                    'status' => 'active',
                ]);
                Notification::make()
                    ->title('¡Check-in Iniciado!')
                    ->body('La reserva está ahora "Activa". ¡Buen viaje!')
                    ->success()
                    ->send();
            });
    }

    // --- ACCIÓN DE CHECK-OUT (CORREGIDA) ---
    protected static function getCheckOutAction(): Action
    {
        return Action::make('Finalizar Check-out')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Finalizar Reserva y Calcular Cargos')
            ->modalDescription('Rellena los datos de llegada. El sistema calculará los cargos extra.')
            ->visible(fn(Booking $record): bool => $record->status === 'active')
            ->form([
                TextInput::make('km_llegada')
                    ->label('Kilometraje de Llegada')
                    ->numeric()
                    ->required()
                    ->rule(function (Get $get, Booking $record) {
                        $kmSalida = $record->km_salida ?? 0;
                        return 'gt:' . $kmSalida;
                    }),
                Select::make('fuel_level_in')
                    ->label('Nivel Combustible (Llegada)')
                    ->options(self::getFuelLevelOptions())
                    ->required(),
                TextInput::make('extra_charge_fuel') 
                    ->label('Cargo por Combustible')
                    ->numeric()->prefix('€')->default(0.00),
                TextInput::make('extra_charge_other') 
                    ->label('Otros Cargos (Limpieza, Daños, etc.)')
                    ->numeric()->prefix('€')->default(0.00),
                Textarea::make('checkout_notes')
                    ->label('Notas de Check-out (Daños, Limpieza, etc.)')
                    ->columnSpanFull(),
            ])
            ->action(function (Booking $record, array $data) {
                
                $record->load('campervan'); 
                
                $campervan = $record->campervan;
                $kmSalida = (int)$record->km_salida; 
                $kmLlegada = (int)$data['km_llegada']; 
                $pricePerExtraKm = (float)$campervan->price_per_extra_km;
                $kmLimitPerDay = (int)$campervan->km_limit;
                
                $cargoKm = 0.00;
                $kmExtra = 0;

                if ($kmLimitPerDay > 0 && $pricePerExtraKm > 0 && $kmLlegada > $kmSalida) {
                    $nights = $record->start_date->diffInDays($record->end_date);
                    $totalNights = $nights > 0 ? $nights : 1;
                    $totalKmLimit = $kmLimitPerDay * $totalNights;
                    $kmRecorridos = $kmLlegada - $kmSalida;
                    $kmExtra = $kmRecorridos - $totalKmLimit;
                    
                    if ($kmExtra > 0) {
                        $cargoKm = $kmExtra * $pricePerExtraKm;
                    } else {
                        $kmExtra = 0; 
                    }
                }

                $record->update([
                    'status' => 'completed',
                    'km_llegada' => $data['km_llegada'],
                    'fuel_level_in' => $data['fuel_level_in'],
                    'checkout_notes' => $data['checkout_notes'],
                    'extra_charge_km' => $cargoKm,
                    'extra_charge_fuel' => $data['extra_charge_fuel'],
                    'extra_charge_other' => $data['extra_charge_other'],
                ]); 

                $cargoTotal = $cargoKm + $data['extra_charge_fuel'] + $data['extra_charge_other'];
                
                // Mail::to($record->customer_email)->send(new BookingClosedMail($record, $cargoTotal, $kmExtra));
                
                Notification::make()
                    ->title('¡Reserva Completada!')
                    ->body('La reserva se ha finalizado y los cargos extra se han calculado.')
                    ->success()
                    ->send();
            });
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