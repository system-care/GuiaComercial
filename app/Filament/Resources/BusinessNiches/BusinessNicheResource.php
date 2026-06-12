<?php

namespace App\Filament\Resources\BusinessNiches;

use App\Filament\Resources\BusinessNiches\Pages\CreateBusinessNiche;
use App\Filament\Resources\BusinessNiches\Pages\EditBusinessNiche;
use App\Filament\Resources\BusinessNiches\Pages\ListBusinessNiches;
use App\Filament\Resources\BusinessNiches\Schemas\BusinessNicheForm;
use App\Filament\Resources\BusinessNiches\Tables\BusinessNichesTable;
use App\Models\BusinessNiche;
use App\Support\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BusinessNicheResource extends Resource
{
    protected static ?string $model = BusinessNiche::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Tipos de Negócios';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool        { return Permission::isSuperAdmin(); }
    public static function canCreate(): bool         { return Permission::isSuperAdmin(); }
    public static function canEdit(Model $r): bool   { return Permission::isSuperAdmin(); }
    public static function canDelete(Model $r): bool { return Permission::isSuperAdmin(); }

    public static function form(Schema $schema): Schema
    {
        return BusinessNicheForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BusinessNichesTable::configure($table);
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
            'index' => ListBusinessNiches::route('/'),
            'create' => CreateBusinessNiche::route('/create'),
            'edit' => EditBusinessNiche::route('/{record}/edit'),
        ];
    }
}
