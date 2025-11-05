<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryItemResource\Pages;
use App\Filament\Resources\InventoryItemResource\RelationManagers;
use App\Models\InventoryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class InventoryItemResource extends Resource
{
    protected static ?string $model = InventoryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'Gestión de Campers';
    protected static ?int $navigationSort = 3;
    protected static ?string $label = 'Item de Inventario';
    protected static ?string $pluralLabel = 'Inventario Global';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Item de Inventario Global')
                    ->description('Define un item físico que posees (ej: "Portabicicletas"). Podrás asignarlo y ponerle precio a cada camper individualmente.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del Item')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->placeholder('Ej: Portabicicletas, Set de cocina, Sillas de camping...'),
                        
                        TextInput::make('total_stock')
                            ->label('Stock Físico Total')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->helperText('Unidades físicas totales que posees de este item en tu inventario global.'),

                        Textarea::make('notes')
                            ->label('Notas (Uso interno)')
                            ->columnSpanFull(),
                    ])->columns(1), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Item')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Stock (Solo afecta a extras)')
                    ->sortable(),
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
            'index' => Pages\ListInventoryItems::route('/'),
            'create' => Pages\CreateInventoryItem::route('/create'),
            'edit' => Pages\EditInventoryItem::route('/{record}/edit'),
        ];
    }    
}