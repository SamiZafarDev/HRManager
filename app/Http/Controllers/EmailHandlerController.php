<?php

namespace App\Http\Controllers;

use App\Models\InterviewDetails;
use App\Models\InterviewSchedule;
use App\Models\StaticEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Psy\CodeCleaner\AssignThisVariablePass;

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
    public function getEmailWithAttributes($emailTemplate, $docId, $company_name){
        $interviewSchedules = InterviewDetails::where('user_id', Auth::id())->where('doc_id', $docId)->first();

        if($interviewSchedules){

            $this->replaceTagsWithData($emailTemplate, $interviewSchedules, $company_name);

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

    private function replaceTagWithData(&$emailTemplate, $dataToReplace){
        try {
            foreach ($dataToReplace as $data) {
                $emailTemplate = str_replace($data->tag, $data->valueToReplace, $emailTemplate);
            }
        } catch (\Throwable $th) {
            echo('Can\'t find any start_time');
        }
    }

    private function replaceTagsWithData(&$emailTemplate, $interviewSchedules, $companyName){
        try {
            $startTime = $interviewSchedules['start_time'];
            $emailTemplate = str_replace('{start time}', $startTime, $emailTemplate);
        } catch (\Throwable $th) {
            echo('Can\'t find any start_time');
        }

        try {
            $endTime = $interviewSchedules['end_time'];
            $emailTemplate = str_replace('{end time}', $endTime, $emailTemplate);
        } catch (\Throwable $th) {
            echo('Can\'t find any end_time');
        }

        try {
            $emailTemplate = str_replace('{company name}', $companyName, $emailTemplate);
        } catch (\Throwable $th) {
            echo('Can\'t find any end_time');
        }
    }


    public function sendEmailDirect(Request $request)
    {
        $subject = $request->subject;
        $emailContent = $request->content; // Use the email template as the message
        $email = $request->email;

        // Send the email as HTML
        try {
            Mail::html($emailContent, function ($mail) use ($email, $subject) {
                $mail->to($email)
                    ->subject($subject);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function sendEmail(Request $request)
    {
        $subject = $request->subject;
        $emailContent = $request->content; // Use the email template as the message
        $email = $request->email;

        if(isset($emailContent))
        {
            $defaultTemplate = view('emailHandler.emailTemplate')->render();
            $emailContent = html_entity_decode($defaultTemplate);
        }

        // Send the email as HTML
        try {
            Mail::html($emailContent, function ($mail) use ($email, $subject) {
                $mail->to($email)
                    ->subject($subject);
            });
        } catch (\Throwable $th) {
            return response()->json([
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function sendForgetPasswordEmail(Request $request)
    {
        $emailTemplate = $this->replaceTagWithData(view('emailHandler.emailTemplate_forgertPassword')->render(), [
            (object)[
                'tag' => '{reset_link}',
                'valueToReplace' => "https://preview--hr-manager.lovable.app/reset-password?token=" . $request->token,
            ],
            (object)[
                'tag' => '{email}',
                'valueToReplace' => $request->email,
            ],
        ]);

        return $this->sendEmailDirect(new Request([
            'subject' => "Interview Invite",
            'email' => $request->email,
            'email_template' => $emailTemplate,
        ]));
    }
}
