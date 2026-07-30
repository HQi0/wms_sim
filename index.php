<?php
session_start();
include 'config/koneksi.php';

// Cek Login
if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("Location: auth/login.php");
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

<?php include 'includes/header.php'; ?>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>

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
                    <a href="<?= BASE_URL ?>transaksi/request_masuk.php" style="color: #856404; font-weight: bold;">Cek Sekarang</a>
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