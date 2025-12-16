<?php
session_start();
include 'koneksi.php';

// CEK AKSES: Requester DILARANG MASUK
if (!isset($_SESSION['status']) || $_SESSION['role'] == 'requester') {
    header("Location: index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$id_approver = $_SESSION['id_user']; // ID Admin/Operator yang login

$sukses = "";
$error = "";

// --- LOGIKA PERSETUJUAN / PENOLAKAN ---

if (isset($_POST['aksi_request'])) {
    $id_transaksi = $_POST['id_transaksi'];
    $jenis_aksi = $_POST['aksi_request']; // 'terima' atau 'tolak'
    
    // Ambil data transaksi dulu untuk cek jumlah & barang
    $cek_tr = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE id='$id_transaksi' AND status='pending'");
    $data_tr = mysqli_fetch_assoc($cek_tr);

    if ($data_tr) {
        $id_barang = $data_tr['id_barang'];
        $jumlah_minta = $data_tr['jumlah'];
        $tgl_now = date('Y-m-d H:i:s');

        if ($jenis_aksi == 'terima') {
            // 1. Cek Stok Cukup Gak?
            $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id='$id_barang'");
            $data_stok = mysqli_fetch_assoc($cek_stok);
            
            if ($data_stok['stok'] >= $jumlah_minta) {
                // 2. Kurangi Stok
                $stok_baru = $data_stok['stok'] - $jumlah_minta;
                mysqli_query($koneksi, "UPDATE barang SET stok='$stok_baru' WHERE id='$id_barang'");
                
                // 3. Update Status jadi APPROVED
                mysqli_query($koneksi, "UPDATE transaksi SET status='approved', id_user='$id_approver', tanggal='$tgl_now' WHERE id='$id_transaksi'");
                
                $sukses = "Permintaan berhasil DISETUJUI. Stok telah berkurang.";
            } else {
                $error = "Gagal: Stok gudang tidak cukup! (Sisa: {$data_stok['stok']})";
            }
        } 
        else if ($jenis_aksi == 'tolak') {
            // Update Status jadi REJECTED
            mysqli_query($koneksi, "UPDATE transaksi SET status='rejected', id_user='$id_approver', tanggal='$tgl_now' WHERE id='$id_transaksi'");
            $sukses = "Permintaan berhasil DITOLAK.";
        }
    } else {
        $error = "Data transaksi tidak ditemukan atau sudah diproses sebelumnya.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Request - WMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS FIX FULL HEIGHT (KONSISTEN DENGAN INDEX) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; display: flex; height: 100vh; overflow: hidden; }
        .wrapper { display: flex; width: 100%; height: 100%; }
        
        .sidebar { width: 260px; background: #162433; color: white; padding: 20px; box-sizing: border-box; flex-shrink: 0; display: flex; flex-direction: column; height: 100%; overflow-y: auto; }
        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        
        .content { flex: 1; padding: 30px; overflow-y: auto; height: 100%; }
        .card { background: white; padding: 25px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }

        .alert-success { background: #d4edda; color: #155724; padding: 15px; margin-bottom: 15px; border-radius: 4px; }
        .alert-danger { background: #f8d7da; color: #721c24; padding: 15px; margin-bottom: 15px; border-radius: 4px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #eee; padding: 12px; text-align: left; vertical-align: middle; }
        th { background: #162433; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .btn-approve { background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }
        .btn-reject { background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px; display: inline-flex; align-items: center; gap: 5px; }
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
            <a href="request_masuk.php" class="active"><i data-lucide="inbox"></i> Permintaan Barang</a>
            <a href="barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
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
            <h1>Persetujuan Permintaan Barang</h1>
            <p>Daftar permintaan barang dari Divisi yang menunggu persetujuan (Pending).</p>

            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div class="card">
                <h3>Inbox Request</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tgl Request</th>
                            <th>Pemohon / Divisi</th>
                            <th>Nama Barang</th>
                            <th>Jml Minta</th>
                            <th>Stok Gudang</th>
                            <th>Keterangan</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Query mengambil transaksi pending
                        $query = "SELECT t.id as id_transaksi, t.tanggal, t.jumlah, t.keterangan, 
                                         b.nama_barang, b.stok as stok_sekarang, b.satuan,
                                         u.nama_lengkap, d.nama_divisi
                                  FROM transaksi t
                                  JOIN barang b ON t.id_barang = b.id
                                  JOIN users u ON t.id_user = u.id
                                  LEFT JOIN divisi d ON t.id_divisi = d.id
                                  WHERE t.status = 'pending'
                                  ORDER BY t.tanggal ASC";
                        
                        $result = mysqli_query($koneksi, $query);

                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $tgl = date('d/m/Y H:i', strtotime($row['tanggal']));
                                
                                // Cek apakah stok aman?
                                if ($row['stok_sekarang'] < $row['jumlah']) {
                                    $info_stok = "<span style='color:red; font-weight:bold;'>{$row['stok_sekarang']} (Kurang)</span>";
                                    $btn_disabled = "disabled style='background:grey; cursor:not-allowed;' title='Stok tidak cukup'";
                                } else {
                                    $info_stok = "<span style='color:green; font-weight:bold;'>{$row['stok_sekarang']} (Aman)</span>";
                                    $btn_disabled = "";
                                }
                        ?>
                        <tr>
                            <td><?= $tgl ?></td>
                            <td>
                                <b><?= $row['nama_lengkap'] ?></b><br>
                                <small>Divisi: <?= $row['nama_divisi'] ?? '-' ?></small>
                            </td>
                            <td><?= $row['nama_barang'] ?></td>
                            <td style="font-size: 16px; font-weight: bold;"><?= $row['jumlah'] ?> <?= $row['satuan'] ?></td>
                            <td><?= $info_stok ?></td>
                            <td><?= $row['keterangan'] ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="id_transaksi" value="<?= $row['id_transaksi'] ?>">
                                    
                                    <button type="submit" name="aksi_request" value="terima" class="btn-approve" <?= $btn_disabled ?> onclick="return confirm('Setujui permintaan ini? Stok akan berkurang.')">
                                        <i data-lucide="check" width="14"></i> Setuju
                                    </button>
                                    
                                    <button type="submit" name="aksi_request" value="tolak" class="btn-reject" onclick="return confirm('Tolak permintaan ini?')">
                                        <i data-lucide="x" width="14"></i> Tolak
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #999;'>
                                    <i data-lucide='check-circle' style='display:block; margin:auto; margin-bottom:10px;' width='40'></i>
                                    Tidak ada permintaan pending saat ini.
                                  </td></tr>";
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