<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Admin & Operator BOLEH MASUK. Requester DILARANG.
if (!isset($_SESSION['status']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator')) {
    header("Location: index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$id_user   = $_SESSION['id_user'];

$sukses = "";
$error = "";

// --- LOGIKA PROSES OPNAME ---
if (isset($_POST['simpan_opname'])) {
    $id_barang = $_POST['id_barang'];
    $stok_fisik = (int) $_POST['stok_fisik'];
    $catatan = $_POST['keterangan'];

    // 1. Ambil Stok Sistem Terkini
    $cek_brg = mysqli_query($koneksi, "SELECT stok, nama_barang FROM barang WHERE id='$id_barang'");
    $data_brg = mysqli_fetch_assoc($cek_brg);
    
    if ($data_brg) {
        $stok_sistem = $data_brg['stok'];
        $selisih = $stok_fisik - $stok_sistem;
        $tgl_sekarang = date('Y-m-d H:i:s');

        // Jika tidak ada selisih, beri info
        if ($selisih == 0) {
            $error = "Stok Fisik sama dengan Sistem. Tidak ada perubahan yang disimpan.";
        } else {
            // Tentukan keterangan otomatis
            if ($selisih > 0) {
                $detail_selisih = "(Lebih +$selisih)";
            } else {
                $detail_selisih = "(Hilang/Kurang $selisih)";
            }
            $ket_lengkap = "Stock Opname: System $stok_sistem -> Fisik $stok_fisik. $detail_selisih. Ket: $catatan";

            // 2. Update Stok Master Barang
            $update_master = mysqli_query($koneksi, "UPDATE barang SET stok='$stok_fisik' WHERE id='$id_barang'");

            // 3. Catat Riwayat Transaksi (Jenis: adjustment)
            // Jumlah di transaksi kita simpan angka positif (absolute) untuk history
            $jml_transaksi = abs($selisih); 
            
            $insert_log = mysqli_query($koneksi, "INSERT INTO transaksi 
                (id_barang, id_user, jenis, jumlah, tanggal, keterangan, status, stok_awal_sistem) 
                VALUES 
                ('$id_barang', '$id_user', 'adjustment', '$jml_transaksi', '$tgl_sekarang', '$ket_lengkap', 'completed', '$stok_sistem')");

            if ($update_master && $insert_log) {
                $sukses = "Berhasil! Stok barang <b>{$data_brg['nama_barang']}</b> kini disesuaikan menjadi <b>$stok_fisik</b>.";
            } else {
                $error = "Terjadi kesalahan database: " . mysqli_error($koneksi);
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
    <title>Stock Opname - WMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS STANDARD */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; display: flex; height: 100vh; }
        .wrapper { display: flex; width: 100%; height: 100%; }
        
        .sidebar { width: 260px; background: #162433; color: white; padding: 20px; box-sizing: border-box; flex-shrink: 0; display: flex; flex-direction: column; }
        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        
        .content { flex: 1; padding: 30px; overflow-y: auto; }
        .card { background: white; padding: 25px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; font-size: 13px; }
        .btn-primary { background: #007bff; } .btn-primary:hover { background: #0056b3; }
        
        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; }
        th { background: #162433; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .badge-loss { background: #ffebee; color: #c62828; padding: 3px 6px; border-radius: 3px; font-weight: bold; }
        .badge-plus { background: #e8f5e9; color: #2e7d32; padding: 3px 6px; border-radius: 3px; font-weight: bold; }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="sidebar">
            <h2>WMS SYSTEM</h2>
            <div style="font-size: 13px; color: #bbb; margin-bottom: 30px;">
                Halo, <b><?= $nama_user ?></b> (<?= ucfirst($role_user) ?>)
            </div>
            
            <a href="index.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>
            
            <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Transaksi</div>
            <a href="barang_masuk.php"><i data-lucide="arrow-down-circle"></i> Barang Masuk</a>
            <a href="request_masuk.php"><i data-lucide="inbox"></i> Permintaan Barang</a>
            <a href="barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
            <a href="stock_opname.php" style="background: #2a3c50; color: #FF7F27; border-left: 4px solid #FF7F27;"><i data-lucide="clipboard-check"></i> Stock Opname</a>

            <?php if($role_user == 'admin'): ?>
                <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
                <a href="master_barang.php"><i data-lucide="package"></i> Data Barang</a>
                <a href="master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
                <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
                <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
                <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
            <?php endif; ?>
            
            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Stock Opname (Penyesuaian Stok)</h1>
            <p>Gunakan fitur ini jika stok fisik di gudang berbeda dengan stok di sistem.</p>

            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3>Input Hasil Opname</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Pilih Barang</label>
                            <select name="id_barang" required style="font-size: 14px;">
                                <option value="">-- Cari Barang --</option>
                                <?php
                                $q = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                                while($row = mysqli_fetch_assoc($q)){
                                    // Tampilkan stok sistem di dropdown agar user tahu
                                    echo "<option value='{$row['id']}'>{$row['nama_barang']} (Sistem: {$row['stok']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Stok Fisik (Hasil Hitung)</label>
                            <input type="number" name="stok_fisik" min="0" required placeholder="Masukkan jumlah real di rak...">
                        </div>

                        <div class="form-group">
                            <label>Keterangan / Alasan</label>
                            <textarea name="keterangan" rows="3" required placeholder="Contoh: Barang rusak air, Barang terselip, Salah hitung sebelumnya..."></textarea>
                        </div>

                        <button type="submit" name="simpan_opname" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin data fisik sudah benar? Stok akan langsung berubah.')">Simpan Penyesuaian</button>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Riwayat Stock Opname</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Sistem</th>
                                <th>Fisik</th>
                                <th>Selisih</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil 10 transaksi terakhir yang jenisnya 'adjustment'
                            $q_log = mysqli_query($koneksi, "
                                SELECT t.*, b.nama_barang, u.username 
                                FROM transaksi t 
                                JOIN barang b ON t.id_barang = b.id
                                JOIN users u ON t.id_user = u.id
                                WHERE t.jenis = 'adjustment'
                                ORDER BY t.tanggal DESC LIMIT 10
                            ");
                            
                            if(mysqli_num_rows($q_log) > 0){
                                while($log = mysqli_fetch_assoc($q_log)){
                                    // Hitung fisik berdasarkan snapshot stok awal + selisih
                                    // Namun cara paling mudah membaca 'keterangan' atau menghitung ulang
                                    // Disini kita lakukan logika sederhana:
                                    // Jika adjustment, 'jumlah' adalah nilai absolut selisih.
                                    // Kita parse dari keterangan saja agar akurat jika ada +/-
                                    
                                    // Logika Visual Selisih
                                    // Kita cek manual sederhana:
                                    // Jika keterangan mengandung "Hilang/Kurang", berarti minus
                                    $is_loss = strpos($log['keterangan'], "Hilang") !== false || strpos($log['keterangan'], "Kurang") !== false;
                                    
                                    if ($is_loss) {
                                        $selisih_text = "<span class='badge-loss'>-{$log['jumlah']}</span>";
                                        $fisik = $log['stok_awal_sistem'] - $log['jumlah'];
                                    } else {
                                        $selisih_text = "<span class='badge-plus'>+{$log['jumlah']}</span>";
                                        $fisik = $log['stok_awal_sistem'] + $log['jumlah'];
                                    }
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?></td>
                                <td><?= $log['nama_barang'] ?></td>
                                <td><?= $log['stok_awal_sistem'] ?></td>
                                <td><b><?= $fisik ?></b></td>
                                <td><?= $selisih_text ?></td>
                                <td><?= ucfirst($log['username']) ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Belum ada data opname.</td></tr>";
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