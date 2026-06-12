<?php

namespace App\Filament\Resources\Professionals;

use App\Filament\Resources\Professionals\Pages\CreateProfessional;
use App\Filament\Resources\Professionals\Pages\EditProfessional;
use App\Filament\Resources\Professionals\Pages\ListProfessionals;
use App\Filament\Resources\Professionals\Schemas\ProfessionalForm;
use App\Filament\Resources\Professionals\Tables\ProfessionalsTable;
use App\Models\Professional;
use App\Support\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProfessionalResource extends Resource
{
    protected static ?string $model = Professional::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Profissionais';

    protected static ?string $modelLabel = 'Profissional';

    protected static ?string $pluralModelLabel = 'Profissionais';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    // Profissional vê a lista (para escolha em agendamentos) mas não cria/edita
    public static function canViewAny(): bool        { return Permission::isTenantMember(); }
    public static function canCreate(): bool         { return Permission::isGestorOrAbove(); }
    public static function canEdit(Model $r): bool   { return Permission::isGestorOrAbove(); }
    public static function canDelete(Model $r): bool { return Permission::isGestorOrAbove(); }

    public static function form(Schema $schema): Schema
    {
        return ProfessionalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProfessionalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListProfessionals::route('/'),
            'create' => CreateProfessional::route('/create'),
            'edit'   => EditProfessional::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
