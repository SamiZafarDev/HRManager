<?php

namespace App\Http\Controllers;

use App\Models\InterviewDetails;
use App\Models\InterviewSchedule;
use App\Models\StaticEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailHandlerController extends Controller
{
    /**
     * Display the email template form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $staticEmail = StaticEmail::where('user_id', Auth::id())->first();
        return view('emailHandler.index', compact('staticEmail'));
    }

    /**
     * Store or update the email template.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'email_template' => 'required|string',
        ]);

        StaticEmail::updateOrCreate(
            ['user_id' => Auth::id()],
            ['email_template' => $request->email_template]
        );

        return redirect()->route('emailHandler.index')->with('success', 'Email template saved successfully.');
    }

    /**
     * Fetch the stored email template.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        $staticEmail = StaticEmail::where('user_id', Auth::id())->first();
        $defaultTemplate = view('emailHandler.emailTemplate')->render();

        $emailTemplate = $staticEmail ? $staticEmail->email_template : html_entity_decode($defaultTemplate);

        return response()->json([
            'success' => true,
            'email_template' => $emailTemplate,
        ]);
    }
    public function getEmailWithAttributes($emailTemplate, $docId){
        $interviewSchedules = InterviewDetails::where('user_id', Auth::id())->where('doc_id', $docId)->first();
        if($interviewSchedules){
            $startTime = $interviewSchedules->start_time;
            $emailTemplate = str_replace('{start time}', $startTime, $emailTemplate);
            return response()->json([
                'success' => true,
                'email_template' => $emailTemplate,
            ]);
        }
        return response()->json([
            'success' => false,
            'email_template' => $emailTemplate,
        ]);
    }
}
