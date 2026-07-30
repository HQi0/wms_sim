<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Hanya Requester
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'requester') {
    header("Location: ../index.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];
$role_user = $_SESSION['role'];
$id_user = $_SESSION['id_user'];
$sukses = "";
$error = "";

// --- LOGIKA SIMPAN REQUEST ---
if (isset($_POST['kirim_request'])) {
    $id_barang = $_POST['id_barang'];
    $id_divisi = $_POST['id_divisi'];
    $jumlah = $_POST['jumlah'];
    $keterangan = $_POST['keterangan'];
    $tanggal = date('Y-m-d H:i:s');

    // Cek stok (Visual only)
    $cek_stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT stok FROM barang WHERE id='$id_barang'"));
    
    if ($jumlah <= 0) {
        $error = "Jumlah permintaan harus lebih dari 0.";
    } else if ($cek_stok['stok'] < $jumlah) {
        $error = "Stok di sistem tidak cukup (Tersedia: {$cek_stok['stok']}). Hubungi Admin.";
    } else {
        $q = "INSERT INTO transaksi (id_barang, id_user, id_divisi, jenis, jumlah, tanggal, keterangan, status) 
              VALUES ('$id_barang', '$id_user', '$id_divisi', 'keluar', '$jumlah', '$tanggal', '$keterangan', 'pending')";
        
        if (mysqli_query($koneksi, $q)) {
            header("Location: riwayat_request.php");
            exit;
        } else {
            $error = "Gagal kirim: " . mysqli_error($koneksi);
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1 style="text-align:center; margin-bottom: 30px;">Form Pengajuan Barang</h1>

            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div class="card">
                <form method="POST">
                    <div class="form-group">
                        <label>Barang yang dibutuhkan</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while($r = mysqli_fetch_assoc($q)){
                                echo "<option value='{$r['id']}'>{$r['nama_barang']} (Tersedia: {$r['stok']} {$r['satuan']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Divisi Pemohon</label>
                        <select name="id_divisi" required>
                            <option value="">-- Pilih Divisi Anda --</option>
                            <?php
                            $qd = mysqli_query($koneksi, "SELECT * FROM divisi ORDER BY nama_divisi ASC");
                            while($d = mysqli_fetch_assoc($qd)){
                                echo "<option value='{$d['id']}'>{$d['nama_divisi']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Permintaan</label>
                        <input type="number" name="jumlah" min="1" required placeholder="Masukkan jumlah...">
                    </div>

                    <div class="form-group">
                        <label>Keterangan / Keperluan</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Untuk kebutuhan produksi mesin B..."></textarea>
                    </div>

                    <button type="submit" name="kirim_request">Kirim Permintaan</button>
                </form>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>