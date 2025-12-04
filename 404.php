<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #1a1a1a;
            overflow: hidden;
        }

        .container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #b87333;
            line-height: 1;
            margin-bottom: 1rem;
            position: relative;
            display: inline-block;
        }

        .error-code::before {
            content: '404';
            position: absolute;
            top: 0;
            left: 0;
            color: #d4a574;
            z-index: -1;
            animation: glitch 3s infinite;
        }

        @keyframes glitch {
            0%, 100% {
                transform: translate(0);
                opacity: 0.8;
            }
            33% {
                transform: translate(2px, -2px);
                opacity: 0.6;
            }
            66% {
                transform: translate(-2px, 2px);
                opacity: 0.7;
            }
        }

        h1 {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #1a1a1a;
            animation: slideIn 0.8s ease-out 0.2s backwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        p {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 2rem;
            line-height: 1.6;
            animation: slideIn 0.8s ease-out 0.4s backwards;
        }







        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #d4a574;
            border-radius: 50%;
            opacity: 0.4;
            animation: rise 8s infinite ease-in;
        }

        @keyframes rise {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.4;
            }
            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        @media (max-width: 768px) {
            .error-code {
                font-size: 5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            p {
                font-size: 1rem;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
            }
        }
    </style>
</head>
<body>
    <div class="particles" id="particles"></div>
    
    <div class="container">
        <div class="error-code">404</div>
        
        <h1>Oops! Page Not Found</h1>
        
        <p>The page you're looking for seems to have wandered off. Don't worry, even the best explorers get lost sometimes.</p>
    </div>

    <script>
        // Create floating particles
        const particlesContainer = document.getElementById('particles');
        const particleCount = 20;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 8 + 's';
            particle.style.animationDuration = (Math.random() * 4 + 6) + 's';
            particlesContainer.appendChild(particle);
        }

        // Add subtle mouse movement effect
        document.addEventListener('mousemove', (e) => {
            const container = document.querySelector('.container');
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            container.style.transform = `translate(${x}px, ${y}px)`;
        });
    </script>
</body>
</html>