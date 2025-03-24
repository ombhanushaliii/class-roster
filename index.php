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

        .download-btn {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            transition: background-color 0.3s;
            font-size: 12px;
        }

        .download-btn:hover {
            background: rgba(255, 255, 255, 0.2);
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
            <a href="/login.html" class="download-btn">Login</a>
        </div>
    </nav>

    <main class="hero">
        <h1>Class Roster</h1>
        <p class="subtitle"> Organize your student records, track attendance, and manage grades in one secure place.</p>
        <a href="/login.php" class="cta-button">
            Get Started
            <span class="arrow-icon">→</span>
        </a>
    </main>
</body>
</html>


