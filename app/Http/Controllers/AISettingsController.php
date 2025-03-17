<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AISettings;
use Illuminate\Support\Facades\Auth;

class AISettingsController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string',
        ]);

        AISettings::updateOrCreate(
            ['user_id' => Auth::id()], // Matching criteria
            ['prompt' => $request->prompt] // Values to update or create
        );

        return redirect()->back()->with('success', 'AI Prompt saved successfully!');
    }
    public function get()
    {
        $aiSettings = AISettings::where('user_id', Auth::id())->first();

        if ($aiSettings) {
            return response()->json([
                'success' => true,
                'prompt' => $aiSettings->prompt,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'prompt' => null,
            ]);
        }
    }

    public function index()
    {
        $aiSettings = AISettings::where('user_id', Auth::id())->get();
        return view('ai_settings.index', compact('aiSettings'));
    }
}
