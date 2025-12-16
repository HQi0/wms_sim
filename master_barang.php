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
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori = $_POST['kategori'];
    $stok = (int) $_POST['stok'];
    $satuan = $_POST['satuan'];
    $min_stok = (int) $_POST['stok_minimal'];
    $id_lokasi = $_POST['id_lokasi']; 

    // Pastikan lokasi dipilih, jika kosong set NULL
    $lokasi_sql = empty($id_lokasi) ? "NULL" : "'$id_lokasi'";

    // LOGIKA SIMPAN BARU
    if (isset($_POST['simpan'])) {
        // Cek Kode Unik
        $cek_kode = mysqli_query($koneksi, "SELECT * FROM barang WHERE kode_barang='$kode'");
        if (mysqli_num_rows($cek_kode) > 0) {
            $error = "Gagal: Kode Barang <b>$kode</b> sudah digunakan!";
        } else {
            $query = "INSERT INTO barang (kode_barang, nama_barang, kategori, stok, satuan, stok_minimal, id_lokasi) 
                      VALUES ('$kode', '$nama', '$kategori', '$stok', '$satuan', '$min_stok', $lokasi_sql)";
            
            if (mysqli_query($koneksi, $query)) {
                $sukses = "Data Barang Berhasil Ditambahkan";
            } else {
                $error = "Gagal menyimpan: " . mysqli_error($koneksi);
            }
        }
    } 
    // LOGIKA UPDATE / EDIT
    else if (isset($_POST['update'])) {
        $id = $_POST['id_barang'];
        
        $query = "UPDATE barang SET 
                    nama_barang='$nama', 
                    kategori='$kategori', 
                    stok_minimal='$min_stok', 
                    satuan='$satuan',
                    id_lokasi=$lokasi_sql
                  WHERE id='$id'";
        
        if (mysqli_query($koneksi, $query)) {
            // Redirect langsung agar URL bersih
            header("Location: master_barang.php");
            exit;
        } else {
            $error = "Gagal update: " . mysqli_error($koneksi);
        }
    }
}

// LOGIKA HAPUS
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    if (mysqli_query($koneksi, "DELETE FROM barang WHERE id='$id_hapus'")) {
        header("Location: master_barang.php");
        exit;
    } else {
        $error = "Gagal menghapus! Barang ini mungkin sudah ada di riwayat transaksi. Hapus transaksinya dulu.";
    }
}

// --- LOGIKA PERSIAPAN FORM EDIT ---
$kode_edit = ""; $nama_edit = ""; $stok_edit = ""; $satuan_edit = ""; $min_edit = ""; 
$id_edit = ""; $kategori_edit = ""; $lokasi_edit = ""; 
$aksi = "simpan"; 

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $data_edit = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM barang WHERE id='$id_edit'"));
    if($data_edit) {
        $kode_edit = $data_edit['kode_barang']; 
        $nama_edit = $data_edit['nama_barang'];
        $stok_edit = $data_edit['stok']; 
        $satuan_edit = $data_edit['satuan'];
        $min_edit = $data_edit['stok_minimal']; 
        $kategori_edit = $data_edit['kategori'];
        $lokasi_edit = $data_edit['id_lokasi'];
        $aksi = "update";
    }
}

