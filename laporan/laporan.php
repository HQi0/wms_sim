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

// Ambil filter, default bulan & tahun sekarang
$bulan_pilih = isset($_POST['bulan']) ? $_POST['bulan'] : date('m');
$tahun_pilih = isset($_POST['tahun']) ? $_POST['tahun'] : date('Y');
?>

<?php include '../includes/header.php'; ?>
    <div class="wrapper">
        <?php include '../includes/sidebar.php'; ?>

        <div class="content">
            <h1>Laporan Mutasi Barang</h1>

            <div class="filter-box">
                <form method="POST" action="" style="display: flex; gap: 10px; width: 100%; align-items: center;">
                    <select name="bulan">
                        <?php
                        $bulan_arr = [
                            '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April',
                            '05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
                            '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
                        ];
                        foreach($bulan_arr as $k => $v){
                            $sel = ($bulan_pilih == $k) ? 'selected' : '';
                            echo "<option value='$k' $sel>$v</option>";
                        }
                        ?>
                    </select>
                    
                    <select name="tahun">
                        <?php
                        for($i=2024; $i<=date('Y'); $i++){
                            $sel = ($tahun_pilih == $i) ? 'selected' : '';
                            echo "<option value='$i' $sel>$i</option>";
                        }
                        ?>
                    </select>

                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                    
                    <a href="cetak_laporan.php?bulan=<?= $bulan_pilih ?>&tahun=<?= $tahun_pilih ?>" target="_blank" class="btn btn-pdf">
                        <i data-lucide="file-down" width="16"></i> Download PDF
                    </a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Tanggal</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Jenis</th>
                        <th>Jml</th>
                        <th>Keterangan / Partner</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Query mengambil semua transaksi (Masuk, Keluar, Adjustment) yang statusnya COMPLETED/APPROVED
                    $query = "SELECT t.*, b.kode_barang, b.nama_barang, s.nama_supplier, d.nama_divisi 
                              FROM transaksi t
                              JOIN barang b ON t.id_barang = b.id
                              LEFT JOIN supplier s ON t.id_supplier = s.id
                              LEFT JOIN divisi d ON t.id_divisi = d.id
                              WHERE MONTH(t.tanggal) = '$bulan_pilih' AND YEAR(t.tanggal) = '$tahun_pilih' 
                              AND t.status != 'pending' AND t.status != 'rejected'
                              ORDER BY t.tanggal ASC";
                    
                    $result = mysqli_query($koneksi, $query);
                    $no = 1;
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $tgl = date('d/m/Y H:i', strtotime($row['tanggal']));
                            
                            $jenis_label = "";
                            $ket_detail = "";

                            if($row['jenis'] == 'masuk'){
                                $jenis_label = "<span class='badge bg-in'>Masuk</span>";
                                $ket_detail = "Supplier: " . ($row['nama_supplier'] ?? '-');
                            } else if ($row['jenis'] == 'keluar') {
                                $jenis_label = "<span class='badge bg-out'>Keluar</span>";
                                $ket_detail = "Divisi: " . ($row['nama_divisi'] ?? '-');
                            } else {
                                $jenis_label = "<span class='badge bg-adj'>Opname</span>";
                                $ket_detail = "Penyesuaian Stok";
                            }
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $tgl ?></td>
                        <td><?= $row['kode_barang'] ?></td>
                        <td><?= $row['nama_barang'] ?></td>
                        <td><?= $jenis_label ?></td>
                        <td><b><?= $row['jumlah'] ?></b></td>
                        <td>
                            <?= $ket_detail ?><br>
                            <small style="color: #666;"><?= $row['keterangan'] ?></small>
                        </td>
                    </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 20px;'>Tidak ada data transaksi pada periode ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>

</body>
</html>