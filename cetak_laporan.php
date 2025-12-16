<?php
session_start();
include 'koneksi.php';

// CEK AKSES
if (!isset($_SESSION['status']) || $_SESSION['role'] != 'admin') {
    die("Akses Ditolak.");
}

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$bulan_indo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$nama_bulan = $bulan_indo[$bulan];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan_<?= $nama_bulan ?>_<?= $tahun ?></title>
    <style>
        /* CSS KHUSUS CETAK PDF */
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; padding: 20px; }
        
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 5px 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #ddd; text-align: center; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        /* Tanda Tangan */
        .ttd-box { width: 100%; margin-top: 40px; display: flex; justify-content: flex-end; }
        .ttd { width: 200px; text-align: center; float: right;}
        
        /* Sembunyikan elemen saat dicetak (opsional) */
        @media print {
            @page { size: A4; margin: 20mm; }
        }
    </style>
</head>

<body onload="window.print()">

    <div class="header">
        <h1>LAPORAN MUTASI BARANG GUDANG</h1>
        <p>PT JAYA MANUFAKTUR</p>
        <p>Periode: <b><?= $nama_bulan ?> <?= $tahun ?></b></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">Kode</th>
                <th>Nama Barang</th>
                <th width="10%">Jenis</th>
                <th width="10%">Masuk</th>
                <th width="10%">Keluar</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query yang sama dengan halaman laporan
            $query = "SELECT t.*, b.kode_barang, b.nama_barang, s.nama_supplier, d.nama_divisi 
                      FROM transaksi t
                      JOIN barang b ON t.id_barang = b.id
                      LEFT JOIN supplier s ON t.id_supplier = s.id
                      LEFT JOIN divisi d ON t.id_divisi = d.id
                      WHERE MONTH(t.tanggal) = '$bulan' AND YEAR(t.tanggal) = '$tahun'
                      AND t.status != 'pending' AND t.status != 'rejected'
                      ORDER BY t.tanggal ASC";
            
            $result = mysqli_query($koneksi, $query);
            $no = 1;
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $tanggal = date('d/m/Y', strtotime($row['tanggal']));
                    
                    $masuk = "-"; $keluar = "-"; $ket = ""; $jenis = strtoupper($row['jenis']);

                    if($row['jenis'] == 'masuk') {
                        $masuk = $row['jumlah'];
                        $ket = $row['nama_supplier'];
                    } else if ($row['jenis'] == 'keluar') {
                        $keluar = $row['jumlah'];
                        $ket = $row['nama_divisi'];
                    } else {
                        // Adjustment
                        $ket = "Stock Opname";
                        if(strpos($row['keterangan'], "Lebih") !== false) $masuk = $row['jumlah'];
                        else $keluar = $row['jumlah'];
                    }
            ?>
            <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><?= $tanggal ?></td>
                <td><?= $row['kode_barang'] ?></td>
                <td><?= $row['nama_barang'] ?></td>
                <td class="text-center"><?= $jenis ?></td>
                <td class="text-center"><?= $masuk ?></td>
                <td class="text-center"><?= $keluar ?></td>
                <td>
                    <?= $ket ?><br>
                    <span style="font-size:10px; font-style:italic;"><?= $row['keterangan'] ?></span>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='8' class='text-center'>Tidak ada data transaksi.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="ttd-box">
        <div class="ttd">
            <p>Jakarta, <?= date('d') . ' ' . $bulan_indo[date('m')] . ' ' . date('Y') ?></p>
            <p>Kepala Gudang,</p>
            <br><br><br>
            <p><b>( <?= $_SESSION['nama_lengkap'] ?> )</b></p>
        </div>
    </div>

</body>
</html>