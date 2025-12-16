<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: login.php");
    exit;
}

// Ambil data dari session
$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role']; // admin, operator, requester

// --- LOGIKA QUERY ---

// 1. Hitung Notifikasi (Untuk Admin/Operator: Request Pending)
$jml_pending = 0;
if ($role_user == 'admin' || $role_user == 'operator') {
    $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE status='pending'");
    $d_pending = mysqli_fetch_assoc($q_pending);
    $jml_pending = $d_pending['total'];
}

// 2. Logika Pencarian Barang
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = $_GET['cari'];
    // Join dengan tabel lokasi_rak untuk menampilkan nama lokasi
    $query_str = "SELECT b.*, l.nama_lokasi 
                  FROM barang b 
                  LEFT JOIN lokasi_rak l ON b.id_lokasi = l.id
                  WHERE b.kode_barang LIKE '%$keyword%' OR b.nama_barang LIKE '%$keyword%' 
                  ORDER BY b.stok ASC";
} else {
    $query_str = "SELECT b.*, l.nama_lokasi 
                  FROM barang b 
                  LEFT JOIN lokasi_rak l ON b.id_lokasi = l.id 
                  ORDER BY b.stok ASC";
}
$query = mysqli_query($koneksi, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard WMS - <?= $role_user ?></title>
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* CSS UTAMA: FIX SIDEBAR FULL HEIGHT */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            height: 100vh; /* Tinggi layar penuh */
            overflow: hidden; /* Mencegah scroll ganda pada body */
        }
        
        .wrapper { 
            display: flex; 
            width: 100%; 
            height: 100%; 
        }
        
        /* Sidebar Styles */
        .sidebar { 
            width: 260px; 
            background: #162433; 
            color: white; 
            padding: 20px; 
            box-sizing: border-box; 
            flex-shrink: 0; 
            display: flex; 
            flex-direction: column; 
            height: 100%; /* Penuh ke bawah */
            overflow-y: auto; /* Scroll sendiri jika menu panjang */
        }

        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        .sidebar .badge { background: #dc3545; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: auto; }

        /* Content Styles */
        .content { 
            flex: 1; 
            padding: 30px; 
            overflow-y: auto; /* Konten scroll sendiri */
            height: 100%; 
        }
        
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }

        /* Table & Search Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background-color: #FF7F27; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .search-box { display: flex; gap: 10px; margin-bottom: 15px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .search-box button { padding: 10px 20px; background: #162433; color: white; border: none; border-radius: 4px; cursor: pointer; display: flex; gap: 5px; align-items: center; }
        .btn-reset { padding: 10px; background: #fee2e2; color: #dc2626; border-radius: 4px; text-decoration: none; display: flex; align-items: center; }

        /* Badges */
        .badge-danger { background: #ffebee; color: #c62828; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-success { background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; }
        .badge-loc { background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>

<body>

    <div class="wrapper">
        <div class="sidebar">
            <h2>WMS SYSTEM</h2>
            <div class="user-info">
                Halo, <b><?= $nama_user ?></b><br>
                Role: <?= strtoupper($role_user) ?>
            </div>

            <a href="index.php" class="active"><i data-lucide="layout-dashboard"></i> Dashboard</a>

            <?php if($role_user == 'admin' || $role_user == 'operator'): ?>
                <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Transaksi</div>
                
                <a href="barang_masuk.php"><i data-lucide="arrow-down-circle"></i> Barang Masuk</a>
                
                <a href="request_masuk.php">
                    <i data-lucide="inbox"></i> Permintaan Barang
                    <?php if($jml_pending > 0): ?>
                        <span class="badge"><?= $jml_pending ?></span>
                    <?php endif; ?>
                </a>

                <a href="barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
                <a href="stock_opname.php"><i data-lucide="clipboard-check"></i> Stock Opname</a>
            <?php endif; ?>

            <?php if($role_user == 'admin'): ?>
                <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
                <a href="master_barang.php"><i data-lucide="package"></i> Data Barang</a>
                <a href="master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
                <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
                <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
                <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
                <a href="laporan.php"><i data-lucide="file-text"></i> Laporan</a>
            <?php endif; ?>

            <?php if($role_user == 'requester'): ?>
                <a href="buat_request.php"><i data-lucide="shopping-cart"></i> Request Barang</a>
                <a href="riwayat_request.php"><i data-lucide="history"></i> Status Request Saya</a>
            <?php endif; ?>

            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Dashboard Stok</h1>
            
            <?php if($role_user == 'requester'): ?>
                <p>Selamat datang. Silakan cek stok di bawah ini sebelum mengajukan permintaan barang.</p>
            <?php else: ?>
                <p>Pantau stok gudang dan notifikasi permintaan barang.</p>
                
                <?php if($jml_pending > 0): ?>
                <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffeeba;">
                    <i data-lucide="alert-circle" style="vertical-align: middle;"></i> 
                    <b>Perhatian:</b> Ada <?= $jml_pending ?> permintaan barang baru yang menunggu persetujuan (Approval). 
                    <a href="request_masuk.php" style="color: #856404; font-weight: bold;">Cek Sekarang</a>
                </div>
                <?php endif; ?>

            <?php endif; ?>

            <div class="card">
                <h3>Katalog Stok Gudang</h3>
                
                <form action="" method="GET" class="search-box">
                    <input type="text" name="cari" placeholder="Cari Kode atau Nama Barang..." value="<?= htmlspecialchars($keyword) ?>" autocomplete="off">
                    <button type="submit"><i data-lucide="search" width="16"></i> Cari</button>
                    <?php if($keyword): ?>
                        <a href="index.php" class="btn-reset" title="Reset"><i data-lucide="x" width="16"></i></a>
                    <?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Lokasi Rak</th> <th>Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) { 
                                $is_critical = ($row['stok'] <= $row['stok_minimal']);
                                
                                // Label Status Stok
                                if ($is_critical) {
                                    $status_label = "<span class='badge-danger'>Segera Restock!</span>";
                                    $row_style = "style='background-color: #fff5f5;'"; 
                                } else {
                                    $status_label = "<span class='badge-success'>Aman</span>";
                                    $row_style = ""; 
                                }

                                // Label Lokasi
                                $lokasi = $row['nama_lokasi'] ? $row['nama_lokasi'] : "-";
                        ?>
                        <tr <?= $row_style ?>>
                            <td><?= $row['kode_barang']; ?></td>
                            <td><?= $row['nama_barang']; ?></td>
                            <td><?= $row['kategori']; ?></td>
                            <td><span class="badge-loc"><i data-lucide="map-pin" width="10"></i> <?= $lokasi ?></span></td>
                            <td><b><?= $row['stok']; ?></b> <?= $row['satuan']; ?></td>
                            <td><?= $status_label; ?></td>
                        </tr>
                        <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 20px;'>Data barang tidak ditemukan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script> lucide.createIcons(); </script>
</body>
</html>