<?php
session_start();
include '../config/koneksi.php';

// CEK AKSES: Admin & Operator BOLEH MASUK. Requester DILARANG.
if (!isset($_SESSION['status']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'operator')) {
    header("Location: ../index.php");
    exit;
}

$role_user = $_SESSION['role'];
$nama_user = $_SESSION['nama_lengkap'];
$id_user   = $_SESSION['id_user'];

$sukses = "";
$error = "";

// --- LOGIKA PROSES OPNAME ---
if (isset($_POST['simpan_opname'])) {
    $id_barang = $_POST['id_barang'];
    $stok_fisik = (int) $_POST['stok_fisik'];
    $catatan = $_POST['keterangan'];

    // 1. Ambil Stok Sistem Terkini
    $cek_brg = mysqli_query($koneksi, "SELECT stok, nama_barang FROM barang WHERE id='$id_barang'");
    $data_brg = mysqli_fetch_assoc($cek_brg);
    
    if ($data_brg) {
        $stok_sistem = $data_brg['stok'];
        $selisih = $stok_fisik - $stok_sistem;
        $tgl_sekarang = date('Y-m-d H:i:s');

        // Jika tidak ada selisih, beri info
        if ($selisih == 0) {
            $error = "Stok Fisik sama dengan Sistem. Tidak ada perubahan yang disimpan.";
        } else {
            // Tentukan keterangan otomatis
            if ($selisih > 0) {
                $detail_selisih = "(Lebih +$selisih)";
            } else {
                $detail_selisih = "(Hilang/Kurang $selisih)";
            }
            $ket_lengkap = "Stock Opname: System $stok_sistem -> Fisik $stok_fisik. $detail_selisih. Ket: $catatan";

            // 2. Update Stok Master Barang
            $update_master = mysqli_query($koneksi, "UPDATE barang SET stok='$stok_fisik' WHERE id='$id_barang'");

            // 3. Catat Riwayat Transaksi (Jenis: adjustment)
            // Jumlah di transaksi kita simpan angka positif (absolute) untuk history
            $jml_transaksi = abs($selisih); 
            
            $insert_log = mysqli_query($koneksi, "INSERT INTO transaksi 
                (id_barang, id_user, jenis, jumlah, tanggal, keterangan, status, stok_awal_sistem) 
                VALUES 
                ('$id_barang', '$id_user', 'adjustment', '$jml_transaksi', '$tgl_sekarang', '$ket_lengkap', 'completed', '$stok_sistem')");

            if ($update_master && $insert_log) {
                $sukses = "Berhasil! Stok barang <b>{$data_brg['nama_barang']}</b> kini disesuaikan menjadi <b>$stok_fisik</b>.";
            } else {
                $error = "Terjadi kesalahan database: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Stock Opname (Penyesuaian Stok)</h1>
            <p>Gunakan fitur ini jika stok fisik di gudang berbeda dengan stok di sistem.</p>

            <?php if($sukses) echo "<div class='alert-success'>$sukses</div>"; ?>
            <?php if($error) echo "<div class='alert-danger'>$error</div>"; ?>

            <div style="display: flex; gap: 20px; align-items: flex-start;">
                
                <div class="card" style="flex: 1;">
                    <h3>Input Hasil Opname</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Pilih Barang</label>
                            <select name="id_barang" required style="font-size: 14px;">
                                <option value="">-- Cari Barang --</option>
                                <?php
                                $q = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                                while($row = mysqli_fetch_assoc($q)){
                                    // Tampilkan stok sistem di dropdown agar user tahu
                                    echo "<option value='{$row['id']}'>{$row['nama_barang']} (Sistem: {$row['stok']})</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Stok Fisik (Hasil Hitung)</label>
                            <input type="number" name="stok_fisik" min="0" required placeholder="Masukkan jumlah real di rak...">
                        </div>

                        <div class="form-group">
                            <label>Keterangan / Alasan</label>
                            <textarea name="keterangan" rows="3" required placeholder="Contoh: Barang rusak air, Barang terselip, Salah hitung sebelumnya..."></textarea>
                        </div>

                        <button type="submit" name="simpan_opname" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin data fisik sudah benar? Stok akan langsung berubah.')">Simpan Penyesuaian</button>
                    </form>
                </div>

                <div class="card" style="flex: 2;">
                    <h3>Riwayat Stock Opname</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Sistem</th>
                                <th>Fisik</th>
                                <th>Selisih</th>
                                <th>Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Ambil 10 transaksi terakhir yang jenisnya 'adjustment'
                            $q_log = mysqli_query($koneksi, "
                                SELECT t.*, b.nama_barang, u.username 
                                FROM transaksi t 
                                JOIN barang b ON t.id_barang = b.id
                                JOIN users u ON t.id_user = u.id
                                WHERE t.jenis = 'adjustment'
                                ORDER BY t.tanggal DESC LIMIT 10
                            ");
                            
                            if(mysqli_num_rows($q_log) > 0){
                                while($log = mysqli_fetch_assoc($q_log)){
                                    // Hitung fisik berdasarkan snapshot stok awal + selisih
                                    // Namun cara paling mudah membaca 'keterangan' atau menghitung ulang
                                    // Disini kita lakukan logika sederhana:
                                    // Jika adjustment, 'jumlah' adalah nilai absolut selisih.
                                    // Kita parse dari keterangan saja agar akurat jika ada +/-
                                    
                                    // Logika Visual Selisih
                                    // Kita cek manual sederhana:
                                    // Jika keterangan mengandung "Hilang/Kurang", berarti minus
                                    $is_loss = strpos($log['keterangan'], "Hilang") !== false || strpos($log['keterangan'], "Kurang") !== false;
                                    
                                    if ($is_loss) {
                                        $selisih_text = "<span class='badge-loss'>-{$log['jumlah']}</span>";
                                        $fisik = $log['stok_awal_sistem'] - $log['jumlah'];
                                    } else {
                                        $selisih_text = "<span class='badge-plus'>+{$log['jumlah']}</span>";
                                        $fisik = $log['stok_awal_sistem'] + $log['jumlah'];
                                    }
                            ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($log['tanggal'])) ?></td>
                                <td><?= $log['nama_barang'] ?></td>
                                <td><?= $log['stok_awal_sistem'] ?></td>
                                <td><b><?= $fisik ?></b></td>
                                <td><?= $selisih_text ?></td>
                                <td><?= ucfirst($log['username']) ?></td>
                            </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Belum ada data opname.</td></tr>";
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