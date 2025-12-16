<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Hanya Requester
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'requester') {
    header("Location: index.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
$id_user = $_SESSION['id_user'];
$sukses = "";
$error = "";

// --- LOGIKA SIMPAN REQUEST ---
if (isset($_POST['kirim_request'])) {
    $id_barang = $_POST['id_barang'];
    $id_divisi = $_POST['id_divisi'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    $tanggal = date('Y-m-d H:i:s');

    // Cek stok (Visual only)
    $cek_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM barang WHERE id='$id_barang'"));
    
    if ($jumlah <= 0) {
        $error = "Jumlah permintaan harus lebih dari 0.";
    } else if ($cek_stok['stok'] < $jumlah) {
        $error = "Stok di sistem tidak cukup (Tersedia: {$cek_stok['stok']}). Hubungi Admin.";
    } else {
        $q = "INSERT INTO transaksi (id_barang, id_user, id_divisi, jenis, jumlah, tanggal, keterangan, status) 
              VALUES ('$id_barang', '$id_user', '$id_divisi', 'keluar', '$jumlah', '$tanggal', '$keterangan', 'pending')";
        
        if (mysqli_query($koneksi, $q)) {
            header("Location: riwayat_request.php");
            exit;
        } else {
            $error = "Gagal kirim: " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Request - WMS</title>
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
            overflow: hidden; 
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
            overflow-y: auto; 
        }
        
        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        
        /* Content Styles */
        .content { 
            flex: 1; 
            padding: 30px; 
            overflow-y: auto; 
            height: 100%; 
        }
        
        .card { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }

        /* Form Styles */
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        button { padding: 12px; background: #162433; color: white; border: none; cursor: pointer; width: 100%; border-radius: 4px; font-weight: bold; font-size: 14px; transition: 0.3s; }
        button:hover { background: #2a3c50; }

        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
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
            
            <a href="buat_request.php" class="active"><i data-lucide="shopping-cart"></i> Request Barang</a>
            <a href="riwayat_request.php"><i data-lucide="history"></i> Status Request Saya</a>
            
            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1 style="text-align:center; margin-bottom: 30px;">Form Pengajuan Barang</h1>

            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Barang yang dibutuhkan</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while($r = mysqli_fetch_assoc($q)){
                                echo "<option value='{$r['id']}'>{$r['nama_barang']} (Tersedia: {$r['stok']} {$r['satuan']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Divisi Pemohon</label>
                        <select name="id_divisi" required>
                            <option value="">-- Pilih Divisi Anda --</option>
                            <?php
                            $qd = mysqli_query($koneksi, "SELECT * FROM divisi ORDER BY nama_divisi ASC");
                            while($d = mysqli_fetch_assoc($qd)){
                                echo "<option value='{$d['id']}'>{$d['nama_divisi']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Permintaan</label>
                        <input type="number" name="jumlah" min="1" required placeholder="Masukkan jumlah...">
                    </div>

                    <div class="form-group">
                        <label>Keterangan / Keperluan</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Untuk kebutuhan produksi mesin B..."></textarea>
                    </div>

                    <button type="submit" name="kirim_request">Kirim Permintaan</button>
                </form>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>