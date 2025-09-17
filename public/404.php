<?php
http_response_code(404);
require_once __DIR__ . '/../config.php';
ensure_session_started();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found • The Forbidden Codex</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Crimson+Text:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
</head>
<body class="error-page">
    <div class="error-container">
        <div class="error-content">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">The Ancient Page Has Vanished</h2>
            <p class="error-description">
                The mystical page you seek has been consumed by the digital void. 
                Perhaps it was never meant to be found, or the ancient spirits have hidden it from mortal eyes.
            </p>
            <div class="error-actions">
                <a href="index.php" class="btn btn-primary">Return to the Codex</a>
                <a href="products.php" class="btn btn-secondary">Browse Offerings</a>
            </div>
        </div>
        <div class="mystical-symbols">
            <div class="symbol symbol-1"></div>
            <div class="symbol symbol-2"></div>
            <div class="symbol symbol-3"></div>
        </div>
    </div>
    
    <style>
        .error-page {
            background: linear-gradient(135deg, #1C1A1B 0%, #333031 50%, #484949 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .error-container {
            text-align: center;
            z-index: 10;
            position: relative;
        }
        
        .error-code {
            font-family: 'Cinzel', serif;
            font-size: 8rem;
            font-weight: 700;
            color: #9D9999;
            text-shadow: 0 0 20px rgba(157, 153, 153, 0.5);
            margin-bottom: 1rem;
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        .error-title {
            font-family: 'Cinzel', serif;
            font-size: 2.5rem;
            color: #D8D4D3;
            margin-bottom: 1.5rem;
        }
        
        .error-description {
            font-size: 1.2rem;
            color: #9D9999;
            max-width: 600px;
            margin: 0 auto 2rem;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .mystical-symbols {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }
        
        .symbol {
            position: absolute;
            width: 100px;
            height: 100px;
            border: 2px solid rgba(157, 153, 153, 0.2);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }
        
        .symbol-1 {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .symbol-2 {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }
        
        .symbol-3 {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes glow {
            from { text-shadow: 0 0 20px rgba(157, 153, 153, 0.5); }
            to { text-shadow: 0 0 30px rgba(157, 153, 153, 0.8), 0 0 40px rgba(157, 153, 153, 0.6); }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        @media (max-width: 768px) {
            .error-code { font-size: 5rem; }
            .error-title { font-size: 2rem; }
            .error-actions { flex-direction: column; align-items: center; }
        }
    </style>
</body>
</html>