<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Hanya Requester
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'requester') {
    header("Location: ../index.php"); exit;
}
$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
$id_user = $_SESSION['id_user'];
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Riwayat Permintaan Saya</h1>
            
            <div class="card">
                <h3>Daftar Transaksi</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Jumlah</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Keterangan Admin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil data milik user ini saja
                        $q = mysqli_query($koneksi, "
                            SELECT t.*, b.nama_barang, b.satuan, d.nama_divisi
                            FROM transaksi t
                            JOIN barang b ON t.id_barang = b.id
                            LEFT JOIN divisi d ON t.id_divisi = d.id
                            WHERE t.id_user = '$id_user' AND t.jenis = 'keluar'
                            ORDER BY t.tanggal DESC
                        ");

                        if(mysqli_num_rows($q) > 0){
                            while($row = mysqli_fetch_assoc($q)){
                                // Badge Warna
                                if($row['status'] == 'pending') $stat = "<span class='badge pending'>Menunggu</span>";
                                elseif($row['status'] == 'approved') $stat = "<span class='badge approved'>Disetujui</span>";
                                else $stat = "<span class='badge rejected'>Ditolak</span>";
                        ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($row['tanggal'])) ?></td>
                            <td><?= $row['nama_barang'] ?></td>
                            <td><b><?= $row['jumlah'] ?></b> <?= $row['satuan'] ?></td>
                            <td><?= $row['nama_divisi'] ?></td>
                            <td><?= $stat ?></td>
                            <td>
                                <i>"<?= $row['keterangan'] ?>"</i>
                                <?php if($row['status']=='approved'): ?>
                                    <br><small style="color:green; font-weight:bold;">Silakan ambil barang di gudang.</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:30px; color:#888;'>Belum ada riwayat permintaan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>