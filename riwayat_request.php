<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Hanya Requester
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'requester') {
    header("Location: index.php"); exit;
}
$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
$id_user = $_SESSION['id_user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Request - WMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS FIX FULL HEIGHT */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            height: 100vh; 
            overflow: hidden; /* Mencegah scroll ganda */
        }
        
        .wrapper { display: flex; width: 100%; height: 100%; }
        
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
            height: 100%; 
            overflow-y: auto; /* Scroll sendiri */
        }
        
        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        
        /* Content Styles */
        .content { 
            flex: 1; 
            padding: 30px; 
            overflow-y: auto; /* Scroll sendiri */
            height: 100%; 
        }
        
        .card { background: white; padding: 25px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #FF7F27; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        /* Badges */
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase;}
        .pending { background: #fff3cd; color: #856404; }
        .approved { background: #d4edda; color: #155724; }
        .rejected { background: #f8d7da; color: #721c24; }
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
            
            <a href="index.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            <a href="buat_request.php"><i data-lucide="shopping-cart"></i> Request Barang</a>
            
            <a href="riwayat_request.php" class="active"><i data-lucide="history"></i> Status Request Saya</a>
            
            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Riwayat Permintaan Saya</h1>
            
            <div class="card">
                <h3>Daftar Transaksi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Keterangan Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data milik user ini saja
                        $q = mysqli_query($koneksi, "
                            SELECT t.*, b.nama_barang, b.satuan, d.nama_divisi
                            FROM transaksi t
                            JOIN barang b ON t.id_barang = b.id
                            LEFT JOIN divisi d ON t.id_divisi = d.id
                            WHERE t.id_user = '$id_user' AND t.jenis = 'keluar'
                            ORDER BY t.tanggal DESC
                        ");

                        if(mysqli_num_rows($q) > 0){
                            while($row = mysqli_fetch_assoc($q)){
                                // Badge Warna
                                if($row['status'] == 'pending') $stat = "<span class='badge pending'>Menunggu</span>";
                                elseif($row['status'] == 'approved') $stat = "<span class='badge approved'>Disetujui</span>";
                                else $stat = "<span class='badge rejected'>Ditolak</span>";
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['nama_barang'] ?></td>
                            <td><b><?= $row['jumlah'] ?></b> <?= $row['satuan'] ?></td>
                            <td><?= $row['nama_divisi'] ?></td>
                            <td><?= $stat ?></td>
                            <td>
                                <i>"<?= $row['keterangan'] ?>"</i>
                                <?php if($row['status']=='approved'): ?>
                                    <br><small style="color:green; font-weight:bold;">Silakan ambil barang di gudang.</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#888;'>Belum ada riwayat permintaan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>