<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desktop Access Required</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #b87333 0%, #8b5a2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #b87333 0%, #8b5a2b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 45px;
            height: 45px;
            fill: white;
        }

        h1 {
            color: #1a1a1a;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        p {
            color: #4a4a4a;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .device-info {
            background: #f7fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 25px;
        }

        .device-info p {
            margin: 0;
            font-size: 14px;
            color: #4a5568;
        }

        .device-info strong {
            color: #2d3748;
        }

        /* Hide on desktop, show on mobile */
        @media (min-width: 768px) {
            body {
                background: #f8f9fa;
            }
            
            .container {
                display: none;
            }
            
            body::after {
                content: "Welcome! This page is optimized for desktop viewing.";
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #2d3748;
                padding: 40px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 3H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h3l-1 1v2h12v-2l-1-1h3c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 13H4V5h16v11z"/>
            </svg>
        </div>
        
        <h1>Desktop Access Required</h1>
        
        <p>This website is designed for desktop and laptop computers only. For the best experience, please access this site using a desktop device with a larger screen.</p>
        
        <div class="device-info">
            <p><strong>Why?</strong> Our platform requires a larger screen and keyboard for optimal functionality and user experience.</p>
        </div>
    </div>
</body>
</html>