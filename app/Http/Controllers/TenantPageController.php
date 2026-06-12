<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;

class TenantPageController extends Controller
{
    public function show(string $slug): View
    {
        $tenant = Tenant::with(['settings', 'niche'])
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $services = \App\Models\Service::forTenant($tenant->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $professionals = \App\Models\Professional::forTenant($tenant->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $settings = $tenant->settings?->settings ?? [];

        return view('tenant.landing', compact('tenant', 'services', 'professionals', 'settings'));
    }
}
