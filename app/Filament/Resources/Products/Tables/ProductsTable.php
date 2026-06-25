<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
                    ->searchable()
                    ->limit(45)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->iconPosition(IconPosition::After)
                    ->icon(fn (int $state): ?string => match (true) {
                        $state <= 50 => 'heroicon-o-exclamation-triangle',
                        default => '',
                    })
                    ->iconColor('warning'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            ->recordUrl(fn ($record) => null)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()->modalFooterActions([
                        Action::make('edit')
                            ->label('Edit product')
                            ->color('primary')
                            ->visible(fn () => auth()->user()->isAdmin())
                            ->url(fn ($record) => route('filament.admin.resources.products.edit', $record)),

                        Action::make('close')
                            ->label('Cancel')
                            ->color('gray')
                            ->close(),
                    ]),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
