<?php

namespace App\Http\Controllers;

use App\Enums\StorageFolder;
use App\Helpers\DocumentProcessor;
use App\Helpers\FileManager;
use App\Http\Requests;
use App\Jobs\RankDocumentsJob;
use App\Models\AISettings;
use App\Models\Documents;
use App\Models\DocumentsDetails;
use App\Services\LlamaService;
use App\Traits\ResponseTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use League\CommonMark\Node\Block\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function Laravel\Prompts\alert;

class DocManagerController extends Controller
{
    use ResponseTrait;
    public function upload(Requests\DocumentUploadRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user == null) throw new Exception("User not found.");

            $documents = $request->file('documents');
            $documentsUploaded = [];
            foreach ($documents as $document) {
                $this->uploadDocument($document, $user->id, $documentsUploaded);
            }

            if ($request->header('Accept') == 'application/json'){
                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully.',
                    'data' => $documentsUploaded,
                ]);
            }
            session()->flash('success', 'Document uploaded successfully.');
            return redirect()->back();
        } catch (\Throwable $th) {

            if ($request->header('Accept') == 'application/json'){
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to upload document: ' . $th->getMessage(),
                ]);
            }
            session()->flash('error', 'Unable to upload document: ' . $th->getMessage());
            return redirect()->back();
        }
    }

    private function uploadDocument($document, $user_id, &$documentsUploaded)
    {
        try {
            $documentPath = FileManager::uploadFile($document, StorageFolder::DOCUMENTS);
            if ($documentPath == null) {
                $documentsUploaded[] = [
                    'success' => false,
                    'name' => $document->name,
                ];
            }

            $parts = explode('/', $documentPath);
            $documentName = end($parts);

            $document = Documents::create([
                'name' => $documentName,
                'user_id' => $user_id,
            ]);
            $document->save();
            $documentsUploaded[] = $document;
        } catch (\Throwable $th) {
            FileManager::deleteFile($document, StorageFolder::DOCUMENTS);
        }
    }

    public function delete(Request $request)
    {
        try {
            $user = Auth::user();
            $docId = $request->doc_id;
            if (empty($docId)) {
                throw new Exception("No document ID provided.");
            }

            $document = Documents::where('id', $docId)->where('user_id', $user->id)->first();
            if ($document == null) {
                throw new Exception("Document not found.");
            }

            DocumentsDetails::where('doc_id', $docId)->delete();
            $document->delete();
            FileManager::deleteFile($document->name, StorageFolder::DOCUMENTS);

            return ResponseTrait::success([], 'Document deleted successfully');
        } catch (\Throwable $th) {
            return ResponseTrait::error('Failed to delete document', ['error' => $th->getMessage()]);
        }
    }
    public function getMyDocuments(Request $request)
    {
        $user = Auth::user();
        if ($user == null) throw new Exception("User not found.");
        return response()->json($this->getDocumentsOfUser($user->id));
    }
    public function deleteMyDocuments(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user == null) throw new Exception("User not found.");

            $documents = $this->getDocumentsOfUser($user->id);

            $response = response()->json([
                'success' => false,
                'message' => 'Document not found',
            ]);

            if (is_countable($documents)) {
                if (count($documents) != 0 && get_class($documents[0]) == Documents::class)
                    $response = $this->deleteDocuments($documents);
            } else {
                return $documents;
            }
            return $response;
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    private function getDocuments($documents_name)
    {
        try {
            $documents = Documents::whereIn('name', $documents_name)->get();
            if ($documents == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No document found.',
                ]);
            }
            return $documents;
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Can\'t document found.',
                'error' => $th->getMessage(),
            ]);
        }
    }
    private function getDocumentsOfUser($user_id)
    {
        try {
            $documents = Documents::where('user_id', $user_id)->get();
            if ($documents == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'No document found.',
                ]);
            }
            return $documents;
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Can\'t found document.',
                'error' => $th->getMessage(),
            ]);
        }
    }


    private function deleteDocuments($documents)
    {
        try {
            $documentIds = $documents->pluck('id')->toArray();
            if (empty($documentIds)) {
                throw new \Exception('No document IDs provided.');
            }
            Documents::whereIn('id', $documentIds)->delete();

            foreach ($documents as $document) {
                FileManager::deleteFile($document->name, StorageFolder::DOCUMENTS);
            }

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'No document found.',
                'error' => $th->getMessage(),
            ]);
        }
    }

    public function rankDocuments(Request $request, LlamaService $llama)
    {
        $userid = Auth::user()->id;
        $documents = Documents::where('user_id', $userid)->get();

        foreach ($documents as $doc) {
            RankDocumentsJob::dispatch($doc, $userid, $llama);
        }

        return response()->json([
            'success' => true,
            'message' => 'Documents are being ranked. This may take some time.',
        ]);
    }
    // public function rankDocuments(Request $request, LlamaService $llama)
    // {
    //     $documents = Documents::where('user_id', Auth::user()->id)->get();
    //     $extractedTexts = [];

    //     foreach ($documents as $doc) {
    //         $filePath = storage_path("app/public/documents/{$doc->name}");
    //         try {
    //             $text = DocumentProcessor::extractText($filePath);
    //             $extractedTexts[] = [
    //                 'id' => $doc->id,
    //                 'name' => $doc->name,
    //                 'content' => $text
    //             ];
    //         } catch (\Exception $e) {
    //             $extractedTexts[] = [
    //                 'id' => $doc->id,
    //                 'name' => $doc->name,
    //                 'content' => 'No Content',
    //                 'error' => "Error processing {$doc->name}: " . $e->getMessage()
    //             ];
    //         }
    //     }

    //     $rankedDocs = $this->sendToAI($extractedTexts, $llama);
    //     $rankedData = $this->sortResponseInRanks($rankedDocs);

    //     $this->createDetailsOfRankedDocs($rankedData);

    //     return ResponseTrait::success([
    //         'rankedDocuments' => $rankedDocs,
    //         'rankedData' => $rankedData,
    //     ], 'Documents ranked successfully');
    // }

    public function testSortResponseInRanks(Request $request)
    {
        $docdata = $request->response;
        $rankedData = $this->sortResponseInRanks($docdata);
        $this->createDetailsOfRankedDocs($rankedData);
        return response()->json([
            'rankedData' => $rankedData,
        ]);
    }

    public function sendToAI($documents, $userid, LlamaService $llama)
    {
        // Split documents into batches of 10-20 to avoid exceeding token limits
        $batchSize = 1; // Adjust based on token size
        $chunks = array_chunk($documents, $batchSize);
        $rankedResults = [];

        foreach ($chunks as $batch) {
            $userPrompt = AISettings::where('user_id', $userid)->first();
            if ($userPrompt == null) {
                $prompt = "
                        Rank the resumes of candidates applying for a Web Frontend Developer position based on the following criteria:
                        - Relevant Experience (5+ years preferred)
                        - Stability (1+ year in a single company)
                        - Skills (React, JavaScript, HTML, CSS)
                        - Education & Certifications
                        - Projects & Portfolio";
                    $userPrompt = AISettings::create([
                    'user_id' => $userid,
                    'prompt' => $prompt,
                ]);
            }
            if ($userPrompt) {
                $prompt = "
                    Objective:
                    " . $userPrompt->prompt . "
                    Instructions:
                        - First you must rank resumes from 0 to 100. Template to follow \"Rank:32\".
                        - Limit the response to 230 characters don't exceed that.
                        - Be concise and clear in your response.
                        - Must found the email from resume and plot it at {CANDIDATE EMAIL HERE}, if doesn't exist just don't give Email in response. Template to follow \"Email:{CANDIDATE EMAIL HERE}\".
                        - Don't tell the thinking process.
                    \n\n";
            }

            foreach ($batch as $index => $doc) {
                $prompt .= ($index + 1) . ". {$doc['name']} Candidate's Resume:\n" . substr($doc['content'], 0, length:300) . "\n\n"; // Limit size
            }

            $response = $this->sendMessageToAI($prompt, $llama);
            $response['content'] = substr($doc['content'], 0, length:1000);

            $rankedResults[] = [
                "document_id" => $doc['id'],
                "document_name" => $doc['name'],
                "response" => $response,
            ];
        }

        return $rankedResults;
    }

    private function sendMessageToAI(string $prompt, LlamaService $llama)
    {
        $maxLength = 2000; // Keeping it below the 2048 token limit
        $truncatedPrompt = $this->truncatePrompt($prompt, $maxLength);

        // If truncation is still exceeding limits, send in batches
        if (strlen($truncatedPrompt) > $maxLength) {
            return $this->sendPromptInBatches($prompt, $llama, $maxLength);
        }

        $result = $llama->generateText($truncatedPrompt);

        if (isset($result['content'])) {
            $data = [
                'prompt' => $prompt,
                'response' => $result['content'],
            ];
            return $data;
        }

        return $result;
    }

    /**
     * Truncate a prompt to fit within the allowed token limit.
     */
    private function truncatePrompt(string $prompt, int $maxLength): string
    {
        return substr($prompt, 0, $maxLength);
    }

    /**
     * Send a large prompt in smaller batches.
     */
    private function sendPromptInBatches(string $prompt, LlamaService $llama, int $batchSize)
    {
        $chunks = str_split($prompt, $batchSize);
        $responses = [];

        foreach ($chunks as $chunk) {
            $result = $llama->generateText($chunk);
            $responses[] = $result['response'] ?? "Data can't be processed.";
        }

        return implode("\n\n", $responses);
    }


    public function chatWithAI(Request $request, LlamaService $llama)
    {
        return $this->sendMessageToAI($request->prompt, $llama);
    }


    public function sortResponseInRanks($rankedDocs)
    {
        $rankedArray = [];

        // Extract rank and store it in an array
        foreach ($rankedDocs as $docData) {
            $rank = 0; // Default rank to 0
            if (isset($docData['response']['response']) && preg_match('/Rank:\s*(\d+)/', str($docData['response']['response']), $matches)) {
                $rank = (int)$matches[1]; // Convert to integer if match found
            }

            // Get email from AI
            // $email = null;
            // if (isset($docData['response']['response']) && preg_match('/Email:\s*(\d+)/', str($docData['response']['response']), $matches)) {
            //     $email = (int)$matches[1]; // Convert to integer if match found
            // }

            $email = null;
            if (isset($docData['response']['response']) && preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $docData['response']['response'], $matches)) {
                $email = $matches[0]; // Extract the email address
            }

            // echo("rank" .":".$rank);
            $rankedArray[] = [
                'rank' => $rank,
                'email' => $email,
                'doc' => $docData
            ];
        }

        // Sort array in descending order based on 'rank'
        usort($rankedArray, function ($a, $b) {
            return $b['rank'] - $a['rank']; // Sort in descending order
        });

        // Print sorted ranks
        foreach ($rankedArray as $item) {
            // echo "Rank: " . $item['rank'] . " - Response: " . ($item['doc']['response'] ?? "No response") . "\n";
        }

        // Return sorted results
        return $rankedArray;
        // return array_column($rankedArray, 'doc');
    }

    // Setting Doc details
    public function createDetailsOfRankedDocs($rankedDocuments)
    {
        foreach ($rankedDocuments as $rankdocument) {
            $docDetails = [
                'doc_id' => $rankdocument['doc']['document_id'],
                // 'stats'  => substr($rankdocument['doc']['response']['content'], 0, length:500) .' response: '. $rankdocument['doc']['response']['response'],
                'stats'  => substr($rankdocument['doc']['response']['response'], 0, length:500),
                'rank'   => $rankdocument['rank'],
                'email'   => $rankdocument['email'],
            ];

            // Use updateOrCreate to update the entry if the document ID is the same, or create a new one if it doesn't exist
            DocumentsDetails::updateOrCreate(
                ['doc_id' => $rankdocument['doc']['document_id']], // Matching criteria
                $docDetails // Values to update or create
            );
        }
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
    public function previewDocument($filename)
    {
        // Define storage path
        $path = storage_path("app/public/documents/{$filename}");

        // Check if file exists
        if (!file_exists($path)) {
            abort(404, "File not found");
        }

        // Get MIME type
        $mimeType = mime_content_type($path);

        // Define supported preview MIME types
        $supportedMimeTypes = [
            'application/pdf', // PDF files
            'image/jpeg',     // JPEG images
            'image/png',      // PNG images
            'text/plain',     // Plain text files
            'text/html',      // HTML files
        ];

        // Check if the file type is supported for preview
        if (!in_array($mimeType, $supportedMimeTypes)) {
            abort(400, "File type not supported for preview");
        }

        // Return response with "inline" Content-Disposition to force preview
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }



    public function sendEmailscheduleInterview(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
            'doc_id' => 'required|exists:documents,id'
        ]);

        try {
            // Instantiate the EmailHandlerController
            $emailHandlerController = new EmailHandlerController();

            // Call the get method and get the response
            $emailResponse = $emailHandlerController->get();

            // Extract the email template from the response
            $emailTemplate = $emailResponse->getData()->email_template;

            $emailTemplate = $emailHandlerController->getEmailWithAttributes($emailTemplate, $request->doc_id)->getdata();
            $emailTemplate = $emailTemplate->email_template;
            // dd($emailTemplate);

            $email = $request->email;
            if (empty($email)) {
                throw new \Exception("No email provided.");
            }

            $subject = "Interview Invite";
            $emailContent = $emailTemplate; // Use the email template as the message

            Mail::html($emailContent, function ($mail) use ($email, $subject) {
                $mail->to($email)
                    ->subject($subject);
            });

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email.',
                'error' => $th->getMessage(),
            ]);
        }
    }
}
