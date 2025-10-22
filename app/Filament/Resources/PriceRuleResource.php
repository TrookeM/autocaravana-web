<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PriceRuleResource\Pages;
use App\Filament\Resources\PriceRuleResource\RelationManagers;
use App\Models\PriceRule;
use App\Models\Campervan;
use App\Models\Booking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PriceRuleResource extends Resource
{
    protected static ?string $model = PriceRule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // app/Filament/Resources/PriceRuleResource.php

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('campervan_id')
                    ->relationship('campervan', 'name')
                    ->label('Autocaravana (Dejar vacío para global)')
                    ->placeholder('Regla global para todas las autocaravanas')
                    ->nullable(),

                Forms\Components\Select::make('type')
                    ->label('Tipo de Ajuste')
                    ->options([
                        'percentage_increase' => 'Aumento Porcentual (+%)',
                        'percentage_decrease' => 'Descuento Porcentual (-%)',
                        'fixed_increase' => 'Aumento Fijo (+€)',
                        'fixed_decrease' => 'Descuento Fijo (-€)',
                    ])
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('value')
                    ->label('Valor (%, o €)')
                    ->required()
                    ->numeric()
                    ->rule('gt:0')
                    ->step(0.01)
                    ->extraAttributes(['inputmode' => 'decimal']),

                Forms\Components\Select::make('period')
                    ->label('Período de Aplicación')
                    ->options([
                        'all' => 'Todas las Fechas (por defecto)',
                        'weekends' => 'Solo Fines de Semana',
                        'weekdays' => 'Solo Días de Semana',
                        'custom_dates' => 'Rango de Fechas Específico',
                    ])
                    ->required()
                    ->live()
                    ->native(false),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Fecha de Inicio')
                    ->nullable()
                    ->visible(fn(Forms\Get $get) => in_array($get('period'), ['custom_dates', 'all'])),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Fecha de Fin (No se incluye esta noche)')
                    ->nullable()
                    ->afterOrEqual('start_date')
                    ->visible(fn(Forms\Get $get) => in_array($get('period'), ['custom_dates', 'all'])),

                Forms\Components\Toggle::make('is_active')
                    ->label('Regla Activa')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    // app/Filament/Resources/PriceRuleResource.php

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('campervan.name')
                    ->label('Aplica a')
                    ->default('GLOBAL')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('type')
                    ->label('Ajuste')
                    ->badge(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor'),
                Tables\Columns\TextColumn::make('period')
                    ->label('Período')
                    ->badge(),
                Tables\Columns\TextColumn::make('start_date')->date()->sortable(),
                Tables\Columns\TextColumn::make('end_date')->date()->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
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
            'index' => Pages\ListPriceRules::route('/'),
            'create' => Pages\CreatePriceRule::route('/create'),
            'edit' => Pages\EditPriceRule::route('/{record}/edit'),
        ];
    } 
}
