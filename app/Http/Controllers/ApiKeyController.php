<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(Request $request, Project $project): View
    {
        $this->authorize('view', $project);

        $keys = $project->apiKeys()->latest()->get();

        // Pull newly generated plaintext key from session (shown once)
        $newKey = session()->pull('new_api_key');

        return view('api-keys.index', compact('project', 'keys', 'newKey'));
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('view', $project);

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        // Enforce a reasonable key limit per project
        if ($project->apiKeys()->where('status', 'active')->count() >= 10) {
            return redirect()
                ->route('projects.api-keys.index', $project)
                ->with('error', 'Maximum of 10 active keys per project. Revoke one first.');
        }

        ['model' => $key, 'plaintext' => $plaintext] = ApiKey::generate(
            $project,
            $request->name
        );

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Generated API key: {$key->name} ({$key->prefix}...)");

        // Flash plaintext to session — shown once on next page load
        session()->flash('new_api_key', $plaintext);

        return redirect()->route('projects.api-keys.index', $project);
    }

    public function revoke(Request $request, Project $project, ApiKey $apiKey): RedirectResponse
    {
        $this->authorize('view', $project);

        abort_if($apiKey->project_id !== $project->id, 404);

        $apiKey->update(['status' => 'revoked']);

        activity()
            ->causedBy($request->user())
            ->performedOn($project)
            ->log("Revoked API key: {$apiKey->name} ({$apiKey->prefix}...)");

        return redirect()
            ->route('projects.api-keys.index', $project)
            ->with('success', "API key \"{$apiKey->name}\" has been revoked.");
    }
}
