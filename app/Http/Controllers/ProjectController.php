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

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = $request->user()->projects()->create([
            'name'        => $request->name,
            'description' => $request->description,
            'environment' => $request->environment,
            'color'       => $request->color ?? '#0e8de6',
        ]);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log('Created project: ' . $project->name);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Project "' . $project->name . '" created successfully.');
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
}
