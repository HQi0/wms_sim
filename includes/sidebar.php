<?php
// Hitung Notifikasi (Untuk Admin/Operator: Request Pending)
$jml_pending_sidebar = 0;
if (isset($role_user) && ($role_user == 'admin' || $role_user == 'operator')) {
    $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM transaksi WHERE status='pending'");
    if($q_pending) {
        $d_pending = mysqli_fetch_assoc($q_pending);
        $jml_pending_sidebar = $d_pending['total'];
    }
}
?>
<div class="sidebar">
    <h2>WMS SYSTEM</h2>
    <div class="user-info">
        Halo, <b><?= $nama_user ?></b><br>
        Role: <?= strtoupper($role_user) ?>
    </div>

    <!-- Gunakan BASE_URL untuk memastikan path selalu benar -->
    <a href="<?= BASE_URL ?>index.php"><i data-lucide="layout-dashboard"></i> Dashboard</a>

    <?php if($role_user == 'admin' || $role_user == 'operator'): ?>
        <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Transaksi</div>
        
        <a href="<?= BASE_URL ?>transaksi/barang_masuk.php"><i data-lucide="arrow-down-circle"></i> Barang Masuk</a>
        
        <a href="<?= BASE_URL ?>transaksi/request_masuk.php">
            <i data-lucide="inbox"></i> Permintaan Barang
            <?php if($jml_pending_sidebar > 0): ?>
                <span class="badge"><?= $jml_pending_sidebar ?></span>
            <?php endif; ?>
        </a>

        <a href="<?= BASE_URL ?>transaksi/barang_keluar.php"><i data-lucide="arrow-up-circle"></i> Barang Keluar</a>
        <a href="<?= BASE_URL ?>transaksi/stock_opname.php"><i data-lucide="clipboard-check"></i> Stock Opname</a>
    <?php endif; ?>

    <?php if($role_user == 'admin'): ?>
        <div style="margin-top:10px; padding: 5px 15px; font-size: 11px; color: #666; text-transform: uppercase; font-weight: bold;">Master Data</div>
        <a href="<?= BASE_URL ?>master/master_barang.php"><i data-lucide="package"></i> Data Barang</a>
        <a href="<?= BASE_URL ?>master/master_lokasi.php"><i data-lucide="map-pin"></i> Data Lokasi Rak</a>
        <a href="<?= BASE_URL ?>master/master_supplier.php"><i data-lucide="truck"></i> Data Supplier</a>
        <a href="<?= BASE_URL ?>master/master_divisi.php"><i data-lucide="users"></i> Data Divisi</a>
        <a href="<?= BASE_URL ?>master/master_user.php"><i data-lucide="user-cog"></i> Manajemen User</a>
        <a href="<?= BASE_URL ?>laporan/laporan.php"><i data-lucide="file-text"></i> Laporan</a>
    <?php endif; ?>

    <?php if($role_user == 'requester'): ?>
        <a href="<?= BASE_URL ?>transaksi/buat_request.php"><i data-lucide="shopping-cart"></i> Request Barang</a>
        <a href="<?= BASE_URL ?>transaksi/riwayat_request.php"><i data-lucide="history"></i> Status Request Saya</a>
    <?php endif; ?>

    <a href="<?= BASE_URL ?>auth/logout.php" style="margin-top: auto; color: #ff6b6b; border-top: 1px solid #444;"><i data-lucide="log-out"></i> Logout</a>
</div>
