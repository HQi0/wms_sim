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

// --- LOGIKA SIMPAN BARANG MASUK ---
if (isset($_POST['simpan_masuk'])) {
    $id_barang   = $_POST['id_barang'];
    $id_supplier = $_POST['id_supplier'];
    $jumlah      = (int) $_POST['jumlah'];
    $tanggal     = $_POST['tanggal'];
    $keterangan  = $_POST['keterangan'];

    if ($jumlah <= 0) {
        $error = "Jumlah barang harus lebih dari 0!";
    } else {
        // 1. Update Stok Barang
        $update_barang = mysqli_query($koneksi, "UPDATE barang SET stok = stok + $jumlah WHERE id='$id_barang'");

        // 2. Catat Transaksi
        // status='completed' karena barang masuk fisik langsung selesai
        $insert_transaksi = mysqli_query($koneksi, 
            "INSERT INTO transaksi (id_barang, id_user, id_supplier, jenis, jumlah, tanggal, keterangan, status) 
             VALUES ('$id_barang', '$id_user', '$id_supplier', 'masuk', '$jumlah', '$tanggal', '$keterangan', 'completed')"
        );

        if ($update_barang && $insert_transaksi) {
            $sukses = "Berhasil! Stok bertambah sebanyak $jumlah unit.";
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1 style="text-align: center; margin-bottom: 30px;">Input Barang Masuk</h1>

            <?php if($sukses): ?>
                <div class="alert-success"><?= $sukses ?></div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" action="">
                    
                    <div class="form-group">
                        <label>Tanggal Masuk</label>
                        <input type="datetime-local" name="tanggal" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Pilih Barang</label>
                        <select name="id_barang" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q_barang = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while($brg = mysqli_fetch_assoc($q_barang)){
                                echo "<option value='{$brg['id']}'>{$brg['nama_barang']} (Stok Saat Ini: {$brg['stok']} {$brg['satuan']})</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Supplier (Penyedia Barang)</label>
                        <select name="id_supplier" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php
                            $q_supplier = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
                            while($sup = mysqli_fetch_assoc($q_supplier)){
                                echo "<option value='{$sup['id']}'>{$sup['nama_supplier']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Jumlah Masuk</label>
                        <input type="number" name="jumlah" min="1" placeholder="Masukkan jumlah barang..." required>
                    </div>

                    <div class="form-group">
                        <label>Keterangan (No. Surat Jalan / Invoice)</label>
                        <textarea name="keterangan" rows="3" placeholder="Contoh: PO-001, Pembelian batch 2"></textarea>
                    </div>

                    <button type="submit" name="simpan_masuk" class="btn-success">Simpan Barang Masuk</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>