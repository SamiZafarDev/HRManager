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
        $userid = Auth::id();
        $aiSettings = AISettings::where('user_id', $userid)->first();
        if($aiSettings == null)
            $aiSettings = AISettingsController::createDefaultPrompt($userid);

        if ($aiSettings) {
            return ResponseTrait::success($aiSettings, 'AI Prompt saved successfully!');
        } else {
            return ResponseTrait::error($aiSettings, 'AI Prompt not found');
        }
    }

    public function index()
    {
        $aiSettings = AISettings::where('user_id', Auth::id())->get();
        return view('ai_settings.index', compact('aiSettings'));
    }

    public static function createDefaultPrompt($userid)
    {
        $prompt = preg_replace('/^\s+/', '', "
                - Rank the resumes of candidates applying for a Web Frontend Developer position
                - Relevant Experience (5+ years preferred)
                - Stability (1+ year in a single company)
                - Skills (React, JavaScript, HTML, CSS)
                - Education & Certifications
                - Projects & Portfolio");
        $userPrompt = AISettings::create([
            'user_id' => $userid,
            'prompt' => $prompt,
        ]);
        return $userPrompt;
    }
}
