<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Hanya Admin yang boleh masuk
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$sukses = "";
$error = "";

// --- LOGIKA SIMPAN / UPDATE / HAPUS ---

// 1. Simpan Baru
if (isset($_POST['simpan'])) {
    $nama_lokasi = mysqli_real_escape_string($koneksi, $_POST['nama_lokasi']);
    
    // Cek duplikat
    $cek = mysqli_query($koneksi, "SELECT * FROM lokasi_rak WHERE nama_lokasi = '$nama_lokasi'");
    if(mysqli_num_rows($cek) > 0){
        $error = "Gagal: Nama Lokasi '$nama_lokasi' sudah ada!";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO lokasi_rak (nama_lokasi) VALUES ('$nama_lokasi')");
        if ($simpan) $sukses = "Lokasi baru berhasil ditambahkan.";
        else $error = "Gagal menyimpan: " . mysqli_error($koneksi);
    }
}

// 2. Update Data
if (isset($_POST['update'])) {
    $id = $_POST['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($koneksi, $_POST['nama_lokasi']);
    
    $update = mysqli_query($koneksi, "UPDATE lokasi_rak SET nama_lokasi='$nama_lokasi' WHERE id='$id'");
    if ($update) {
        header("Location: master_lokasi.php"); 
        exit; 
    } else {
        $error = "Gagal update: " . mysqli_error($koneksi);
    }
}

// 3. Hapus Data
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $hapus = mysqli_query($koneksi, "DELETE FROM lokasi_rak WHERE id='$id_hapus'");
    if ($hapus) {
        header("Location: master_lokasi.php");
        exit;
    }
    else $error = "Gagal menghapus: " . mysqli_error($koneksi);
}

// --- LOGIKA PERSIAPAN EDIT ---
$nama_edit = "";
$id_edit = "";
$aksi = "simpan"; 

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $q_edit = mysqli_query($koneksi, "SELECT * FROM lokasi_rak WHERE id='$id_edit'");
    $data_edit = mysqli_fetch_assoc($q_edit);
    if ($data_edit) {
        $nama_edit = $data_edit['nama_lokasi'];
        $aksi = "update"; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Lokasi Rak - WMS</title>
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
        
        .card { background: white; padding: 25px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; transition: 0.2s; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #28a745; } 
        .btn-warning { background: #ffc107; color: black; } 
        .btn-danger { background: #dc3545; } 
        
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #162433; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="sidebar">
            <h2>WMS SYSTEM</h2>
            <div class="user-info">
                Halo, <b><?= $nama_user ?></b> (Admin)
            </div>
            
            <a href="index.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            
            <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Transaksi</div>
            <a href="barang_masuk.php"><i data-lucide="arrow-down-circle"></i> Barang Masuk</a>
            <a href="request_masuk.php"><i data-lucide="inbox"></i> Permintaan Barang</a>
            <a href="barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
            <a href="stock_opname.php"><i data-lucide="clipboard-check"></i> Stock Opname</a>

            <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
            <a href="master_barang.php"><i data-lucide="package"></i> Data Barang</a>
            <a href="master_lokasi.php" class="active"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
            <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
            <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
            <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
            <a href="laporan.php"><i data-lucide="file-text"></i> Laporan</a>
            
            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Manajemen Lokasi Rak / Gudang</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah Lokasi Baru' : 'Edit Lokasi'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id_lokasi" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Nama Lokasi / Rak</label>
                            <input type="text" name="nama_lokasi" value="<?= $nama_edit ?>" placeholder="Contoh: Rak A-01, Gudang Besi" required autofocus>
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Simpan Data' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_lokasi.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Lokasi Tersedia</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Lokasi</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_lok = mysqli_query($koneksi, "SELECT * FROM lokasi_rak ORDER BY nama_lokasi ASC");
                            if(mysqli_num_rows($q_lok) > 0){
                                while($row = mysqli_fetch_assoc($q_lok)){
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><b><?= $row['nama_lokasi'] ?></b></td>
                                <td>
                                    <a href="master_lokasi.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 12px; padding: 5px 10px;">Edit</a>
                                    <a href="master_lokasi.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 12px; padding: 5px 10px;" onclick="return confirm('Yakin hapus lokasi ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; padding: 20px;'>Belum ada data lokasi.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <script> lucide.createIcons(); </script>
</body>
</html>