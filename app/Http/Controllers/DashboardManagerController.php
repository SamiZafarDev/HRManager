<?php

namespace App\Http\Controllers;

use App\Models\Documents;
use App\Models\DocumentsDetails;
use App\Models\InterviewDetails;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Helpers\QueueManager;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Auth;

class DashboardManagerController extends Controller
{
    public function getDashboardData(Request $request)
    {
        $userId = Auth::user()->id;

        $documents = $this->getDocumentCount($userId);
        $rankedDocuments = $this->getRankedDocumentsData($userId, $documents['totalDocuments']);
        $emailsSent = $this->getTotalEmailsSent($userId);
        $pendingInterviews = $this->getPendingInterviews($userId);

        // Fetch and return the dashboard data
        $data = [
            'documents' => $documents,
            'rankedDocuments' => $rankedDocuments,
            'totalEmailSent' => $emailsSent,
            'pendingInterviews' => $pendingInterviews,
        ];

        return ResponseTrait::success($data, 'Dashboard data retrieved successfully');
    }

    private function getDocumentCount($userId, $lastDays = 30)
    {
        // Total documents count
        $totalDocuments = Documents::where('user_id', $userId)->count();

        // Documents count for the last 30 days
        $documentsCountOfLastMonth = Documents::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($lastDays))
            ->count();

        // Documents count for the previous 30 days
        $documentsCountOfBeforeLastMonth = Documents::where('user_id', $userId)
            ->whereBetween('created_at', [now()->subDays($lastDays*2), now()->subDays($lastDays)])
            ->count();

        // Calculate percentage change
        $percentageChange = 0;
        if ($documentsCountOfBeforeLastMonth > 0) {
            $percentageChange = (($documentsCountOfLastMonth - $documentsCountOfBeforeLastMonth) / $documentsCountOfBeforeLastMonth) * 100;
        } elseif ($documentsCountOfLastMonth > 0) {
            $percentageChange = 100; // If there were no documents in the previous period, any new documents represent a 100% increase
        }

        return [
            'totalDocuments' => $totalDocuments,
            'documentsCountOfLastMonth' => $documentsCountOfLastMonth,
            'documentsCountOfBeforeLastMonth' => $documentsCountOfBeforeLastMonth,
            'percentageChange' => round($percentageChange, 2), // Round to 2 decimal places
            'timeFrame' => 'Last '.$lastDays.' days',
        ];
    }
    private function getRankedDocumentsData($userId, $totalDocuments, $howManyRanks = 5)
    {
        // Count ranked documents
        $rankedDocuments = DocumentsDetails::whereHas('doc', function ($query) use ($userId) {
                $query->where('user_id', $userId); // Filter by user_id from Documents table
            })
            ->whereNotNull('rank'); // Ensure the document has a rank

        $rankedDocumentsCount = $rankedDocuments->count(); // Ensure the document has a rank

        // Calculate percentage of ranked documents
        $percentageOfRanked = 0;
        if ($totalDocuments > 0) {
            $percentageOfRanked = ($rankedDocumentsCount / $totalDocuments) * 100;
        }

        return [
            'rankedDocuments' => $rankedDocumentsCount,
            'percentageOfTotal' => round($percentageOfRanked, 2), // Round to 2 decimal places
            'topRankedDocuments' => $rankedDocuments->orderBy('rank', 'desc')->take($howManyRanks)->get(), // Get top 5 ranked documents
        ];
    }
    private function getTotalEmailsSent($userId, $lastDays = 30)
    {
        // Total emails sent by the user
        $totalEmailsSent = InterviewDetails::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($lastDays))
            ->count();

        return [
            'totalEmailsSent' => $totalEmailsSent,
            'timeFrame' => 'Last '.$lastDays.' days',
        ];
    }
    private function getPendingInterviews($userId, $nextDays = 7)
    {
        // Count pending interviews scheduled in the next 7 days
        $pendingInterviews = InterviewDetails::where('user_id', $userId)
            ->whereBetween('start_time', [now(), now()->addDays($nextDays)])
            ->count();

        return [
            'pendingInterviews' => $pendingInterviews,
            'timeFrame' => 'Next '.$nextDays.' days',
        ];
    }

    public function getQueueCount()
    {
        try {
            return ResponseTrait::success(['queue_size'=>QueueManager::queueSize()], 'Queue count retrieved successfully');
        } catch (\Throwable $th) {
            return ResponseTrait::error('Couldn\'t recieve queue count due to: '.$th->getMessage());
        }
    }

}
