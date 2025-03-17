<?php

namespace App\Http\Controllers;

use App\Helpers\FileManager;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\StorageFolder;
use App\Http\Requests\ProfilePictureRequest;
use App\Models\DocumentsDetails;
use Exception;
use Illuminate\Support\Facades\Auth;

class RankedDocumentsController extends Controller{
    public function index()
    {
        return view('interviewDetails.index', compact('interviewDetails'));
    }

    public function getDocumentDetails()
    {
        if (Auth::user() == null) {
            return response()->json([

                'data' => 'not found'
            ]);
        }

        $documentDetails = DocumentsDetails::with('doc') // Correct method is 'with' not 'include'
            ->whereHas('doc', function ($query) {
                $query->where('user_id', Auth::user()->id); // Filtering by user_id from Documents table
            })
            ->orderBy('rank', 'desc') // Sort by rank in descending order
            ->get();


        foreach ($documentDetails as $doc) {
            $rank = 0; // Default rank to 0
            if (isset($doc->stats) && preg_match('/Rank:\s*(\d+)/', $doc->stats, $matches)) {
                $rank = (int)$matches[1]; // Convert to integer if match found
            }
            // $email = null;
            // if (isset($doc->stats) && preg_match('/Email:\s*([^\s]+)/', $doc->stats, $matches)) {
            //     $email = $matches[1]; // Extract the email address
            // }
            $email = null;
            if (isset($doc->stats) && preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $doc->stats, $matches)) {
                $email = $matches[0]; // Extract the email address
            }
            $doc->email = $email; // Save the rank in doc->rank
            $doc->rank = $rank; // Save the rank in doc->rank
            $doc->save(); // Save the updated document details
        }

        return response()->json($documentDetails);
    }
}
