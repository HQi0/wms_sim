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

// Ambil filter, default bulan & tahun sekarang
$bulan_pilih = isset($_POST['bulan']) ? $_POST['bulan'] : date('m');
$tahun_pilih = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bulanan - WMS</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS FIX FULL HEIGHT (KONSISTEN) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; display: flex; height: 100vh; overflow: hidden; }
        .wrapper { display: flex; width: 100%; height: 100%; }
        
        .sidebar { width: 260px; background: #162433; color: white; padding: 20px; box-sizing: border-box; flex-shrink: 0; display: flex; flex-direction: column; height: 100%; overflow-y: auto; }
        .sidebar h2 { font-size: 20px; margin-bottom: 10px; border-bottom: 1px solid #444; padding-bottom: 10px; }
        .sidebar .user-info { font-size: 13px; color: #bbb; margin-bottom: 30px; }
        .sidebar a { display: flex; align-items: center; gap: 10px; color: #ccc; text-decoration: none; padding: 12px 15px; border-bottom: 1px solid #2a3c50; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { color: #FF7F27; background: #2a3c50; padding-left: 20px; }
        
        .content { flex: 1; padding: 30px; overflow-y: auto; height: 100%; }
        
        .filter-box { background: white; padding: 20px; border-radius: 5px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        select, .btn { padding: 10px; border-radius: 4px; border: 1px solid #ddd; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; border: none; cursor: pointer; }
        .btn-pdf { background: #dc3545; color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; border: none; }
        .btn-pdf:hover { background: #c82333; }

        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-top: 10px; font-size: 13px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #162433; color: white; }
        tr:nth-child(even) { background-color: #f9f9f9; }

        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; }
        .bg-in { background: #d4edda; color: #155724; }
        .bg-out { background: #f8d7da; color: #721c24; }
        .bg-adj { background: #e2e3e5; color: #383d41; }
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
            <a href="master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
            <a href="master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
            <a href="master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
            
            <a href="laporan.php" class="active"><i data-lucide="file-text"></i> Laporan</a>

            <a href="logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
        </div>

        <div class="content">
            <h1>Laporan Mutasi Barang</h1>

            <div class="filter-box">
                <form method="POST" action="" style="display: flex; gap: 10px; width: 100%; align-items: center;">
                    <select name="bulan">
                        <?php
                        $bulan_arr = [
                            '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April',
                            '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
                            '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
                        ];
                        foreach($bulan_arr as $k => $v){
                            $sel = ($bulan_pilih == $k) ? 'selected' : '';
                            echo "<option value='$k' $sel>$v</option>";
                        }
                        ?>
                    </select>
                    
                    <select name="tahun">
                        <?php
                        for($i=2024; $i<=date('Y'); $i++){
                            $sel = ($tahun_pilih == $i) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                    
                    <a href="cetak_laporan.php?bulan=<?= $bulan_pilih ?>&tahun=<?= $tahun_pilih ?>" target="_blank" class="btn btn-pdf">
                        <i data-lucide="file-down" width="16"></i> Download PDF
                    </a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Tanggal</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jenis</th>
                        <th>Jml</th>
                        <th>Keterangan / Partner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query mengambil semua transaksi (Masuk, Keluar, Adjustment) yang statusnya COMPLETED/APPROVED
                    $query = "SELECT t.*, b.kode_barang, b.nama_barang, s.nama_supplier, d.nama_divisi 
                              FROM transaksi t
                              JOIN barang b ON t.id_barang = b.id
                              LEFT JOIN supplier s ON t.id_supplier = s.id
                              LEFT JOIN divisi d ON t.id_divisi = d.id
                              WHERE MONTH(t.tanggal) = '$bulan_pilih' AND YEAR(t.tanggal) = '$tahun_pilih' 
                              AND t.status != 'pending' AND t.status != 'rejected'
                              ORDER BY t.tanggal ASC";
                    
                    $result = mysqli_query($koneksi, $query);
                    $no = 1;
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $tgl = date('d/m/Y H:i', strtotime($row['tanggal']));
                            
                            $jenis_label = "";
                            $ket_detail = "";

                            if($row['jenis'] == 'masuk'){
                                $jenis_label = "<span class='badge bg-in'>Masuk</span>";
                                $ket_detail = "Supplier: " . ($row['nama_supplier'] ?? '-');
                            } else if ($row['jenis'] == 'keluar') {
                                $jenis_label = "<span class='badge bg-out'>Keluar</span>";
                                $ket_detail = "Divisi: " . ($row['nama_divisi'] ?? '-');
                            } else {
                                $jenis_label = "<span class='badge bg-adj'>Opname</span>";
                                $ket_detail = "Penyesuaian Stok";
                            }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $tgl ?></td>
                        <td><?= $row['kode_barang'] ?></td>
                        <td><?= $row['nama_barang'] ?></td>
                        <td><?= $jenis_label ?></td>
                        <td><b><?= $row['jumlah'] ?></b></td>
                        <td>
                            <?= $ket_detail ?><br>
                            <small style="color: #666;"><?= $row['keterangan'] ?></small>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Tidak ada data transaksi pada periode ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>