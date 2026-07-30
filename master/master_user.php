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
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $role = $_POST['role'];
    $password_raw = $_POST['password'];

    // SIMPAN BARU
    if (isset($_POST['simpan'])) {
        // Cek username kembar
        $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
        if(mysqli_num_rows($cek) > 0) {
            $error = "Gagal: Username '$username' sudah dipakai orang lain.";
        } else {
            // Hash password agar aman
            $pass_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $q = "INSERT INTO users (username, password, nama_lengkap, role) VALUES ('$username', '$pass_hash', '$nama', '$role')";
            
            if (mysqli_query($koneksi, $q)) {
                $sukses = "User baru berhasil dibuat.";
            } else {
                $error = "Gagal simpan: " . mysqli_error($koneksi);
            }
        }
    } 
    // UPDATE DATA
    else if (isset($_POST['update'])) {
        $id = $_POST['id'];
        
        // Cek apakah admin mengganti password user ini?
        if (!empty($password_raw)) {
            $pass_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $q = "UPDATE users SET nama_lengkap='$nama', role='$role', password='$pass_hash' WHERE id='$id'";
        } else {
            // Jika password kosong, jangan diupdate (pakai password lama)
            $q = "UPDATE users SET nama_lengkap='$nama', role='$role' WHERE id='$id'";
        }

        if (mysqli_query($koneksi, $q)) {
            header("Location: master_user.php");
            exit;
        } else {
            $error = "Gagal update: " . mysqli_error($koneksi);
        }
    }
}

// HAPUS DATA
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Cegah admin menghapus dirinya sendiri
    if ($id == $_SESSION['id_user']) {
        echo "<script>alert('Anda tidak bisa menghapus akun sendiri!'); window.location.href='master_user.php';</script>";
        exit;
    }

    if (mysqli_query($koneksi, "DELETE FROM users WHERE id='$id'")) {
        header("Location: master_user.php");
        exit;
    } else {
        $error = "Gagal hapus data.";
    }
}

// --- LOGIKA EDIT ---
$u_edit=""; $n_edit=""; $r_edit=""; $id_edit=""; 
$aksi="simpan";

if(isset($_GET['edit'])){
    $id_edit=$_GET['edit'];
    $d=mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id='$id_edit'"));
    if ($d) {
        $u_edit=$d['username']; 
        $n_edit=$d['nama_lengkap']; 
        $r_edit=$d['role']; 
        $aksi="update";
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Manajemen User & Hak Akses</h1>
            
            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3><?= ($aksi == 'simpan') ? 'Tambah User Baru' : 'Edit User'; ?></h3>
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?= $id_edit ?>">
                        
                        <div class="form-group">
                            <label>Username (Untuk Login)</label>
                            <input type="text" name="username" value="<?= $u_edit ?>" required placeholder="Tanpa spasi, contoh: andi" <?= ($aksi=='update') ? 'readonly style="background:#e9ecef"' : '' ?>>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= $n_edit ?>" required placeholder="Contoh: Andi Staff Gudang">
                        </div>
                        
                        <div class="form-group">
                            <label>Role / Jabatan</label>
                            <select name="role" required>
                                <option value="admin" <?= ($r_edit=='admin')?'selected':'' ?>>Admin (Full Akses)</option>
                                <option value="operator" <?= ($r_edit=='operator')?'selected':'' ?>>Operator (Gudang)</option>
                                <option value="requester" <?= ($r_edit=='requester')?'selected':'' ?>>Requester (Divisi)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Password <?= ($aksi=='update') ? '<small>(Kosongkan jika tidak ingin mengubah password)</small>' : '' ?></label>
                            <input type="password" name="password" <?= ($aksi=='simpan') ? 'required' : '' ?> placeholder="***">
                        </div>
                        
                        <button type="submit" name="<?= $aksi ?>" class="btn btn-primary">
                            <?= ($aksi == 'simpan') ? 'Buat User' : 'Simpan Perubahan'; ?>
                        </button>
                        
                        <?php if($aksi == 'update'): ?>
                            <a href="master_user.php" class="btn btn-danger">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Daftar Pengguna</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role ASC, username ASC");
                            if(mysqli_num_rows($q) > 0){
                                while($row = mysqli_fetch_assoc($q)){
                                    // Warna Badge Role
                                    if($row['role']=='admin') $badge = 'badge-admin';
                                    elseif($row['role']=='operator') $badge = 'badge-operator';
                                    else $badge = 'badge-requester';
                            ?>
                            <tr>
                                <td><b><?= $row['username'] ?></b></td>
                                <td><?= $row['nama_lengkap'] ?></td>
                                <td><span class="badge <?= $badge ?>"><?= strtoupper($row['role']) ?></span></td>
                                <td>
                                    <a href="master_user.php?edit=<?= $row['id'] ?>" class="btn btn-warning" style="font-size: 11px; padding: 5px 8px;">Edit</a>
                                    <?php if($row['id'] != $_SESSION['id_user']): ?>
                                        <a href="master_user.php?hapus=<?= $row['id'] ?>" class="btn btn-danger" style="font-size: 11px; padding: 5px 8px;" onclick="return confirm('Yakin hapus user ini? Login mereka akan hangus.')">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; padding:20px;'>Belum ada user.</td></tr>";
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