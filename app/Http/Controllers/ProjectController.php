<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $projects = $request->user()
            ->projects()
            ->active()
            ->latest()
            ->get();

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'environment' => ['required', 'in:test,staging'],
            'provider'    => ['required', 'in:paystack,flutterwave,stripe'],
            'color'       => ['nullable', 'string'],
        ]);

        $project = $request->user()->projects()->create([
            'name'        => $validated['name'],
            'slug'        => \Str::slug($validated['name']) . '-' . \Str::random(6),
            'description' => $validated['description'] ?? null,
            'environment' => $validated['environment'],
            'provider'    => $validated['provider'],
            'color'       => match ($validated['provider']) {
                'paystack'    => '#00C3F7',
                'flutterwave' => '#F5A623',
                'stripe'      => '#635BFF',
                default       => '#0e8de6',
            },
            'status'      => 'active',
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Created project: {$project->name} ({$project->providerLabel()})");

        session(['active_project_id' => $project->id]);

        return redirect()
            ->route('projects.paystack.overview', $project)
            ->with('success', "Project created. Your {$project->providerLabel()} sandbox is ready.");
    }

    public function show(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        // Remember which project the user is working in
        session(['active_project_id' => $project->id]);

        return view('projects.show', compact('project'));
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        // Soft-archive rather than hard delete for demo safety
        $project->update(['status' => 'archived']);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log('Archived project: ' . $project->name);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project archived.');
    }

    public function switch(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);
        session(['active_project_id' => $project->id]);
        return redirect()->route('projects.paystack.overview', $project);
    }
}
