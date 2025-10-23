<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages\CreateCoupon;
use App\Filament\Resources\CouponResource\Pages\EditCoupon;
use App\Filament\Resources\CouponResource\Pages\ListCoupons;
use App\Models\Coupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Reservas y Pagos';
    protected static ?string $modelLabel = 'Cupón';
    protected static ?string $pluralModelLabel = 'Cupones';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalles del Cupón')
                    ->columns(3)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        Select::make('type')
                            ->label('Tipo de Descuento')
                            ->options([
                                'fixed' => 'Cantidad Fija (€)',
                                'percentage' => 'Porcentaje (%)',
                            ])
                            ->default('fixed')
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('value')
                            ->label('Valor del Descuento')
                            ->numeric()
                            ->required()
                            ->columnSpan(1)
                            ->prefix(fn (Forms\Get $get) => $get('type') === 'percentage' ? '%' : '€'),
                    ]),

                Forms\Components\Section::make('Configuración y Límite')
                    ->columns(3)
                    ->schema([
                        TextInput::make('max_uses')
                            ->label('Usos Máximos')
                            ->numeric()
                            ->helperText('Déjalo vacío para usos ilimitados.')
                            ->nullable()
                            ->columnSpan(1),

                        // --- CAMPO CORREGIDO: Oculto en creación y con valor por defecto ---
                        TextInput::make('uses')
                            ->label('Usos Actuales')
                            ->numeric()
                            ->readOnly()
                            ->default(0) // Aseguramos que Filament no envíe NULL
                            ->hidden(fn (string $operation): bool => $operation === 'create') // Oculto en la vista 'create'
                            ->columnSpan(1),
                        // ---------------------------------------------------------------------
                            
                        DatePicker::make('expires_at')
                            ->label('Fecha de Expiración')
                            ->nullable()
                            ->columnSpan(1),
                    ]),

                Forms\Components\Section::make('Estado')
                    ->columns(1)
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true)
                            ->helperText('Solo los cupones activos pueden ser utilizados.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'primary',
                        'percentage' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                    
                TextColumn::make('value')
                    ->label('Valor')
                    ->formatStateUsing(function (string $state, Coupon $record): string {
                        return $record->type === 'percentage' ? "{$state}%" : "{$state} €";
                    })
                    ->sortable(),

                TextColumn::make('uses')
                    ->label('Usos')
                    ->formatStateUsing(fn (string $state, Coupon $record): string => 
                        $record->max_uses ? "{$state} / {$record->max_uses}" : "{$state} / ∞"
                    )
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expira')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo de Descuento')
                    ->options([
                        'fixed' => 'Cantidad Fija (€)',
                        'percentage' => 'Porcentaje (%)',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Estado')
                    ->placeholder('Todos')
                    ->trueLabel('Solo Activos')
                    ->falseLabel('Solo Inactivos')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_active', true),
                        false: fn (Builder $query) => $query->where('is_active', false),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
