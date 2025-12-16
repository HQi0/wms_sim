<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Hanya Admin & Operator
if (!isset($_SESSION['status']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator')) {
    header("Location: index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$id_user   = $_SESSION['id_user'];
$sukses    = "";
$error     = "";

// --- LOGIKA SIMPAN BARANG KELUAR ---
if (isset($_POST['simpan_keluar'])) {
    $id_barang = $_POST['id_barang'];
    $id_divisi = $_POST['id_divisi']; 
    $jumlah    = (int) $_POST['jumlah'];
    $tanggal   = $_POST['tanggal'];
    $keterangan= $_POST['keterangan'];

    if ($jumlah <= 0) {
        $error = "Jumlah barang harus lebih dari 0!";
    } else {
        // 1. Cek Stok Cukup?
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id='$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);
        $stok_tersedia = $data_stok['stok'];

        if ($jumlah > $stok_tersedia) {
            $error = "Gagal: Stok tidak cukup! Tersedia hanya $stok_tersedia unit.";
        } else {
            // 2. Kurangi Stok
            $update_barang = mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id='$id_barang'");

            // 3. Catat Transaksi (Jenis: keluar, Status: completed)
            $insert_transaksi = mysqli_query($koneksi, 
                "INSERT INTO transaksi (id_barang, id_user, id_divisi, jenis, jumlah, tanggal, keterangan, status) 
                 VALUES ('$id_barang', '$id_user', '$id_divisi', 'keluar', '$jumlah', '$tanggal', '$keterangan', 'completed')"
            );

            if ($update_barang && $insert_transaksi) {
                $sukses = "Berhasil! Stok berkurang sebanyak $jumlah unit.";
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar - WMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS FIX FULL HEIGHT */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background-color: #f0f0f0; 
            margin: 0; 
            padding: 0; 
            display: flex; 
            height: 100vh; /* Tinggi layar penuh */
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
        
        .card { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); max-width: 600px; margin: auto; }

        /* Form Styles */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 13px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }

        .btn-warning { background: #ffc107; color: black; border: none; padding: 12px 20px; cursor: pointer; border-radius: 4px; width: 100%; font-size: 14px; font-weight: bold; transition: 0.3s; }
        .btn-warning:hover { background: #e0a800; }

        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 4px; text-align: center; }
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

            <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Transaksi</div>
            
            <a href="barang_masuk.php"><i data-lucide="arrow-down-circle"></i> Barang Masuk</a>
            <a href="request_masuk.php"><i data-lucide="inbox"></i> Permintaan Barang</a>
            <a href="barang_keluar.php" class="active"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
            <a href="stock_opname.php"><i data-lucide="clipboard-check"></i> Stock Opname</a>

            <?php if($role_user == 'admin'): ?>
                <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
                <a href="master_barang.php"><i data-lucide="package"></i> Data Barang</a>
                <a href="master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
                <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
                <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
                <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
                <a href="laporan.php"><i data-lucide="file-text"></i> Laporan</a>
            <?php endif; ?>

            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1 style="text-align: center; margin-bottom: 30px;">Input Barang Keluar (Manual)</h1>

            <?php if($sukses): ?>
                <div class="alert-success"><?= $sukses ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="">
                    
                    <div class="form-group">
                        <label>Tanggal Keluar</label>
                        <input type="datetime-local" name="tanggal" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Pilih Barang</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while($brg = mysqli_fetch_assoc($q_barang)){
                                echo "<option value='{$brg['id']}'>{$brg['nama_barang']} (Sisa Stok: {$brg['stok']} {$brg['satuan']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Divisi Pemohon / Tujuan</label>
                        <select name="id_divisi" required>
                            <option value="">-- Pilih Divisi --</option>
                            <?php
                            $q_divisi = mysqli_query($koneksi, "SELECT * FROM divisi ORDER BY nama_divisi ASC");
                            while($div = mysqli_fetch_assoc($q_divisi)){
                                echo "<option value='{$div['id']}'>{$div['nama_divisi']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Keluar</label>
                        <input type="number" name="jumlah" min="1" placeholder="Masukkan jumlah barang..." required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Penggunaan darurat, Barang rusak dibuang..."></textarea>
                    </div>

                    <button type="submit" name="simpan_keluar" class="btn-warning">Proses Barang Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>