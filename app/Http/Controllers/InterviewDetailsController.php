<?php

namespace App\Http\Controllers;

use App\Models\InterviewDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewDetailsController extends Controller
{
    /**
     * Display a listing of the interview details.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $interviewDetails = InterviewDetails::where('user_id', Auth::id())->get();
        return view('interviewDetails.index', compact('interviewDetails'));
    }

    /**
     * Show the form for creating a new interview detail.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('interviewDetails.create');
    }

    /**
     * Store a newly created interview detail in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'doc_id' => 'required|exists:documents,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'start_time' => 'required|date_format:Y-m-d\TH:i',
            'end_time' => 'required|date_format:Y-m-d\TH:i|after:start_time',
        ]);

        InterviewDetails::create([
            'user_id' => Auth::id(),
            'doc_id' => $request->doc_id,
            'name' => $request->name,
            'email' => $request->email,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('interviewDetails.index')->with('success', 'Interview detail created successfully.');
    }

    /**
     * Show the form for editing the specified interview detail.
     *
     * @param  \App\Models\InterviewDetails  $interviewDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(InterviewDetails $interviewDetail)
    {
        return view('interviewDetails.edit', compact('interviewDetail'));
    }

    /**
     * Update the specified interview detail in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InterviewDetails  $interviewDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InterviewDetails $interviewDetail)
    {
        $request->validate([
            'doc_id' => 'required|exists:documents,id',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'start_time' => 'required|date_format:Y-m-d\TH:i',
            'end_time' => 'required|date_format:Y-m-d\TH:i|after:start_time',
        ]);

        $interviewDetail->update([
            'doc_id' => $request->doc_id,
            'name' => $request->name,
            'email' => $request->email,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()->route('interviewDetails.index')->with('success', 'Interview detail updated successfully.');
    }

    /**
     * Remove the specified interview detail from storage.
     *
     * @param  \App\Models\InterviewDetails  $interviewDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(InterviewDetails $interviewDetail)
    {
        $interviewDetail->delete();
        return redirect()->route('interviewDetails.index')->with('success', 'Interview detail deleted successfully.');
    }
}
