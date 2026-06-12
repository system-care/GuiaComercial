<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(string $slug): View
    {
        $tenant = Tenant::where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        return view('booking.show', compact('tenant'));
    }
}
