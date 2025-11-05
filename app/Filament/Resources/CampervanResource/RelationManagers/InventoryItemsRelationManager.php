<?php

namespace App\Filament\Resources\CampervanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Select;
use App\Models\InventoryItem;

class InventoryItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryItems';

    protected static ?string $title = 'Inventario y Extras de la Camper';
    protected static ?string $label = 'Item';
    protected static ?string $pluralLabel = 'Inventario y Extras';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('quantity')
                    ->label('Cantidad Incluida')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->helperText('¿Cuántas unidades se incluyen por defecto con esta camper?'),
                
                Toggle::make('es_opcional')
                    ->label('Es un Extra de Pago')
                    ->reactive()
                    ->helperText('¿El cliente puede contratar esto por un coste adicional?'),

                TextInput::make('precio')
                    ->label('Precio del Extra')
                    ->numeric()
                    ->prefix('€')
                    ->default(0)
                    ->visible(fn (Forms\Get $get) => $get('es_opcional')), 

                Toggle::make('es_por_dia')
                    ->label('¿Precio por día?')
                    ->default(false)
                    ->visible(fn (Forms\Get $get) => $get('es_opcional')), 
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Item (del Inventario Global)')
                    ->searchable(),
                
                TextColumn::make('quantity') 
                    ->label('Cant. Incluida')
                    ->sortable(),

                IconColumn::make('es_opcional')
                    ->label('Extra de Pago')
                    ->boolean(),

                TextColumn::make('precio')
                    ->label('Precio Extra')
                    ->money('EUR')
                    ->sortable(),
                
                TextColumn::make('total_stock')
                    ->label('Stock (Global)')
                    ->sortable()
                    ->tooltip('Unidades totales que posees de este item.'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Crear y Adjuntar Item')
                    ->form([
                        TextInput::make('name')
                            ->label('Nombre del Item Nuevo')
                            ->required(),
                        TextInput::make('total_stock')
                            ->label('Stock Total Global')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Cantidad Incluida')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Toggle::make('es_opcional')
                            ->label('Es un Extra de Pago')
                            ->reactive(),
                        TextInput::make('precio')
                            ->label('Precio Extra')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('es_opcional')),
                        Toggle::make('es_por_dia')
                            ->label('¿Precio por día?')
                            ->default(false)
                            ->visible(fn (Forms\Get $get) => $get('es_opcional')),
                    ]),

                Tables\Actions\AttachAction::make()
                    ->label('Adjuntar Item Existente')
                    ->form(fn (Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) {
                                return InventoryItem::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => InventoryItem::find($value)?->name)
                            ->options(InventoryItem::query()->orderBy('name')->limit(100)->pluck('name', 'id'))
                            ->searchDebounce(500)
                            ->label('Item del Inventario Global')
                            ->hint('Teclea para buscar o haz clic para ver la lista completa'),
                        
                        TextInput::make('quantity')
                            ->label('Cantidad Incluida (No se descuenta del stock)')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Toggle::make('es_opcional')
                            ->label('Es un Extra de Pago')
                            ->reactive(),
                        TextInput::make('precio')
                            ->label('Precio Extra')
                            ->numeric()
                            ->prefix('€')
                            ->default(0)
                            ->visible(fn (Forms\Get $get) => $get('es_opcional')),
                        Toggle::make('es_por_dia')
                            ->label('¿Precio por día?')
                            ->default(false)
                            ->visible(fn (Forms\Get $get) => $get('es_opcional')),
                    ])
                    ->recordSelectSearchColumns(['name'])
                    ->recordSelect(fn (Select $select) => $select->placeholder('Selecciona un item...')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(), 
                Tables\Actions\DetachAction::make(), 
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}