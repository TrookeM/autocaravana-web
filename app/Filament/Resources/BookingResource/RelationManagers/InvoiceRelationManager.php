<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// use Illuminate\Database\Eloquent\Model; // <-- Ya no es necesario

// --- Imports para la descarga ---
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
// ---------------------------------

class InvoiceRelationManager extends RelationManager
{
    // Define la relación (hasOne)
    protected static string $relationship = 'invoice';

    // Título que aparecerá en Filament
    protected static ?string $title = 'Factura';

    public function form(Form $form): Form
    {
        // Este formulario se usa para la acción "Ver"
        return $form
            ->schema([
                Forms\Components\TextInput::make('invoice_number')
                    ->label('Número de Factura')
                    ->disabled(),
                Forms\Components\DatePicker::make('invoice_date')
                    ->label('Fecha de Factura')
                    ->disabled(),

                Forms\Components\Placeholder::make('total_amount')
                    ->label('Importe Total')
                    ->content(
                        fn(?Invoice $record): string =>
                        $record ? number_format($record->total_amount / 100, 2) . ' €' : '0 €'
                    ),
                Forms\Components\Placeholder::make('tax_amount')
                    ->label('IVA')
                    ->content(
                        fn(?Invoice $record): string =>
                        $record ? number_format($record->tax_amount / 100, 2) . ' €' : '0 €'
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('invoice_number')
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Número')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                // ¡COLUMNA CORREGIDA! (para los céntimos)
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('eur', divideBy: 100) // <-- Arreglo de céntimos
                    ->sortable(),

                // ¡COLUMNA AÑADIDA!
                Tables\Columns\TextColumn::make('tax_amount')
                    ->label('IVA')
                    ->money('eur', divideBy: 100) // <-- Arreglo de céntimos
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                // Sin filtros por ahora
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Deshabilitado
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // <-- Añadimos la acción de Ver

                Tables\Actions\Action::make('downloadPdf')
                    ->label('Descargar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->action(function (Invoice $record) {
                        $record->load('booking.campervan');
                        $pdf = Pdf::loadView('pdf.invoice', [
                            'invoice' => $record,
                            'booking' => $record->booking
                        ]);
                        $pdf->setPaper('a4', 'portrait');

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            "Factura-{$record->invoice_number}.pdf"
                        );
                    }),
            ])
            ->bulkActions([
                // Sin acciones en lote
            ]);
    }

    public function canAssociate(): bool
    {
        return false;
    }

    public function canDissociate(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }
}
