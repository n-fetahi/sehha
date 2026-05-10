<?php

namespace App\Filament\Resources\Wallets;

use App\Models\Wallet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    // ✅ تعريب القائمة
    protected static ?string $navigationLabel = 'المحافظ';
    protected static ?string $modelLabel = 'محفظة';
    protected static ?string $pluralModelLabel = 'المحافظ';

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    protected static ?int $navigationSort = 10;

    // ✅ أيقونة مناسبة للمحافظ (نقود)
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\Wallets\Schemas\WalletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Wallets\Tables\WalletsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Wallets\Pages\ListWallets::route('/'),
            'create' => \App\Filament\Resources\Wallets\Pages\CreateWallet::route('/create'),
            'edit' => \App\Filament\Resources\Wallets\Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}