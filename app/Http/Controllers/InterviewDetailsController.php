<?php

namespace App\Http\Controllers;

use App\Models\InterviewDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ResponseTrait;

class InterviewDetailsController extends Controller
{
    /**
     * Display a listing of the interview details.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $interviewDetails = InterviewDetails::where('user_id', Auth::id())->get();

        if ($request->header('Accept') == 'application/json'){
            return ResponseTrait::success($interviewDetails, 'Interview details retrieved successfully.');
        }
        return view('interviewDetails.index', compact('interviewDetails'));
    }
    public function get(Request $request)
    {
        try {
            $interviewDetails = InterviewDetails::where('user_id', Auth::id())->get();
            return ResponseTrait::success($interviewDetails, 'Interview details retrieved successfully.');
        } catch (\Throwable $th) {
            return ResponseTrait::error($th->getMessage(), 'Error retrieving interview details.');
        }
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
            'end_time' => 'optional|date_format:Y-m-d\TH:i|after:start_time',
        ]);

        try {
            InterviewDetails::create([
                'user_id' => Auth::id(),
                'doc_id' => $request->doc_id,
                'name' => $request->name,
                'email' => $request->email,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            $DocManagerController = new DocManagerController();
            $DocManagerController->sendEmailscheduleInterview($request, $this);

            if($request->header('Accept') == 'application/json'){
                return ResponseTrait::success($DocManagerController, 'Interview detail created successfully.');
            }else{
                return redirect()->route('interviewDetails.index')->with('success', 'Interview detail created successfully.');
            }
        } catch (\Throwable $th) {
            //throw $th;
            if($request->header('Accept') == 'application/json'){
                return ResponseTrait::error('Error creating interview detail: ' . $th->getMessage());
            }else{
                return redirect()->back()->with('error', 'Error creating interview detail: ' . $th->getMessage());
            }
        }
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
        try {
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

            if($request->header('Accept') == 'application/json'){
                return ResponseTrait::success($interviewDetail, 'Interview detail updated successfully.');
            }else{
                return redirect()->route('interviewDetails.index')->with('success', 'Interview detail updated successfully.');
            }
        } catch (\Throwable $th) {
            //throw $th;
            if($request->header('Accept') == 'application/json'){
                return ResponseTrait::error('Error creating interview detail: ' . $th->getMessage());
            }else{
                return redirect()->back()->with('error', 'Error creating interview detail: ' . $th->getMessage());
            }
        }
    }

    /**
     * Remove the specified interview detail from storage.
     *
     * @param  \App\Models\InterviewDetails  $interviewDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(InterviewDetails $interviewDetail)
    {
        try {
            $interviewDetail->delete();
            if($interviewDetail->header('Accept') == 'application/json'){
                return ResponseTrait::success($interviewDetail, 'Interview detail deleted successfully.');
            }
            else{
                return redirect()->route('interviewDetails.index')->with('success', 'Interview detail deleted successfully.');
            }
        } catch (\Throwable $th) {
            //throw $th;
            if($interviewDetail->header('Accept') == 'application/json'){
                return ResponseTrait::error('Error deleting interview detail: ' . $th->getMessage());
            }else{
                return redirect()->back()->with('error', 'Error deleting interview detail: ' . $th->getMessage());
            }
        }
    }


}
