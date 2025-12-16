<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Hanya Admin
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$error = ""; 
$sukses = "";

// --- LOGIKA SIMPAN / UPDATE / HAPUS ---

if (isset($_POST['simpan']) || isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $kontak = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // SIMPAN BARU
    if (isset($_POST['simpan'])) {
        $q = "INSERT INTO supplier (nama_supplier, kontak, alamat) VALUES ('$nama', '$kontak', '$alamat')";
        if (mysqli_query($koneksi, $q)) {
            $sukses = "Supplier berhasil ditambahkan.";
        } else {
            $error = "Gagal: " . mysqli_error($koneksi);
        }
    } 
    // UPDATE DATA
    else if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $q = "UPDATE supplier SET nama_supplier='$nama', kontak='$kontak', alamat='$alamat' WHERE id='$id'";
        if (mysqli_query($koneksi, $q)) {
            header("Location: master_supplier.php");
            exit;
        } else {
            $error = "Gagal update: " . mysqli_error($koneksi);
        }
    }
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (mysqli_query($koneksi, "DELETE FROM supplier WHERE id='$id'")) {
        header("Location: master_supplier.php");
        exit;
    } else {
        $error = "Gagal hapus! Data ini mungkin sedang digunakan di riwayat transaksi.";
    }
}

// --- LOGIKA EDIT ---
$nama_edit = ""; $kontak_edit = ""; $alamat_edit = ""; $id_edit = ""; 
$aksi = "simpan";

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM supplier WHERE id='$id_edit'"));
    if($r) {
        $nama_edit = $r['nama_supplier']; 
        $kontak_edit = $r['kontak']; 
        $alamat_edit = $r['alamat']; 
        $aksi = "update";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Supplier - WMS</title>
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

        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        /* Buttons */
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; transition: 0.2s; text-decoration: none; display: inline-block; font-size: 13px; }
        .btn-primary { background: #28a745; } 
        .btn-warning { background: #ffc107; color: black; } 
        .btn-danger { background: #dc3545; } 
        
        /* Alerts */
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 4px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
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
            <a href="master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
            <a href="master_supplier.php" class="active"><i data-lucide="truck"></i> Data Supplier</a>
            <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
            <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
            <a href="laporan.php"><i data-lucide="file-text"></i> Laporan</a>
            
            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Data Supplier</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah Supplier' : 'Edit Supplier'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Nama Supplier</label>
                            <input type="text" name="nama_supplier" value="<?= $nama_edit ?>" required placeholder="Contoh: PT Logam Jaya">
                        </div>
                        
                        <div class="form-group">
                            <label>Kontak (HP / Telepon)</label>
                            <input type="text" name="kontak" value="<?= $kontak_edit ?>" required placeholder="0812...">
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" required placeholder="Alamat kantor supplier..."><?= $alamat_edit ?></textarea>
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Simpan Data' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_supplier.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Supplier</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama Supplier</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
                            if(mysqli_num_rows($q) > 0){
                                while($row = mysqli_fetch_assoc($q)){
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><b><?= $row['nama_supplier'] ?></b></td>
                                <td><?= $row['kontak'] ?></td>
                                <td><?= $row['alamat'] ?></td>
                                <td>
                                    <a href="master_supplier.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 11px; padding: 5px 8px;">Edit</a>
                                    <a href="master_supplier.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 8px;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Belum ada data supplier.</td></tr>";
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