<?php

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointment;
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

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Agendamentos';

    protected static ?string $modelLabel = 'Agendamento';

    protected static ?string $pluralModelLabel = 'Agendamentos';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool        { return Permission::isTenantMember(); }
    public static function canCreate(): bool         { return Permission::canManageAppointments(); }
    public static function canEdit(Model $r): bool   { return Permission::canEditAppointments(); }
    public static function canDelete(Model $r): bool { return Permission::canCancelAppointments(); }

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'edit'   => EditAppointment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['customer', 'service', 'professional']);

        $user = auth()->user();

        if ($user && $user->isProfissional() && ! $user->hasPermission(User::PERM_VER_AGENDA_GERAL)) {
            $professionalId = $user->professional_id;
            if ($professionalId) {
                $query->where('professional_id', $professionalId);
            } else {
                $query->whereRaw('0 = 1');
            }
        }

        return $query;
    }
}
