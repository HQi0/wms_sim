<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Hanya Admin & Operator
if (!isset($_SESSION['status']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator')) {
    header("Location: ../index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$id_user   = $_SESSION['id_user'];
$sukses    = "";
$error     = "";

// --- LOGIKA SIMPAN BARANG KELUAR ---
if (isset($_POST['simpan_keluar'])) {
    $id_barang = $_POST['id_barang'];
    $id_divisi = $_POST['id_divisi']; 
    $jumlah    = (int) $_POST['jumlah'];
    $tanggal   = $_POST['tanggal'];
    $keterangan= $_POST['keterangan'];

    if ($jumlah <= 0) {
        $error = "Jumlah barang harus lebih dari 0!";
    } else {
        // 1. Cek Stok Cukup?
        $cek_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE id='$id_barang'");
        $data_stok = mysqli_fetch_assoc($cek_stok);
        $stok_tersedia = $data_stok['stok'];

        if ($jumlah > $stok_tersedia) {
            $error = "Gagal: Stok tidak cukup! Tersedia hanya $stok_tersedia unit.";
        } else {
            // 2. Kurangi Stok
            $update_barang = mysqli_query($koneksi, "UPDATE barang SET stok = stok - $jumlah WHERE id='$id_barang'");

            // 3. Catat Transaksi (Jenis: keluar, Status: completed)
            $insert_transaksi = mysqli_query($koneksi, 
                "INSERT INTO transaksi (id_barang, id_user, id_divisi, jenis, jumlah, tanggal, keterangan, status) 
                 VALUES ('$id_barang', '$id_user', '$id_divisi', 'keluar', '$jumlah', '$tanggal', '$keterangan', 'completed')"
            );

            if ($update_barang && $insert_transaksi) {
                $sukses = "Berhasil! Stok berkurang sebanyak $jumlah unit.";
            } else {
                $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1 style="text-align: center; margin-bottom: 30px;">Input Barang Keluar (Manual)</h1>

            <?php if($sukses): ?>
                <div class="alert-success"><?= $sukses ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="">
                    
                    <div class="form-group">
                        <label>Tanggal Keluar</label>
                        <input type="datetime-local" name="tanggal" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Pilih Barang</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while($brg = mysqli_fetch_assoc($q_barang)){
                                echo "<option value='{$brg['id']}'>{$brg['nama_barang']} (Sisa Stok: {$brg['stok']} {$brg['satuan']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Divisi Pemohon / Tujuan</label>
                        <select name="id_divisi" required>
                            <option value="">-- Pilih Divisi --</option>
                            <?php
                            $q_divisi = mysqli_query($koneksi, "SELECT * FROM divisi ORDER BY nama_divisi ASC");
                            while($div = mysqli_fetch_assoc($q_divisi)){
                                echo "<option value='{$div['id']}'>{$div['nama_divisi']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Keluar</label>
                        <input type="number" name="jumlah" min="1" placeholder="Masukkan jumlah barang..." required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: Penggunaan darurat, Barang rusak dibuang..."></textarea>
                    </div>

                    <button type="submit" name="simpan_keluar" class="btn-warning">Proses Barang Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>