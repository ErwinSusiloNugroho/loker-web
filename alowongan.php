<?php
// Check if this is an API request
if (isset($_GET['api']) && $_GET['api'] === 'lowongan') {
    // API endpoint for job listings
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Access-Control-Allow-Headers: Content-Type');
    
    // Database configuration
    $host = 'localhost';
    $dbname = 'lowongan_kerja';
    $username = 'root';
    $password = '';
    
    try {
        // Database connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Query to get job listings (only company name and id)
        // Ordered by latest posting date
        $query = "SELECT id_lowongan, nama_perusahaan FROM lowongan ORDER BY tanggal_posting DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        
        $lowongan = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Return data in JSON format
        echo json_encode($lowongan);
        
    } catch (PDOException $e) {
        // If error occurs, send error response
        http_response_code(500);
        echo json_encode([
            'error' => 'Database connection failed',
            'message' => $e->getMessage()
        ]);
    }
    
    // Stop execution after API response
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lowongan Kerja - Portal Lowongan Kerja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        
        header {
            background-color: #1e90ff;
            padding: 15px 0;
            color: white;
            text-align: center;
        }
        
        nav {
            background-color: #333;
            overflow: hidden;
        }
        
        nav a {
            float: left;
            display: block;
            color: #fff;
            text-align: center;
            padding: 12px 16px;
            text-decoration: none;
        }
        
        nav a:hover {
            background-color: #575757;
        }
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        h2 {
            color: #1e90ff;
            margin-bottom: 20px;
        }
        
        .job-listing {
            margin-top: 20px;
        }
        
        .job-card {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            transition: background-color 0.3s;
        }
        
        .job-card:hover {
            background-color: #e8f4f8;
        }
        
        .job-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.2em;
        }
        
        .job-card button {
            background-color: #1e90ff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .job-card button:hover {
            background-color: #1c7ed6;
        }
        
        .no-jobs {
            text-align: center;
            color: #666;
            padding: 40px 20px;
            font-style: italic;
        }
        
        .loading {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 3px;
            margin: 15px 0;
            border: 1px solid #e8c5c5;
        }
        
        footer {
            background-color: #1e90ff;
            color: white;
            text-align: center;
            padding: 10px 0;
            margin-top: 40px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 20px 10px;
                padding: 15px;
            }
            
            nav a {
                float: none;
                display: block;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Portal Lowongan Kerja</h1>
    </header>

    <nav>
        <a href="index.html">Home</a>
        <a href="contact.html">Contact</a>
        <a href="alowongan.php">Lowongan</a>
        <a href="profil.html">Profil</a>
        <a href="login.html">Register & Login</a>
        <a href="info.html">Informasi Umum</a>
    </nav>

    <div class="container">
        <h2>Daftar Lowongan Kerja</h2>
        <div class="job-listing" id="jobListing">
            <div class="loading">
                <p>Memuat data lowongan...</p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Portal Lowongan Kerja Indonesia. All Rights Reserved.</p>
    </footer>

    <script>
        // Function to load job listings from database
        async function loadJobListings() {
            try {
                const response = await fetch('?api=lowongan');
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.message || 'Database error');
                }
                
                displayJobListings(data);
            } catch (error) {
                console.error('Error loading job listings:', error);
                showError('Gagal memuat data lowongan. Menampilkan data contoh.');
                // If failed to load from database, show sample data
                displayJobListings(sampleData);
            }
        }

        // Function to display job listings
        function displayJobListings(jobs) {
            const jobListingContainer = document.getElementById('jobListing');
            
            if (!jobs || jobs.length === 0) {
                jobListingContainer.innerHTML = `
                    <div class="no-jobs">
                        <p>Belum ada lowongan tersedia saat ini.</p>
                        <p>Silakan kembali lagi nanti untuk melihat lowongan terbaru.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            jobs.forEach(job => {
                html += `
                    <div class="job-card">
                        <h3>${escapeHtml(job.nama_perusahaan)}</h3>
                        <button onclick="viewJobDetail(${job.id_lowongan})">
                            Lihat Detail
                        </button>
                    </div>
                `;
            });

            jobListingContainer.innerHTML = html;
        }

        // Function to show error message
        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            
            const container = document.querySelector('.container');
            container.insertBefore(errorDiv, document.getElementById('jobListing'));
            
            // Remove error message after 5 seconds
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        // Function to escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Function to view job detail
                function viewJobDetail(idLowongan) {
                if (idLowongan) {
                    // Redirect to login page instead of detail page
                    window.location.href = 'login.html';
                } else {
                    alert('ID lowongan tidak valid');
                }
            }

        // Sample data for testing (will be replaced with database data)
        const sampleData = [
            {
                id_lowongan: 1,
                nama_perusahaan: "PT Sukses Makmur Indonesia"
            },
            {
                id_lowongan: 2,
                nama_perusahaan: "PT Jaya Teknologi"
            },
            {
                id_lowongan: 3,
                nama_perusahaan: "CV Maju Bersama"
            }
        ];

        // Run load data function when page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            loadJobListings();
        });
    </script>
</body>
</html>