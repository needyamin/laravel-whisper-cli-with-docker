<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EnglishAI - Speaking Test Platform</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
  <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-bg {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
  </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-gradient" href="#">
                <i class="bi bi-mic-fill me-2"></i>EnglishAI
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#pricing">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#technology">Technology</a>
                    </li>
                </ul>
                
                <div class="d-flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary me-2">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-gradient">Get Started</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <div class="mb-4">
                        <span class="badge bg-primary bg-gradient mb-3">
                            <i class="bi bi-lightning-fill me-1"></i>Powered by Cloudflare Whisper AI
                        </span>
</div>

                    <h1 class="display-4 fw-bold mb-4">
                        Master English <span class="text-gradient">Speaking</span>
                    </h1>
                    
                    <p class="lead text-muted mb-4">
                        Experience the future of language learning with our AI-powered English speaking tests. 
                        Get instant feedback, earn certificates, and track your progress.
                    </p>
                    
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        @auth
                            <a href="{{ route('test.show') }}" class="btn btn-gradient btn-lg">
                                <i class="bi bi-play-circle me-2"></i>Start Your Test
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-gradient btn-lg">
                                <i class="bi bi-person-plus me-2"></i>Get Started Free
                            </a>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </a>
                        @endauth
</div>

                    <div class="d-flex flex-wrap gap-4 text-muted">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>AI-Powered Assessment</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Instant Feedback</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Digital Certificates</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 text-center">
                    <div class="position-relative">
                        <div class="bg-white rounded-4 shadow-lg p-5">
                            <i class="bi bi-mic-fill text-primary" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 mb-2">AI Speech Recognition</h4>
                            <p class="text-muted">Advanced technology for accurate pronunciation analysis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Why Choose EnglishAI?</h2>
                <p class="lead text-muted">Everything you need to improve your English speaking skills</p>
</div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm card-hover">
                        <div class="card-body text-center p-4">
                            <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-mic-fill text-white fs-4"></i>
                            </div>
                            <h5 class="card-title">AI-Powered Assessment</h5>
                            <p class="card-text text-muted">Advanced speech recognition technology provides accurate pronunciation and fluency analysis.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm card-hover">
                        <div class="card-body text-center p-4">
                            <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-lightning-fill text-white fs-4"></i>
                            </div>
                            <h5 class="card-title">Instant Feedback</h5>
                            <p class="card-text text-muted">Get detailed feedback on your pronunciation, accuracy, and fluency immediately after each test.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm card-hover">
                        <div class="card-body text-center p-4">
                            <div class="bg-warning bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-award-fill text-white fs-4"></i>
                            </div>
                            <h5 class="card-title">Digital Certificates</h5>
                            <p class="card-text text-muted">Earn digital certificates when you pass tests, showcasing your English speaking achievements.</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm card-hover">
                        <div class="card-body text-center p-4">
                            <div class="bg-info bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                <i class="bi bi-graph-up text-white fs-4"></i>
                            </div>
                            <h5 class="card-title">Progress Tracking</h5>
                            <p class="card-text text-muted">Monitor your improvement over time with detailed progress reports and test history.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Simple Pricing</h2>
                <p class="lead text-muted">Choose the plan that works for you</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                @php
                    $pricingPlans = \App\Models\PricingPlan::where('is_active', true)->orderBy('price')->get();
                @endphp
                
                @foreach($pricingPlans as $plan)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm {{ $plan->name === 'Test Pack' ? 'border-primary border-2' : '' }}">
                            @if($plan->name === 'Test Pack')
                                <div class="card-header bg-primary text-white text-center">
                                    <span class="badge bg-light text-primary">Most Popular</span>
                                </div>
                            @endif
                            
                            <div class="card-body text-center p-4">
                                <h5 class="card-title">{{ $plan->name }}</h5>
                                <p class="text-muted">{{ $plan->description }}</p>
                                
                                <div class="mb-4">
                                    <span class="display-4 fw-bold text-gradient">${{ number_format($plan->price / 100, 2) }}</span>
                                    <span class="text-muted">/{{ $plan->test_limit == 1 ? 'test' : ($plan->test_limit == -1 ? 'month' : 'pack') }}</span>
                                </div>
                                
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check text-success me-2"></i>
                                        {{ $plan->test_limit == -1 ? 'Unlimited' : $plan->test_limit }} speaking tests
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check text-success me-2"></i>
                                        AI-powered feedback
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check text-success me-2"></i>
                                        Digital certificates
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check text-success me-2"></i>
                                        Progress tracking
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="text-center mt-5">
                <div class="alert alert-success d-inline-flex align-items-center" role="alert">
                    <i class="bi bi-gift-fill me-2"></i>
                    <strong>Your first test is completely FREE!</strong>
                </div>
            </div>
        </div>
    </section>

    <!-- Technology Section -->
    <section id="technology" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3">Powered by Advanced Technology</h2>
                <p class="lead text-muted">Built with the latest AI technology for the best results</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-cloud-fill text-white fs-3"></i>
                        </div>
                        <h5>Cloudflare Whisper AI</h5>
                        <p class="text-muted">Advanced speech recognition powered by Cloudflare's Worker AI infrastructure.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-lightning-fill text-white fs-3"></i>
                        </div>
                        <h5>Real-time Processing</h5>
                        <p class="text-muted">Instant feedback and scoring using advanced algorithms for pronunciation and fluency.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-warning bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-shield-check text-white fs-3"></i>
                        </div>
                        <h5>Secure & Private</h5>
                        <p class="text-muted">Your audio data is processed securely with enterprise-grade encryption.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-3">
                    <div class="text-center">
                        <div class="bg-info bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="bi bi-heart-fill text-white fs-3"></i>
                        </div>
                        <h5>User-Friendly</h5>
                        <p class="text-muted">Intuitive interface with microphone reset and comprehensive error handling.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-mic-fill me-2"></i>EnglishAI
                    </h5>
                    <p class="text-muted mb-3">
                        The most advanced AI-powered English speaking assessment platform. 
                        Experience accurate pronunciation analysis and instant feedback.
                    </p>
                    <div class="d-flex gap-2">
                        <span class="badge bg-primary">Laravel 12</span>
                        <span class="badge bg-info">Cloudflare AI</span>
                        <span class="badge bg-success">Stripe Payments</span>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Features</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">AI Speech Recognition</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Instant Feedback</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Digital Certificates</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Progress Tracking</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Technology</h6>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Cloudflare Whisper AI</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Laravel Framework</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Bootstrap 5</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">JavaScript ES6+</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li><a href="#pricing" class="text-muted text-decoration-none">Pricing</a></li>
                        <li><a href="#technology" class="text-muted text-decoration-none">Technology</a></li>
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="text-muted text-decoration-none">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('register') }}" class="text-muted text-decoration-none">Get Started</a></li>
                        @endauth
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6">
                    <h6 class="fw-bold mb-3">Info</h6>
                    <ul class="list-unstyled">
                        <li class="text-muted">Version 1.0.0</li>
                        <li class="text-muted">Last Updated: {{ date('M d, Y') }}</li>
                    </ul>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="text-muted mb-0">
                        &copy; 2025 EnglishAI Speaking Test Platform. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-arrow-up me-1"></i>Back to Top
                    </button>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>