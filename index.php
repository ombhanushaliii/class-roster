<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <title>Class Roster</title>
    <style>
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

        .cta-button {
            appearance: button;
            background-color: #000;
            background-image: none;
            border: 1px solid #000;
            border-radius: 4px;
            box-shadow: #fff 4px 4px 0 0,#000 4px 4px 0 1px;
            box-sizing: border-box;
            color: #fff;
            cursor: pointer;
            display: inline-block;
            font-family: ITCAvantGardeStd-Bk,Arial,sans-serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 20px;
            margin: 0 5px 10px 0;
            overflow: visible;
            padding: 12px 40px;
            text-align: center;
            text-transform: none;
            touch-action: manipulation;
            user-select: none;
            -webkit-user-select: none;
            vertical-align: middle;
            white-space: nowrap;
            text-decoration: none;
        }

        .cta-button:focus {
            text-decoration: none;
        }

        .cta-button:hover {
            text-decoration: none;
        }

        .cta-button:active {
            box-shadow: rgba(0, 0, 0, .125) 0 3px 5px inset;
            outline: 0;
        }

        .cta-button:not([disabled]):active {
            box-shadow: #fff 2px 2px 0 0, #000 2px 2px 0 1px;
            transform: translate(2px, 2px);
        }

        @media (min-width: 768px) {
            .cta-button {
                padding: 12px 50px;
            }
        }

        .arrow-icon {
            font-size: 1.2rem;
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
        <p class="subtitle"> Organize your student records, track attendance, and manage grades in one secure place.</p>
        <a href="signup.php" class="cta-button">
            Get Started
            <span class="arrow-icon">→</span>
        </a>
    </main>
</body>
</html>