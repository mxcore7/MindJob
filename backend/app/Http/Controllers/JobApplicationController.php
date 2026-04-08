<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\Job;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    // List user's applications
    public function index(Request $request)
    {
        $applications = JobApplication::where('user_id', $request->user()->id)
            ->with('job')
            ->orderBy('applied_at', 'desc')
            ->get();

        return response()->json($applications);
    }

    // Apply to a job
    public function store(Request $request)
    {
        $request->validate([
            'job_id' => 'required|exists:jobs,id',
        ]);

        $existing = JobApplication::where('user_id', $request->user()->id)
            ->where('job_id', $request->job_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already applied to this job'], 409);
        }

        $application = JobApplication::create([
            'user_id' => $request->user()->id,
            'job_id' => $request->job_id,
            'status' => 'applied',
        ]);

        $application->load('job');

        return response()->json($application, 201);
    }

    // Delete application
    public function destroy(Request $request, $id)
    {
        $application = JobApplication::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $application->delete();

        return response()->json(['message' => 'Application withdrawn']);
    }
}
