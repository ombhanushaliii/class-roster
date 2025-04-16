<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <title>Class Roster</title>
    <style>
        :root {
            --radial-gradient-background: 250, 250, 250;
            --solid-color-background: 15, 15, 15;
            --overlay-color: 255, 255, 255;
            --x: 100%;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins;
        }

        body {
            background-color: #1a1a1a;
            color: white;
            min-height: 100vh;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Removed .download-btn styles */

        .button-30 {
            align-items: center;
            appearance: none;
            background-color: #FCFCFD;
            border-radius: 4px;
            border-width: 0;
            box-shadow: rgba(45, 35, 66, 0.4) 0 2px 4px,rgba(45, 35, 66, 0.3) 0 7px 13px -3px,#D6D6E7 0 -3px 0 inset;
            box-sizing: border-box;
            color: #36395A;
            cursor: pointer;
            display: inline-flex;
            font-family: "JetBrains Mono",monospace;
            height: 36px; /* Adjusted from 48px to fit nav better */
            justify-content: center;
            line-height: 1;
            list-style: none;
            overflow: hidden;
            padding-left: 16px;
            padding-right: 16px;
            position: relative;
            text-align: left;
            text-decoration: none;
            transition: box-shadow .15s,transform .15s;
            user-select: none;
            -webkit-user-select: none;
            touch-action: manipulation;
            white-space: nowrap;
            will-change: box-shadow,transform;
            font-size: 14px; /* Reduced from 18px to better fit navbar */
        }

        .button-30:focus {
            box-shadow: #D6D6E7 0 0 0 1.5px inset, rgba(45, 35, 66, 0.4) 0 2px 4px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #D6D6E7 0 -3px 0 inset;
        }

        .button-30:hover {
            box-shadow: rgba(45, 35, 66, 0.4) 0 4px 8px, rgba(45, 35, 66, 0.3) 0 7px 13px -3px, #D6D6E7 0 -3px 0 inset;
            transform: translateY(-2px);
        }

        .button-30:active {
            box-shadow: #D6D6E7 0 3px 7px inset;
            transform: translateY(2px);
        }

        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            min-height: calc(100vh - 80px);
            padding: 2rem;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/api/placeholder/1920/1080') center/cover;
        }

        .hero h1 {
            font-size: 4rem;
            margin-bottom: 0.5rem;
        }

        .version {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            opacity: 0.8;
        }

        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            max-width: 600px;
            opacity: 0.9;
        }

        /* New Shiny Button Styles */
        .shiny-button {
            position: relative;
            padding: 12px 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
            transform: scale(1);
            text-decoration: none;
            display: inline-block;
        }
        
        .radial-gradient {
            background: radial-gradient(
                circle at 50% 0%,
                rgba(var(--radial-gradient-background), 0.05) 0%,
                transparent 60%
            ) rgba(var(--solid-color-background), 1);
        }
        
        .button-text {
            position: relative;
            display: block;
            color: rgb(245, 245, 245);
            letter-spacing: 1px;
            font-weight: 300;
            z-index: 2;
        }
        
        .linear-mask {
            mask-image: linear-gradient(
                -75deg,
                white calc(var(--x) + 20%),
                transparent calc(var(--x) + 30%),
                white calc(var(--x) + 100%)
            );
            -webkit-mask-image: linear-gradient(
                -75deg,
                white calc(var(--x) + 20%),
                transparent calc(var(--x) + 30%),
                white calc(var(--x) + 100%)
            );
        }
        
        .linear-overlay {
            position: absolute;
            inset: 0;
            padding: 1px;
            border-radius: 6px;
            background-image: linear-gradient(
                -75deg,
                rgba(var(--overlay-color), 0.1) calc(var(--x) + 20%),
                rgba(var(--overlay-color), 0.5) calc(var(--x) + 25%),
                rgba(var(--overlay-color), 0.1) calc(var(--x) + 100%)
            );
            mask:
                linear-gradient(black, black) content-box,
                linear-gradient(black, black);
            -webkit-mask:
                linear-gradient(black, black) content-box,
                linear-gradient(black, black);
            mask-composite: exclude;
            -webkit-mask-composite: xor;
        }
        
        .shiny-button:active {
            transform: scale(0.97);
            transition: transform 0.2s cubic-bezier(0.4, 2, 0.7, 0.8);
        }

        @media (min-width: 768px) {
            .shiny-button {
                padding: 12px 50px;
            }
        }

        .arrow-icon {
            font-size: 1.2rem;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">Class Roster</div>
        <div class="nav-right">
            <a href="signup.php" class="button-30" role="button">Login</a>
        </div>
    </nav>

    <main class="hero">
        <h1>Class Roster</h1>
        <p class="subtitle">Organize your student records, track attendance, and manage grades in one secure place.</p>
        <a id="shiny-button" href="signup.php" class="shiny-button radial-gradient">
            <span class="button-text linear-mask">Get Started <span class="arrow-icon">→</span></span>
            <span id="overlay" class="linear-overlay"></span>
        </a>
    </main>

    <script>
        const button = document.getElementById('shiny-button');
        const root = document.documentElement;
        
        // Function to animate the shine effect
        function animateShine() {
            // Set initial position
            root.style.setProperty('--x', '100%');
            
            // Animate from 100% to -100%
            const duration = 1500;
            const startTime = performance.now();
            
            function animate(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Calculate current position (100% to -100%)
                const position = 100 - progress * 200;
                root.style.setProperty('--x', `${position}%`);
                
                if (progress < 1) {
                    requestAnimationFrame(animate);
                } else {
                    // Delay before next animation
                    setTimeout(animateShine, 1000);
                }
            }
            
            requestAnimationFrame(animate);
        }
        
        // Start the animation
        setTimeout(animateShine, 500);
        
        // Add spring-like animation for tap
        button.addEventListener('mousedown', () => {
            button.style.transform = 'scale(0.97)';
            button.style.transition = 'transform 0.15s cubic-bezier(0.2, 2, 0.4, 1)';
        });
        
        button.addEventListener('mouseup', () => {
            button.style.transform = 'scale(1)';
            button.style.transition = 'transform 0.3s cubic-bezier(0.2, 0.8, 0.4, 1)';
        });
    </script>
</body>
</html>