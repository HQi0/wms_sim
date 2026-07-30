<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Hanya Admin
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$error = ""; 
$sukses = "";

// --- LOGIKA SIMPAN / UPDATE / HAPUS ---

if (isset($_POST['simpan']) || isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_divisi']);

    // SIMPAN BARU
    if (isset($_POST['simpan'])) {
        $q = "INSERT INTO divisi (nama_divisi) VALUES ('$nama')";
        if (mysqli_query($koneksi, $q)) {
            $sukses = "Divisi berhasil ditambahkan.";
        } else {
            $error = "Gagal: " . mysqli_error($koneksi);
        }
    } 
    // UPDATE DATA
    else if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $q = "UPDATE divisi SET nama_divisi='$nama' WHERE id='$id'";
        if (mysqli_query($koneksi, $q)) {
            header("Location: master_divisi.php");
            exit;
        } else {
            $error = "Gagal update: " . mysqli_error($koneksi);
        }
    }
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (mysqli_query($koneksi, "DELETE FROM divisi WHERE id='$id'")) {
        header("Location: master_divisi.php");
        exit;
    } else {
        $error = "Gagal hapus! Data ini mungkin sedang digunakan di riwayat transaksi.";
    }
}

// --- LOGIKA EDIT ---
$nama_edit = ""; $id_edit = ""; 
$aksi = "simpan";

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM divisi WHERE id='$id_edit'"));
    if($r) {
        $nama_edit = $r['nama_divisi']; 
        $aksi = "update";
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Data Divisi (Departemen)</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah Divisi' : 'Edit Divisi'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Nama Divisi</label>
                            <input type="text" name="nama_divisi" value="<?= $nama_edit ?>" required placeholder="Contoh: Produksi, HRD, Gudang">
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Simpan Data' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_divisi.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Divisi</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama Divisi</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($koneksi, "SELECT * FROM divisi ORDER BY nama_divisi ASC");
                            if(mysqli_num_rows($q) > 0){
                                while($row = mysqli_fetch_assoc($q)){
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><b><?= $row['nama_divisi'] ?></b></td>
                                <td>
                                    <a href="master_divisi.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 11px; padding: 5px 8px;">Edit</a>
                                    <a href="master_divisi.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 8px;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; padding:20px;'>Belum ada data divisi.</td></tr>";
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