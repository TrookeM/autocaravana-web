<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampervanResource\Pages;
use App\Models\Campervan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class CampervanResource extends Resource
{
    protected static ?string $model = Campervan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Información Principal')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre Modelo')
                                    ->required(),

                                RichEditor::make('description')
                                    ->label('Descripción Detallada')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Gestión de Imágenes')
                            ->description('Sube la imagen principal para la lista y las imágenes de la galería de detalle.')
                            ->schema([
                                FileUpload::make('main_image_path')
                                    ->label('Imagen Principal (Foto de Portada)')
                                    ->disk('public')
                                    ->directory('campervan_images')
                                    ->image()
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('16:9')
                                    ->imageResizeTargetWidth('1200')
                                    ->imageResizeTargetHeight('675')
                                    ->columnSpan(1),

                                FileUpload::make('secondary_images_json')
                                    ->label('Galería de Imágenes Secundarias')
                                    ->disk('public')
                                    ->directory('campervan_images')
                                    ->multiple()
                                    ->maxFiles(5)
                                    ->image()
                                    ->reorderable()
                                    ->columnSpan(1),
                            ])->columns(2),
                    ])->columnSpan(2),

                Forms\Components\Section::make('Configuración')
                    ->schema([
                        TextInput::make('price_per_night')
                            ->label('Precio por noche')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        Toggle::make('allows_deposit')
                            ->label('Permitir Pago de Señal')
                            ->default(true)
                            ->helperText('Si está activo, el cliente puede optar por pagar solo el 30% como señal.'),

                        Toggle::make('no_checkout_booking')
                            ->label('Bloquear check-in en día de check-out')
                            ->default(false)
                            ->helperText('Si se activa, no se podrá iniciar una reserva el mismo día que finaliza otra (para limpieza).')
                            ->onColor('danger'),

                        Toggle::make('is_visible')
                            ->label('Visible para alquilar')
                            ->default(true)
                            ->helperText('Si está apagado, no aparecerá en la web pública'),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre Modelo')
                    ->searchable(),

                TextColumn::make('price_per_night')
                    ->label('Precio/noche')
                    ->money('EUR')
                    ->sortable(),

                IconColumn::make('allows_deposit')
                    ->label('Permite Señal')
                    ->boolean(),

                IconColumn::make('no_checkout_booking')
                    ->label('Bloqueo')
                    ->tooltip('Bloqueo de check-in en día de check-out')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open'),

                IconColumn::make('is_visible')
                    ->label('Visible')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Fecha de alta')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('allows_deposit')
                    ->label('Pago de Señal')
                    ->placeholder('Todas las caravanas')
                    ->trueLabel('Solo con Señal')
                    ->falseLabel('Solo sin Señal')
                    ->queries(
                        true: fn(Builder $query) => $query->where('allows_deposit', true),
                        false: fn(Builder $query) => $query->where('allows_deposit', false),
                        blank: fn(Builder $query) => $query,
                    ),

                Tables\Filters\TernaryFilter::make('no_checkout_booking')
                    ->label('Bloqueo Check-in/Check-out')
                    ->placeholder('Todas')
                    ->trueLabel('Solo Bloqueadas')
                    ->falseLabel('Solo Desbloqueadas'),
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
            'index' => Pages\ListCampervans::route('/'),
            'create' => Pages\CreateCampervan::route('/create'),
            'edit' => Pages\EditCampervan::route('/{record}/edit'),
        ];
    }
}
