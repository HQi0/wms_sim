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
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $kontak = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // SIMPAN BARU
    if (isset($_POST['simpan'])) {
        $q = "INSERT INTO supplier (nama_supplier, kontak, alamat) VALUES ('$nama', '$kontak', '$alamat')";
        if (mysqli_query($koneksi, $q)) {
            $sukses = "Supplier berhasil ditambahkan.";
        } else {
            $error = "Gagal: " . mysqli_error($koneksi);
        }
    } 
    // UPDATE DATA
    else if (isset($_POST['update'])) {
        $id = $_POST['id'];
        $q = "UPDATE supplier SET nama_supplier='$nama', kontak='$kontak', alamat='$alamat' WHERE id='$id'";
        if (mysqli_query($koneksi, $q)) {
            header("Location: master_supplier.php");
            exit;
        } else {
            $error = "Gagal update: " . mysqli_error($koneksi);
        }
    }
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (mysqli_query($koneksi, "DELETE FROM supplier WHERE id='$id'")) {
        header("Location: master_supplier.php");
        exit;
    } else {
        $error = "Gagal hapus! Data ini mungkin sedang digunakan di riwayat transaksi.";
    }
}

// --- LOGIKA EDIT ---
$nama_edit = ""; $kontak_edit = ""; $alamat_edit = ""; $id_edit = ""; 
$aksi = "simpan";

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM supplier WHERE id='$id_edit'"));
    if($r) {
        $nama_edit = $r['nama_supplier']; 
        $kontak_edit = $r['kontak']; 
        $alamat_edit = $r['alamat']; 
        $aksi = "update";
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Data Supplier</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah Supplier' : 'Edit Supplier'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Nama Supplier</label>
                            <input type="text" name="nama_supplier" value="<?= $nama_edit ?>" required placeholder="Contoh: PT Logam Jaya">
                        </div>
                        
                        <div class="form-group">
                            <label>Kontak (HP / Telepon)</label>
                            <input type="text" name="kontak" value="<?= $kontak_edit ?>" required placeholder="0812...">
                        </div>

                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="alamat" rows="3" required placeholder="Alamat kantor supplier..."><?= $alamat_edit ?></textarea>
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Simpan Data' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_supplier.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Supplier</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th>Nama Supplier</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q = mysqli_query($koneksi, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
                            if(mysqli_num_rows($q) > 0){
                                while($row = mysqli_fetch_assoc($q)){
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><b><?= $row['nama_supplier'] ?></b></td>
                                <td><?= $row['kontak'] ?></td>
                                <td><?= $row['alamat'] ?></td>
                                <td>
                                    <a href="master_supplier.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 11px; padding: 5px 8px;">Edit</a>
                                    <a href="master_supplier.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 8px;" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Belum ada data supplier.</td></tr>";
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