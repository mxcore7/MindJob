<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'ilike', '%' . $search . '%')
                  ->orWhere('company', 'ilike', '%' . $search . '%');
        }

        if ($request->has('location')) {
            $query->where('location', 'ilike', '%' . $request->location . '%');
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'description' => 'required|string',
            'skills_required' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
        ]);

        $job = Job::create($validated);
        return response()->json($job, 201);
    }

    public function show(Job $job)
    {
        return response()->json($job);
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'company' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'skills_required' => 'nullable|array',
            'location' => 'nullable|string|max:255',
            'salary' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
        ]);

        $job->update($validated);
        return response()->json($job);
    }

    public function destroy(Job $job)
    {
        $job->delete();
        return response()->json(null, 204);
    }
}
