<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit();
}

// Initialize variables
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add_job'])) {
            $nama_perusahaan = trim($_POST['nama_perusahaan']);
            $posisi = trim($_POST['posisi']);
            $lokasi = trim($_POST['lokasi']);
            $deskripsi = trim($_POST['deskripsi']);
            $kualifikasi = trim($_POST['kualifikasi']);
            $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
            
            // Validation
            if (empty($nama_perusahaan) || empty($posisi) || empty($lokasi) || empty($deskripsi) || empty($kualifikasi) || empty($tanggal_kadaluarsa)) {
                $error = "Semua field harus diisi!";
            } else {
                $stmt = $pdo->prepare("INSERT INTO lowongan (nama_perusahaan, posisi, lokasi, deskripsi, kualifikasi, tanggal_kadaluarsa, id_admin, tanggal_posting) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$nama_perusahaan, $posisi, $lokasi, $deskripsi, $kualifikasi, $tanggal_kadaluarsa, $_SESSION['admin_id']]);
                
                $success = "Lowongan berhasil ditambahkan!";
            }
        }
        
        if (isset($_POST['delete_job'])) {
            $id_lowongan = $_POST['id_lowongan'];
            $stmt = $pdo->prepare("DELETE FROM lowongan WHERE id_lowongan = ?");
            $stmt->execute([$id_lowongan]);
            
            $success = "Lowongan berhasil dihapus!";
        }
        
        if (isset($_POST['update_job'])) {
            $id_lowongan = $_POST['id_lowongan'];
            $nama_perusahaan = trim($_POST['nama_perusahaan']);
            $posisi = trim($_POST['posisi']);
            $lokasi = trim($_POST['lokasi']);
            $deskripsi = trim($_POST['deskripsi']);
            $kualifikasi = trim($_POST['kualifikasi']);
            $tanggal_kadaluarsa = $_POST['tanggal_kadaluarsa'];
            
            // Validation
            if (empty($nama_perusahaan) || empty($posisi) || empty($lokasi) || empty($deskripsi) || empty($kualifikasi) || empty($tanggal_kadaluarsa)) {
                $error = "Semua field harus diisi!";
            } else {
                $stmt = $pdo->prepare("UPDATE lowongan SET nama_perusahaan = ?, posisi = ?, lokasi = ?, deskripsi = ?, kualifikasi = ?, tanggal_kadaluarsa = ? WHERE id_lowongan = ?");
                $stmt->execute([$nama_perusahaan, $posisi, $lokasi, $deskripsi, $kualifikasi, $tanggal_kadaluarsa, $id_lowongan]);
                
                $success = "Lowongan berhasil diperbarui!";
            }
        }
    } catch (PDOException $e) {
        $error = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Get all job postings
try {
    $stmt = $pdo->prepare("SELECT * FROM lowongan ORDER BY tanggal_posting DESC");
    $stmt->execute();
    $lowongan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get statistics
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_jobs FROM lowongan");
    $stmt->execute();
    $total_jobs = $stmt->fetch(PDO::FETCH_ASSOC)['total_jobs'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
    $stmt->execute();
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as active_jobs FROM lowongan WHERE tanggal_kadaluarsa >= CURDATE()");
    $stmt->execute();
    $active_jobs = $stmt->fetch(PDO::FETCH_ASSOC)['active_jobs'];
} catch (PDOException $e) {
    $error = "Terjadi kesalahan saat mengambil data: " . $e->getMessage();
    $lowongan = [];
    $total_jobs = 0;
    $total_users = 0;
    $active_jobs = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Portal Lowongan Kerja</title>
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
        .admin-info {
            float: right;
            color: #fff;
            padding: 12px 16px;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            flex: 1;
            margin: 0 10px;
        }
        .stat-card h3 {
            color: #1e90ff;
            margin-top: 0;
        }
        .stat-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
        }
        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .form-section h2 {
            color: #1e90ff;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-primary {
            background-color: #1e90ff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0d75d8;
        }
        .btn-danger {
            background-color: #ff6b6b;
            color: white;
        }
        .btn-danger:hover {
            background-color: #ff5252;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        .job-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            overflow-x: auto;
        }
        .job-table th,
        .job-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .job-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .job-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .table-container {
            overflow-x: auto;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <header>
        <h1>Admin Dashboard - Portal Lowongan Kerja</h1>
    </header>

    <nav>
        <a href="#dashboard">Dashboard</a>
        <a href="#add-job">Tambah Lowongan</a>
        <a href="#job-list">Daftar Lowongan</a>
        <div class="admin-info">
            Selamat datang, Admin
            <a href="index.html" class="logout-btn">Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Statistics Section -->
        <div class="stats">
            <div class="stat-card">
                <h3>Total Lowongan</h3>
                <div class="number"><?php echo $total_jobs; ?></div>
            </div>
            <div class="stat-card">
                <h3>Lowongan Aktif</h3>
                <div class="number"><?php echo $active_jobs; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $total_users; ?></div>
            </div>
        </div>

        <!-- Add Job Form -->
        <div class="form-section" id="add-job">
            <h2>Tambah Lowongan Kerja Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nama_perusahaan">Nama Perusahaan:</label>
                    <input type="text" id="nama_perusahaan" name="nama_perusahaan" required>
                </div>
                <div class="form-group">
                    <label for="posisi">Posisi yang Dibutuhkan:</label>
                    <input type="text" id="posisi" name="posisi" required>
                </div>
                <div class="form-group">
                    <label for="lokasi">Lokasi:</label>
                    <input type="text" id="lokasi" name="lokasi" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Pekerjaan:</label>
                    <textarea id="deskripsi" name="deskripsi" required></textarea>
                </div>
                <div class="form-group">
                    <label for="kualifikasi">Kualifikasi:</label>
                    <textarea id="kualifikasi" name="kualifikasi" required></textarea>
                </div>
                <div class="form-group">
                    <label for="tanggal_kadaluarsa">Tanggal Kadaluarsa:</label>
                    <input type="date" id="tanggal_kadaluarsa" name="tanggal_kadaluarsa" required>
                </div>
                <button type="submit" name="add_job" class="btn btn-primary">Tambah Lowongan</button>
            </form>
        </div>

        <!-- Job List Section -->
        <div class="form-section" id="job-list">
            <h2>Daftar Lowongan Kerja</h2>
            <?php if (count($lowongan) > 0): ?>
                <div class="table-container">
                    <table class="job-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Perusahaan</th>
                                <th>Posisi</th>
                                <th>Lokasi</th>
                                <th>Tanggal Posting</th>
                                <th>Tanggal Kadaluarsa</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowongan as $index => $job): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($job['nama_perusahaan']); ?></td>
                                    <td><?php echo htmlspecialchars($job['posisi']); ?></td>
                                    <td><?php echo htmlspecialchars($job['lokasi']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($job['tanggal_posting'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($job['tanggal_kadaluarsa'])); ?></td>
                                    <td>
                                        <?php if ($job['tanggal_kadaluarsa'] >= date('Y-m-d')): ?>
                                            <span style="color: green;">Aktif</span>
                                        <?php else: ?>
                                            <span style="color: red;">Kadaluarsa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-warning" onclick="editJob(<?php echo $job['id_lowongan']; ?>, '<?php echo htmlspecialchars($job['nama_perusahaan'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($job['posisi'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($job['lokasi'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($job['deskripsi'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($job['kualifikasi'], ENT_QUOTES); ?>', '<?php echo $job['tanggal_kadaluarsa']; ?>')">Edit</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id_lowongan" value="<?php echo $job['id_lowongan']; ?>">
                                            <button type="submit" name="delete_job" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus lowongan ini?')">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>Belum ada lowongan yang ditambahkan.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit Lowongan Kerja</h2>
            <form method="POST" id="editForm">
                <input type="hidden" id="edit_id_lowongan" name="id_lowongan">
                <div class="form-group">
                    <label for="edit_nama_perusahaan">Nama Perusahaan:</label>
                    <input type="text" id="edit_nama_perusahaan" name="nama_perusahaan" required>
                </div>
                <div class="form-group">
                    <label for="edit_posisi">Posisi yang Dibutuhkan:</label>
                    <input type="text" id="edit_posisi" name="posisi" required>
                </div>
                <div class="form-group">
                    <label for="edit_lokasi">Lokasi:</label>
                    <input type="text" id="edit_lokasi" name="lokasi" required>
                </div>
                <div class="form-group">
                    <label for="edit_deskripsi">Deskripsi Pekerjaan:</label>
                    <textarea id="edit_deskripsi" name="deskripsi" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_kualifikasi">Kualifikasi:</label>
                    <textarea id="edit_kualifikasi" name="kualifikasi" required></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_tanggal_kadaluarsa">Tanggal Kadaluarsa:</label>
                    <input type="date" id="edit_tanggal_kadaluarsa" name="tanggal_kadaluarsa" required>
                </div>
                <button type="submit" name="update_job" class="btn btn-primary">Update Lowongan</button>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Portal Lowongan Kerja. All rights reserved.</p>
    </footer>

    <script>
        // Modal functionality
        var modal = document.getElementById("editModal");
        var span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Edit job function
        function editJob(id, nama_perusahaan, posisi, lokasi, deskripsi, kualifikasi, tanggal_kadaluarsa) {
            document.getElementById('edit_id_lowongan').value = id;
            document.getElementById('edit_nama_perusahaan').value = nama_perusahaan;
            document.getElementById('edit_posisi').value = posisi;
            document.getElementById('edit_lokasi').value = lokasi;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_kualifikasi').value = kualifikasi;
            document.getElementById('edit_tanggal_kadaluarsa').value = tanggal_kadaluarsa;
            modal.style.display = "block";
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('nav a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function(message) {
                message.style.display = 'none';
            });
        }, 5000);
    </script>

</body>
</html>