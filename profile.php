<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #666;
        }
        
        .breadcrumb a {
            color: #666;
            text-decoration: none;
        }
        
        .breadcrumb span {
            margin: 0 8px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            margin-right: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .profile-info h2 {
            font-size: 18px;
            font-weight: 400;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .details-card {
            background-color: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #2980b9;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 500;
        }
        
        .info-value a {
            color: #3498db;
            text-decoration: none;
        }
        
        .info-value a:hover {
            text-decoration: underline;
        }
        
        .action-button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .action-button:hover {
            background-color: #2980b9;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-image {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="breadcrumb">
            <a href="dashboard.html">Dashboard</a>
            <span>›</span>
            <strong>Student Profile</strong>
        </div>
        <button class="action-button">Edit Profile</button>
    </header>
    
    <div class="container">
        <div class="profile-header">
            <div class="profile-image">
                <img src="/api/placeholder/120/120" alt="Student Photo">
            </div>
            <div class="profile-info">
                <h1>Om Anand Jha</h1>
                <h2>Bachelor of Technology Computer Engineering</h2>
                <h2>Second Year</h2>
            </div>
        </div>
        
        <div class="details-card">
            <h3 class="card-title">Personal Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">Om Anand Jha</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">26-FEB-2006</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Father's Name</div>
                    <div class="info-value">Arbind Jha</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mother's Name</div>
                    <div class="info-value">Ranjana Jha</div>
                </div>
                <div class="info-item">
                    <div class="info-label">SVVN ID</div>
                    <div class="info-value">SVVN2023CS214</div>
                </div>
                <div class="info-item">
                    <div class="info-label">College</div>
                    <div class="info-value">K.J Somaiya College of Engineering</div>
                </div>
            </div>
        </div>
        
        <div class="details-card">
            <h3 class="card-title">Contact Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><a href="mailto:omanand23@somaiya.edu">omanand23@somaiya.edu</a></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">+91 8097795781</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">Vinayak Park-A,Titwala,421605</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Emergency Contact</div>
                    <div class="info-value">9876543211(Ayush Anand)</div>
                </div>
            </div>
        </div>
        
        <div class="details-card">
            <h3 class="card-title">Academic Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Program</div>
                    <div class="info-value">Bachelor of Technology</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Specialization</div>
                    <div class="info-value">Computer Engineering</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Current Year</div>
                    <div class="info-value">Second Year</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Enrollment Year</div>
                    <div class="info-value">2023</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>