<?php

namespace App\Http\Controllers;

use App\Models\InterviewDetails;
use App\Models\InterviewSchedule;
use App\Models\StaticEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

            $this->replaceTagsWithData($emailTemplate, $interviewSchedules);

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

    private function replaceTagsWithData(&$emailTemplate, $interviewSchedules){
        try {
            $startTime = $interviewSchedules['start_time'];
            $emailTemplate = str_replace('{start time}', $startTime, $emailTemplate);
        } catch (\Throwable $th) {
            echo('Can\'t find any start_time');
        }

        try {
            $startTime = $interviewSchedules['end_time'];
            $emailTemplate = str_replace('{end time}', $startTime, $emailTemplate);
        } catch (\Throwable $th) {
            echo('Can\'t find any end_time');
        }
    }


    public function sendEmail(Request $request)
    {
        $subject = "Interview Invite";
        // $emailContent = $request->emailContent; // Use the email template as the message
        $email = $request->email;

        // if(isset($emailContent))
        {
            $defaultTemplate = view('emailHandler.emailTemplate')->render();
            $emailContent = html_entity_decode($defaultTemplate);
        }

        // Mail::raw($emailContent, function ($mail) use ($email, $subject) {
        //     $mail->to($email)
        //         ->subject($subject);
        // });

        // Send the email as HTML
        Mail::html($emailContent, function ($mail) use ($email, $subject) {
            $mail->to($email)
                ->subject($subject);
        });
    }
}
