<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Hanya Admin yang boleh masuk
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$sukses = "";
$error = "";

// --- LOGIKA SIMPAN / UPDATE / HAPUS ---

// 1. Simpan Baru
if (isset($_POST['simpan'])) {
    $nama_lokasi = mysqli_real_escape_string($koneksi, $_POST['nama_lokasi']);
    
    // Cek duplikat
    $cek = mysqli_query($koneksi, "SELECT * FROM lokasi_rak WHERE nama_lokasi = '$nama_lokasi'");
    if(mysqli_num_rows($cek) > 0){
        $error = "Gagal: Nama Lokasi '$nama_lokasi' sudah ada!";
    } else {
        $simpan = mysqli_query($koneksi, "INSERT INTO lokasi_rak (nama_lokasi) VALUES ('$nama_lokasi')");
        if ($simpan) $sukses = "Lokasi baru berhasil ditambahkan.";
        else $error = "Gagal menyimpan: " . mysqli_error($koneksi);
    }
}

// 2. Update Data
if (isset($_POST['update'])) {
    $id = $_POST['id_lokasi'];
    $nama_lokasi = mysqli_real_escape_string($koneksi, $_POST['nama_lokasi']);
    
    $update = mysqli_query($koneksi, "UPDATE lokasi_rak SET nama_lokasi='$nama_lokasi' WHERE id='$id'");
    if ($update) {
        header("Location: master_lokasi.php"); 
        exit; 
    } else {
        $error = "Gagal update: " . mysqli_error($koneksi);
    }
}

// 3. Hapus Data
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    $hapus = mysqli_query($koneksi, "DELETE FROM lokasi_rak WHERE id='$id_hapus'");
    if ($hapus) {
        header("Location: master_lokasi.php");
        exit;
    }
    else $error = "Gagal menghapus: " . mysqli_error($koneksi);
}

// --- LOGIKA PERSIAPAN EDIT ---
$nama_edit = "";
$id_edit = "";
$aksi = "simpan"; 

if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $q_edit = mysqli_query($koneksi, "SELECT * FROM lokasi_rak WHERE id='$id_edit'");
    $data_edit = mysqli_fetch_assoc($q_edit);
    if ($data_edit) {
        $nama_edit = $data_edit['nama_lokasi'];
        $aksi = "update"; 
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Manajemen Lokasi Rak / Gudang</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah Lokasi Baru' : 'Edit Lokasi'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id_lokasi" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Nama Lokasi / Rak</label>
                            <input type="text" name="nama_lokasi" value="<?= $nama_edit ?>" placeholder="Contoh: Rak A-01, Gudang Besi" required autofocus>
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Simpan Data' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_lokasi.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Lokasi Tersedia</h3>
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Lokasi</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            $q_lok = mysqli_query($koneksi, "SELECT * FROM lokasi_rak ORDER BY nama_lokasi ASC");
                            if(mysqli_num_rows($q_lok) > 0){
                                while($row = mysqli_fetch_assoc($q_lok)){
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><b><?= $row['nama_lokasi'] ?></b></td>
                                <td>
                                    <a href="master_lokasi.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 12px; padding: 5px 10px;">Edit</a>
                                    <a href="master_lokasi.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 12px; padding: 5px 10px;" onclick="return confirm('Yakin hapus lokasi ini?')">Hapus</a>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='3' style='text-align:center; padding: 20px;'>Belum ada data lokasi.</td></tr>";
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