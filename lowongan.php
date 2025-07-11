<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit();
}

// Get all active job postings
$stmt = $pdo->prepare("SELECT * FROM lowongan WHERE tanggal_kadaluarsa >= CURDATE() ORDER BY tanggal_posting DESC");
$stmt->execute();
$lowongan = $stmt->fetchAll();
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
            padding-bottom: 70px;
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
        .user-info {
            float: right;
            color: #fff;
            padding: 12px 16px;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }
        .welcome {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .job-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .job-card h3 {
            color: #1e90ff;
            margin-top: 0;
        }
        .job-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .job-info span {
            color: #666;
        }
        .job-description {
            margin: 15px 0;
            line-height: 1.6;
        }
        .job-requirements {
            margin: 15px 0;
            line-height: 1.6;
        }
        .deadline {
            color: #ff6b6b;
            font-weight: bold;
        }
        .no-jobs {
            text-align: center;
            color: #666;
            padding: 40px;
        }
        footer {
            background-color: #1e90ff;
            color: white;
            text-align: center;
            padding: 15px 0;
            font-size: 14px;
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            z-index: 100;
        }
        .logout-btn {
            background-color: #ff6b6b;
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 10px;
        }
        .logout-btn:hover {
            background-color: #ff5252;
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
        <a href="lowongan.php">Lowongan</a>
        <a href="profil.html">Profil</a>
        <div class="user-info">
            Selamat datang, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
            <a href="index.html" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome">
            <h2>Selamat Datang di Portal Lowongan Kerja</h2>
            <p>Halo <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>, berikut adalah daftar lowongan kerja yang tersedia:</p>
        </div>

        <?php if (empty($lowongan)): ?>
            <div class="no-jobs">
                <h3>Belum ada lowongan kerja yang tersedia</h3>
                <p>Silakan kembali lagi nanti untuk melihat lowongan terbaru.</p>
            </div>
        <?php else: ?>
            <?php foreach ($lowongan as $job): ?>
                <div class="job-card">
                    <h3><?php echo htmlspecialchars($job['posisi']); ?></h3>
                    <div class="job-info">
                        <span><strong>Perusahaan:</strong> <?php echo htmlspecialchars($job['nama_perusahaan']); ?></span>
                        <span><strong>Lokasi:</strong> <?php echo htmlspecialchars($job['lokasi']); ?></span>
                    </div>
                    <div class="job-info">
                        <span><strong>Tanggal Posting:</strong> <?php echo date('d F Y', strtotime($job['tanggal_posting'])); ?></span>
                        <span class="deadline"><strong>Batas Waktu:</strong> <?php echo date('d F Y', strtotime($job['tanggal_kadaluarsa'])); ?></span>
                    </div>
                    <div class="job-description">
                        <strong>Deskripsi:</strong><br>
                        <?php echo nl2br(htmlspecialchars($job['deskripsi'])); ?>
                    </div>
                    <div class="job-requirements">
                        <strong>Kualifikasi:</strong><br>
                        <?php echo nl2br(htmlspecialchars($job['kualifikasi'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 Portal Lowongan Kerja Indonesia. All Rights Reserved.</p>
    </footer>

</body>
</html>