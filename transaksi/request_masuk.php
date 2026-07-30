<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Requester DILARANG MASUK
if (!isset($_SESSION['status']) || $_SESSION['role'] == 'requester') {
    header("Location: ../index.php");
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

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

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