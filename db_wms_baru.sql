-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 09:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_wms_baru`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` enum('Bahan Baku','Barang Jadi','Sparepart') NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `stok_minimal` int(11) NOT NULL DEFAULT 10,
  `satuan` varchar(20) NOT NULL,
  `id_lokasi` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id`, `kode_barang`, `nama_barang`, `kategori`, `stok`, `stok_minimal`, `satuan`, `id_lokasi`) VALUES
(1, 'BRG001', 'Plat Besi 5mm', 'Bahan Baku', 50, 10, 'Lembar', 1),
(2, 'BRG002', 'Baut Baja M10', 'Sparepart', 405, 51, 'Pcs', 2),
(3, 'PROD01', 'kkkkkk', 'Barang Jadi', 105, 22, 'Unit', 3),
(4, 'adas', 'asdadsa', 'Bahan Baku', 51, 1, 'pcs', 5),
(5, '213123124afq w', 'qeqeq', 'Bahan Baku', 400, 50, 'unit', 5),
(6, 'sadasd', 'asdad', 'Bahan Baku', 100, 50, 'unit', 3);

-- --------------------------------------------------------

--
-- Table structure for table `divisi`
--

CREATE TABLE `divisi` (
  `id` int(11) NOT NULL,
  `nama_divisi` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `divisi`
--

INSERT INTO `divisi` (`id`, `nama_divisi`) VALUES
(1, 'Produksi'),
(2, 'Gudang'),
(3, 'Pemasaranadasd'),
(4, 'asfsfasf'),
(5, 'asdasd');

-- --------------------------------------------------------

--
-- Table structure for table `lokasi_rak`
--

CREATE TABLE `lokasi_rak` (
  `id` int(11) NOT NULL,
  `nama_lokasi` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lokasi_rak`
--

INSERT INTO `lokasi_rak` (`id`, `nama_lokasi`) VALUES
(1, 'saasRak A-01 (Bahan Baku)'),
(2, 'Rak B-01 (Sparepart)'),
(3, 'Rak C-01 (Barang Jadi)'),
(4, 'araekssssaas'),
(5, 'asjdnjadasa');

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `kontak` varchar(50) NOT NULL,
  `alamat` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `nama_supplier`, `kontak`, `alamat`) VALUES
(1, 'PT Logam Jaya', '08123456789', 'Jl. Industri No. 1, Jakarta'),
(2, 'CV Plastik Abadiaaasa', '08987654321', 'Jl. Raya Bogor KM 5, Jakarta'),
(3, 's', 's', 's'),
(4, 'asdasd', '211', 'asas');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `id_divisi` int(11) DEFAULT NULL,
  `jenis` enum('masuk','keluar','adjustment') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'completed',
  `stok_awal_sistem` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id`, `id_barang`, `id_user`, `id_supplier`, `id_divisi`, `jenis`, `jumlah`, `tanggal`, `keterangan`, `status`, `stok_awal_sistem`) VALUES
(1, 1, 1, 1, NULL, 'masuk', 50, '2025-01-01 09:00:00', 'Restock Awal', 'completed', NULL),
(2, 2, 2, NULL, NULL, 'keluar', 5, '2025-01-02 10:00:00', 'Dipakai Produksi', 'completed', NULL),
(3, 2, 1, NULL, NULL, 'adjustment', 132, '2025-12-15 10:34:29', 'Stock Opname: System 200 -> Fisik 332. (Lebih +132). Ket: as', 'completed', 200),
(4, 2, 1, NULL, 2, 'keluar', 12, '2025-12-15 10:39:52', '', 'approved', NULL),
(5, 2, 1, NULL, 5, 'keluar', 22, '2025-12-15 10:41:21', '', 'approved', NULL),
(6, 2, 1, NULL, 2, 'keluar', 11, '2025-12-15 10:42:24', '', 'approved', NULL),
(7, 3, 1, NULL, 4, 'keluar', 1, '2025-12-15 10:42:26', '', 'rejected', NULL),
(8, 2, 1, NULL, 5, 'keluar', 1, '2025-12-16 03:21:45', 'a', 'approved', NULL),
(9, 4, 1, NULL, 5, 'keluar', 1, '2025-12-20 05:48:00', '12', 'completed', NULL),
(10, 2, 3, NULL, 4, 'keluar', 12, '2025-12-24 05:24:27', '', 'approved', NULL),
(11, 2, 2, NULL, NULL, 'adjustment', 126, '2025-12-24 05:31:30', 'Stock Opname: System 274 -> Fisik 400. (Lebih +126). Ket: qe', 'completed', 274),
(12, 4, 2, 2, NULL, 'masuk', 21, '2025-12-24 05:31:00', '', 'completed', NULL),
(13, 2, 3, NULL, 3, 'keluar', 100, '2025-12-24 05:56:19', '', 'approved', NULL),
(14, 3, 1, 2, NULL, 'masuk', 100, '2025-12-24 05:56:00', '', 'completed', NULL),
(15, 2, 1, NULL, NULL, 'adjustment', 105, '2025-12-24 05:58:48', 'Stock Opname: System 300 -> Fisik 405. (Lebih +105). Ket: adasd', 'completed', 300),
(16, 4, 3, NULL, 2, 'keluar', 21, '2025-12-24 06:05:39', '', 'approved', NULL),
(17, 4, 1, NULL, NULL, 'adjustment', 1, '2025-12-24 06:06:50', 'Stock Opname: System 0 -> Fisik 1. (Lebih +1). Ket: ae', 'completed', 0),
(18, 4, 1, 1, NULL, 'masuk', 50, '2025-12-24 06:08:00', '', 'completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','operator','requester') NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `created_at`) VALUES
(1, 'admin', '$2y$10$74wtKgs4q4HqAVYLtD4m1.3CabtpSwQ2A76oOF3Z5s7PGuifLn31m', 'admin', 'Administrator Gudang', '2025-12-15 07:46:26'),
(2, 'operator', '$2y$10$74wtKgs4q4HqAVYLtD4m1.3CabtpSwQ2A76oOF3Z5s7PGuifLn31m', 'operator', 'Budi (Staff Gudang)', '2025-12-15 07:46:26'),
(3, 'requester', '$2y$10$74wtKgs4q4HqAVYLtD4m1.3CabtpSwQ2A76oOF3Z5s7PGuifLn31m', 'requester', 'Andi (Kepala Produksi)', '2025-12-15 07:46:26'),
(4, 'tesuser', '$2y$10$dYGD0Q82592HbkPIuyeTM.7hRg5UJLCaZhdtQrllbS0KlOfuqiD9q', 'operator', 'adasd', '2025-12-15 09:31:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `id_lokasi` (`id_lokasi`);

--
-- Indexes for table `divisi`
--
ALTER TABLE `divisi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lokasi_rak`
--
ALTER TABLE `lokasi_rak`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `id_divisi` (`id_divisi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `divisi`
--
ALTER TABLE `divisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lokasi_rak`
--
ALTER TABLE `lokasi_rak`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `barang_ibfk_1` FOREIGN KEY (`id_lokasi`) REFERENCES `lokasi_rak` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_3` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_4` FOREIGN KEY (`id_divisi`) REFERENCES `divisi` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