// --- LOGIKA PENCARIAN ---
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = $_GET['cari'];
    $q_str = "SELECT b.*, l.nama_lokasi FROM barang b 
              LEFT JOIN lokasi_rak l ON b.id_lokasi = l.id
              WHERE b.kode_barang LIKE '%$keyword%' OR b.nama_barang LIKE '%$keyword%' 
              ORDER BY b.id DESC";
} else {
    $q_str = "SELECT b.*, l.nama_lokasi FROM barang b 
              LEFT JOIN lokasi_rak l ON b.id_lokasi = l.id
              ORDER BY b.id DESC";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Barang - WMS</title>
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
        
        .card { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; display: inline-block; font-size: 13px; margin-right: 5px; }
        .btn-primary { background: #28a745; } 
        .btn-warning { background: #ffc107; color: black; } 
        .btn-danger { background: #dc3545; } 

        table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #162433; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 4px; }

        .search-box { display: flex; gap: 10px; margin-bottom: 15px; }
        .search-box input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
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
            <a href="barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
            <a href="stock_opname.php"><i data-lucide="clipboard-check"></i> Stock Opname</a>

            <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
            
            <a href="master_barang.php" class="active"><i data-lucide="package"></i> Data Barang</a>
            <a href="master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
            <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
            <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
            <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
            <a href="laporan.php"><i data-lucide="file-text"></i> Laporan</a>

            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Data Master Barang</h1>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>

            <div class="card">
                <h3><?= ($aksi == 'simpan') ? 'Tambah Barang Baru' : 'Edit Barang'; ?></h3>
                <form method="POST" action="">
                    <input type="hidden" name="id_barang" value="<?= $id_edit ?>">
                    
                    <div style="display: flex; gap: 20px;">
                        <div style="flex: 1;">
                            <div class="form-group"><label>Kode Barang</label><input type="text" name="kode_barang" value="<?= $kode_edit ?>" required <?= ($aksi=='update') ? 'readonly style="background:#e9ecef;"' : '' ?>></div>
                            <div class="form-group"><label>Nama Barang</label><input type="text" name="nama_barang" value="<?= $nama_edit ?>" required></div>
                            <div class="form-group"><label>Kategori</label>
                                <select name="kategori">
                                    <option value="Bahan Baku" <?= ($kategori_edit=='Bahan Baku')?'selected':'' ?>>Bahan Baku</option>
                                    <option value="Barang Jadi" <?= ($kategori_edit=='Barang Jadi')?'selected':'' ?>>Barang Jadi</option>
                                    <option value="Sparepart" <?= ($kategori_edit=='Sparepart')?'selected':'' ?>>Sparepart</option>
                                </select>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <div class="form-group"><label>Lokasi Penyimpanan (Rak)</label>
                                <select name="id_lokasi" required>
                                    <option value="">-- Pilih Lokasi --</option>
                                    <?php
                                    $q_rak = mysqli_query($koneksi, "SELECT * FROM lokasi_rak ORDER BY nama_lokasi ASC");
                                    while($rak = mysqli_fetch_assoc($q_rak)){
                                        $selected = ($lokasi_edit == $rak['id']) ? 'selected' : '';
                                        echo "<option value='{$rak['id']}' $selected>{$rak['nama_lokasi']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group"><label>Satuan</label><input type="text" name="satuan" value="<?= $satuan_edit ?>" required placeholder="Pcs, Unit, Lembar..."></div>
                            <div class="form-group"><label>Stok Minimal (Alert)</label><input type="number" name="stok_minimal" value="<?= $min_edit ?>" required></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Stok Awal</label>
                        <input type="number" name="stok" value="<?= $stok_edit ?>" required <?= ($aksi=='update') ? 'readonly style="background:#e9ecef;"' : '' ?>>
                        <?php if($aksi == 'update'): ?><small style="color: grey;">*Stok hanya bisa diubah lewat Transaksi atau Stock Opname.</small><?php endif; ?>
                    </div>

                    <button type="submit" name="<?= $aksi ?>" class="btn btn-primary"><?= ($aksi == 'simpan') ? 'Simpan Barang' : 'Simpan Perubahan'; ?></button>
                    <?php if($aksi == 'update'): ?><a href="master_barang.php" class="btn btn-danger">Batal Edit</a><?php endif; ?>
                </form>
            </div>

            <div class="card">
                <h3>Daftar Inventory</h3>
                <form action="" method="GET" class="search-box">
                    <input type="text" name="cari" placeholder="Cari Kode atau Nama Barang..." value="<?= $keyword ?>" autocomplete="off">
                    <button type="submit" class="btn" style="background: #162433; color: white; margin:0;">Cari</button>
                    <?php if($keyword): ?><a href="master_barang.php" class="btn btn-danger" style="margin-left: 5px;">X</a><?php endif; ?>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Lokasi</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Min.</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($koneksi, $q_str);
                        if (mysqli_num_rows($q) > 0) {
                            while($row = mysqli_fetch_assoc($q)){
                        ?>
                        <tr>
                            <td><?= $row['kode_barang'] ?></td>
                            <td><?= $row['nama_barang'] ?></td>
                            <td><span style="background: #e3f2fd; color: #1565c0; padding: 2px 6px; border-radius: 4px; font-size: 11px;"><?= $row['nama_lokasi'] ?? '-' ?></span></td>
                            <td><?= $row['kategori'] ?></td>
                            <td><b><?= $row['stok'] ?></b> <?= $row['satuan'] ?></td>
                            <td><?= $row['stok_minimal'] ?></td>
                            <td>
                                <a href="master_barang.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 11px;">Edit</a>
                                <a href="master_barang.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 11px;" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data.</td></tr>";
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