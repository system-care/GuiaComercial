<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use App\Models\User;
use App\Support\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Clientes';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 3;

    // Profissional sempre vê clientes vinculados a ele (via appointments)
    public static function canViewAny(): bool        { return Permission::isTenantMember(); }
    public static function canCreate(): bool         { return Permission::isGestorOrAbove(); }
    public static function canEdit(Model $r): bool   { return Permission::isGestorOrAbove(); }
    public static function canDelete(Model $r): bool { return Permission::isGestorOrAbove(); }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit'   => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);

        $user = auth()->user();

        if ($user && $user->isProfissional() && ! $user->hasPermission(User::PERM_VER_TODOS_CLIENTES)) {
            $professionalId = $user->professional_id;
            if ($professionalId) {
                $query->whereHas('appointments', fn ($q) => $q->where('professional_id', $professionalId));
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }
}
