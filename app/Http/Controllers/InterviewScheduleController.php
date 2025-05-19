<?php
// filepath: /Applications/XAMPP/xamppfiles/htdocs/HRManager/app/Http/Controllers/InterviewScheduleController.php
namespace App\Http\Controllers;

use App\Models\InterviewSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InterviewScheduleController extends Controller
{
    /**
     * Display a listing of the interview schedules.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $interviewSchedules = InterviewSchedule::where('user_id', Auth::id())->get();
        return view('interviewSchedules.index', compact('interviewSchedules'));
    }

    /**
     * Show the form for creating a new interview schedule.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('interviewSchedules.create');
    }

    /**
     * Store a newly created interview schedule in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'interview_offset' => 'required|integer|min:1',
            'number_of_interviews' => 'required|integer|min:1',
        ]);

        InterviewSchedule::create([
            'user_id' => Auth::id(),
            'interview_offset' => $request->interview_offset,
            'number_of_interviews' => $request->number_of_interviews,
        ]);

        return redirect()->route('interviewSchedules.index')->with('success', 'Interview schedule created successfully.');
    }

    /**
     * Show the form for editing the specified interview schedule.
     *
     * @param  \App\Models\InterviewSchedule  $interviewSchedule
     * @return \Illuminate\Http\Response
     */
    public function edit(InterviewSchedule $interviewSchedule)
    {
        return view('interviewSchedules.edit', compact('interviewSchedule'));
    }

    /**
     * Update the specified interview schedule in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\InterviewSchedule  $interviewSchedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, InterviewSchedule $interviewSchedule)
    {
        $request->validate([
            'interview_offset' => 'required|integer|min:1',
            'number_of_interviews' => 'required|integer|min:1',
        ]);

        $interviewSchedule->update([
            'interview_offset' => $request->interview_offset,
            'number_of_interviews' => $request->number_of_interviews,
        ]);

        return redirect()->route('interviewSchedules.index')->with('success', 'Interview schedule updated successfully.');
    }

    /**
     * Remove the specified interview schedule from storage.
     *
     * @param  \App\Models\InterviewSchedule  $interviewSchedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(InterviewSchedule $interviewSchedule)
    {
        $interviewSchedule->delete();
        return redirect()->route('interviewSchedules.index')->with('success', 'Interview schedule deleted successfully.');
    }


    public function getInterviewSchedule($request, EmailHandlerController $emailHandlerController)
    {
        return $emailHandlerController->getInterviewEmailTemplate($request);
    }
}
