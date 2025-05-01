<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AISettings;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTrait;

class AISettingsController extends Controller
{
    use ResponseTrait;
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        AISettings::updateOrCreate(
            ['user_id' => Auth::id()], // Matching criteria
            ['prompt' => $request->prompt] // Values to update or create
        );

        if ($request->header('Accept') == 'application/json'){
            return ResponseTrait::success([], 'AI Prompt saved successfully!');
        }
        return redirect()->back()->with('success', 'AI Prompt saved successfully!');
    }
    public function get()
    {
        $aiSettings = AISettings::where('user_id', Auth::id())->first();

        if ($aiSettings) {
            return ResponseTrait::success([
                'prompt' => $aiSettings->prompt,
            ], 'AI Prompt saved successfully!');
        } else {
            return ResponseTrait::error([
                'prompt' => $aiSettings->prompt,
            ], 'AI Prompt not found');
        }
    }

    public function index()
    {
        $aiSettings = AISettings::where('user_id', Auth::id())->get();
        return view('ai_settings.index', compact('aiSettings'));
    }
}
