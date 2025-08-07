<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oops! Access Denied – Support Portal</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700" rel="stylesheet" />
    
    <style>
        /* CSS Custom Properties for Theme Colors - matching your project */
        :root {
            --color-primary-50: rgb(248 250 252);
            --color-primary-100: rgb(241 245 249);
            --color-primary-200: rgb(226 232 240);
            --color-primary-300: rgb(203 213 225);
            --color-primary-400: rgb(148 163 184);
            --color-primary-500: rgb(100 116 139);
            --color-primary-600: rgb(71 85 105);
            --color-primary-700: rgb(51 65 85);
            --color-primary-800: rgb(30 41 59);
            --color-primary-900: rgb(15 23 42);

            --color-accent-400: rgb(56 189 248);
            --color-accent-500: rgb(14 165 233);
            --color-accent-600: rgb(2 132 199);
            --color-accent-700: rgb(3 105 161);

            --color-danger-500: rgb(239 68 68);
            --color-danger-600: rgb(220 38 38);
        }

        .dark {
            --color-bg-primary: rgb(15 23 42);
            --color-bg-secondary: rgb(30 41 59);
            --color-text-primary: rgb(248 250 252);
            --color-text-secondary: rgb(203 213 225);
        }

        body {
            font-family: 'Figtree', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            margin: 0;
            padding: 0;
            background: var(--color-primary-50);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-primary-900);
            transition: background-color 0.3s, color 0.3s;
        }
        
        /* Glass morphism container matching your project style */
        .container {
            text-align: center;
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            border: 1px solid var(--color-primary-200);
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            margin: 2rem;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--color-danger-500);
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin: 1rem 0;
            color: var(--color-primary-800);
        }
        
        .error-message {
            font-size: 1.1rem;
            margin: 2rem 0;
            line-height: 1.6;
            color: var(--color-primary-600);
        }
        
        .quirky-text {
            font-style: italic;
            color: var(--color-accent-700);
            margin: 1.5rem 0;
            font-weight: 500;
        }
        
        /* Button matching your btn-primary style */
        .back-button {
            background: var(--color-accent-600);
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .back-button:hover {
            background: var(--color-accent-700);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }
        
        .back-button:focus {
            outline: none;
            ring: 2px solid var(--color-accent-500);
            ring-offset: 2px;
        }
        
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }
        
        .bounce {
            animation: bounce 2s infinite;
        }

        /* Dark mode support matching your project */
        @media (prefers-color-scheme: dark) {
            body {
                background: var(--color-primary-900);
                color: var(--color-primary-50);
            }
            
            .container {
                background: rgba(30, 41, 59, 0.4);
                border-color: var(--color-primary-700);
            }
            
            .error-title {
                color: var(--color-primary-100);
            }
            
            .error-message {
                color: var(--color-primary-300);
            }
            
            .quirky-text {
                color: var(--color-accent-400);
            }
        }

        /* Responsive design */
        @media (max-width: 640px) {
            .container {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-message {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon bounce">🚫</div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Denied</h2>
        
        <div class="error-message">
            Whoops! It looks like you're trying to access something that's off-limits.
        </div>
        
        <div class="quirky-text">
            🕵️ You don't have permission for this specific action. 
            <br>
            Maybe you need to level up your access privileges first!
        </div>
        
        <button class="back-button" onclick="goBack()">
            ← Take Me Back
        </button>
    </div>
    
    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                // Fallback to home page if no history
                window.location.href = '/';
            }
        }
    </script>
</body>
</html>