<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #fdf8f3 0%, #fff5eb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #5a3a2a;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle background decoration */
        .bg-decoration {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(184, 115, 51, 0.05), transparent);
            pointer-events: none;
        }

        .bg-decoration:nth-child(1) {
            top: -200px;
            right: -200px;
        }

        .bg-decoration:nth-child(2) {
            bottom: -200px;
            left: -200px;
        }

        /* Main Container */
        .container {
            text-align: center;
            z-index: 10;
            max-width: 600px;
            padding: 40px 20px;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Lock Icon */
        .icon-container {
            margin: 0 auto 40px;
            position: relative;
            width: 140px;
            height: 140px;
        }

        .lock-circle {
            width: 140px;
            height: 140px;
            background: linear-gradient(135deg, #f4a460 0%, #d4894a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(184, 115, 51, 0.2);
            animation: gentlePulse 3s ease-in-out infinite;
            position: relative;
        }

        @keyframes gentlePulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 40px rgba(184, 115, 51, 0.2);
            }
            50% {
                transform: scale(1.03);
                box-shadow: 0 15px 50px rgba(184, 115, 51, 0.3);
            }
        }

        .lock-icon {
            width: 60px;
            height: 70px;
            position: relative;
        }

        .lock-body {
            width: 50px;
            height: 40px;
            background: white;
            border-radius: 6px;
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .lock-shackle {
            width: 35px;
            height: 35px;
            border: 6px solid white;
            border-bottom: none;
            border-radius: 25px 25px 0 0;
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .keyhole {
            width: 6px;
            height: 12px;
            background: #d4894a;
            border-radius: 3px 3px 0 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .keyhole::after {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 8px solid #d4894a;
        }

        /* Typography */
        .error-code {
            font-size: 96px;
            font-weight: 700;
            color: #b87333;
            margin-bottom: 10px;
            letter-spacing: -2px;
            animation: slideIn 0.6s ease-out 0.2s both;
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

        h1 {
            font-size: 32px;
            font-weight: 600;
            color: #7a4a2a;
            margin-bottom: 20px;
            animation: slideIn 0.6s ease-out 0.3s both;
        }

        p {
            font-size: 18px;
            color: #8a6a5a;
            line-height: 1.6;
            margin-bottom: 40px;
            animation: slideIn 0.6s ease-out 0.4s both;
        }

        /* Button */
        .btn {
            display: inline-block;
            padding: 14px 36px;
            background: linear-gradient(135deg, #d4894a 0%, #b87333 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(184, 115, 51, 0.25);
            transition: all 0.3s ease;
            animation: slideIn 0.6s ease-out 0.5s both;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(184, 115, 51, 0.35);
        }

        .btn:active {
            transform: translateY(0);
        }

        /* Divider */
        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #d4894a, transparent);
            margin: 30px auto;
            animation: slideIn 0.6s ease-out 0.35s both;
        }

        /* Subtle floating animation for decorative elements */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .floating {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div class="bg-decoration"></div>
    <div class="bg-decoration"></div>

    <div class="container">
        <div class="icon-container floating">
            <div class="lock-circle">
                <div class="lock-icon">
                    <div class="lock-shackle"></div>
                    <div class="lock-body">
                        <div class="keyhole"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="error-code">403</div>
        <div class="divider"></div>
        <h1>Access Forbidden</h1>
        <p>You don't have permission to access this resource.<br>Please contact your administrator if you believe this is an error.</p>
        <a href="#" class="btn" onclick="window.history.back(); return false;">
            Return to Previous Page
        </a>
    </div>
</body>
</html>