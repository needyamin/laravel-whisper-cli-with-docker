<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - English Speaking Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900">English Speaking Test</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="{{ route('test.show') }}" class="text-gray-600 hover:text-gray-900">New Test</a>
                    <span class="text-gray-700">{{ Auth::user()->name }}</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">My Certificates</h2>
            <p class="text-gray-600">View and download your earned certificates</p>
        </div>

        @if($certificates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($certificates as $certificate)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">Certificate</div>
                            <div class="text-sm text-gray-500">{{ $certificate->certificate_number }}</div>
                        </div>
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Score:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->score_achieved }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Grade:</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $certificate->grade === 'A+' || $certificate->grade === 'A' ? 'bg-green-100 text-green-800' :
                                   ($certificate->grade === 'B' ? 'bg-blue-100 text-blue-800' :
                                   ($certificate->grade === 'C' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $certificate->grade }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Issued:</span>
                            <span class="text-sm text-gray-900">{{ $certificate->issued_at->format('M d, Y') }}</span>
                        </div>
                        @if($certificate->expires_at)
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Expires:</span>
                            <span class="text-sm text-gray-900">{{ $certificate->expires_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="text-xs text-gray-500 mb-2">
                            Test Paragraph:
                        </div>
                        <div class="text-sm text-gray-700 mb-4">
                            {{ Str::limit($certificate->test->paragraph->content, 100) }}
                        </div>
                        
                        <div class="flex space-x-2">
                            <button onclick="downloadCertificate({{ $certificate->id }})" 
                                    class="flex-1 bg-indigo-600 text-white text-sm font-medium py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                Download
                            </button>
                            <a href="{{ route('test.results', $certificate->test->id) }}" 
                               class="flex-1 bg-gray-200 text-gray-800 text-sm font-medium py-2 px-4 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                                View Results
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white shadow rounded-lg p-6 text-center">
            <div class="text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No certificates yet</h3>
                <p class="mt-1 text-sm text-gray-500">Pass a speaking test with 70% or higher to earn your first certificate.</p>
                <div class="mt-6">
                    <a href="{{ route('test.show') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Take a Test
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        async function downloadCertificate(certificateId) {
            try {
                const response = await fetch(`/certificate/${certificateId}/download`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    // For now, just show certificate details
                    // In production, this would generate and download a PDF
                    alert(`Certificate Details:\n\nCertificate Number: ${data.certificate_number}\nScore: ${data.score}%\nGrade: ${data.grade}\nIssued: ${data.issued_at}\nExpires: ${data.expires_at || 'Never'}`);
                } else {
                    alert('Failed to download certificate');
                }
            } catch (error) {
                console.error('Error downloading certificate:', error);
                alert('Error downloading certificate');
            }
        }
    </script>
</body>
</html>