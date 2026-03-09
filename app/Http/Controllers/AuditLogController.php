<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $activities = Activity::with('causer', 'subject')
            ->latest()
            ->paginate(30);

        return view('audit.index', compact('activities'));
    }
}
