<?php

namespace App\Http\Controllers;

use App\Models\SpeakingTest;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show user dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        $stats = [
            'total_tests' => $user->speakingTests()->count(),
            'completed_tests' => $user->speakingTests()->where('status', 'completed')->count(),
            'passed_tests' => $user->speakingTests()->whereHas('attempts', function($query) {
                $query->where('overall_score', '>=', 70);
            })->count(),
            'certificates' => $user->certificates()->where('is_valid', true)->count(),
            'average_score' => $user->speakingTests()
                ->whereHas('attempts')
                ->with('attempts')
                ->get()
                ->avg(function($test) {
                    return $test->attempts->last()?->overall_score ?? 0;
                }) ?? 0
        ];

        $recentTests = $user->speakingTests()
            ->with(['paragraph', 'attempts', 'certificate'])
            ->latest()
            ->limit(5)
            ->get();

        $certificates = $user->certificates()
            ->with('test.paragraph')
            ->where('is_valid', true)
            ->latest()
            ->get();

        return view('dashboard', compact('stats', 'recentTests', 'certificates'));
    }

    /**
     * Show test history.
     */
    public function testHistory()
    {
        $user = Auth::user();
        
        $tests = $user->speakingTests()
            ->with(['paragraph', 'attempts', 'certificate'])
            ->latest()
            ->paginate(10);

        return view('test.history', compact('tests'));
    }

    /**
     * Show certificates.
     */
    public function certificates()
    {
        $user = Auth::user();
        
        $certificates = $user->certificates()
            ->with('test.paragraph')
            ->where('is_valid', true)
            ->latest()
            ->get();

        return view('certificates.index', compact('certificates'));
    }

    /**
     * Download certificate.
     */
    public function downloadCertificate($certificateId)
    {
        $certificate = Certificate::where('id', $certificateId)
            ->where('user_id', Auth::id())
            ->where('is_valid', true)
            ->firstOrFail();

        // For now, return certificate details
        // In production, generate and return PDF
        return response()->json([
            'certificate_number' => $certificate->certificate_number,
            'score' => $certificate->score_achieved,
            'grade' => $certificate->grade,
            'issued_at' => $certificate->issued_at->format('Y-m-d'),
            'expires_at' => $certificate->expires_at?->format('Y-m-d')
        ]);
    }
}
