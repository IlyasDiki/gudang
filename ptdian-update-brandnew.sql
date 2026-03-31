-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 03:46 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ptdian`
--

-- --------------------------------------------------------

--
-- Table structure for table `at_detail`
--

CREATE TABLE `at_detail` (
  `id_at` int(11) NOT NULL,
  `id_mutasi` int(11) DEFAULT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `sortir` decimal(10,2) DEFAULT NULL,
  `ma` decimal(10,2) DEFAULT NULL,
  `aa` decimal(10,2) DEFAULT NULL,
  `b_mentah` decimal(10,2) DEFAULT NULL,
  `air` decimal(10,2) DEFAULT NULL,
  `atp` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `at_detail`
--

INSERT INTO `at_detail` (`id_at`, `id_mutasi`, `id_barang`, `sortir`, `ma`, `aa`, `b_mentah`, `air`, `atp`) VALUES
(2, 7, 2, '2.00', '2.00', '555.00', '55.00', '55.00', '55.00'),
(3, 8, 2, '55.00', '55.00', '55.00', '55.00', '55.00', '55.00');

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `id_kelompok` int(11) NOT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `stok_minimum` int(11) DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `id_kelompok`, `satuan`, `aktif`, `stok_minimum`) VALUES
(1, 'a1', 'Fadli - Polman', 2, 'Kg', 1, 50),
(2, 'b1', 'Fadli-Polman', 3, 'Kg', 1, 50),
(3, 'a2', 'Indah - Galur', 2, 'Kg', 1, 0),
(4, 'b2', 'Indah - Galur', 3, 'Kg', 1, 0),
(5, 'c1', 'Plastik C28', 4, 'Kg', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `jenis_barang`
--

CREATE TABLE `jenis_barang` (
  `id_jbarang` int(11) NOT NULL,
  `jenis_barang` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_barang`
--

INSERT INTO `jenis_barang` (`id_jbarang`, `jenis_barang`) VALUES
(1, 'Bahan Baku'),
(2, 'Pendukung'),
(3, 'Packaging');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_mutasi`
--

CREATE TABLE `jenis_mutasi` (
  `id_jenis` int(11) NOT NULL,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `tipe` enum('TRANSAKSI','AT','PRODUKSI','KOREKSI') NOT NULL,
  `arah` enum('MASUK','KELUAR') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_mutasi`
--

INSERT INTO `jenis_mutasi` (`id_jenis`, `kode_jenis`, `nama_jenis`, `tipe`, `arah`) VALUES
(1, 'TRM', 'Transaksi Barang Masuk', 'TRANSAKSI', 'MASUK'),
(2, 'AT', 'Pemakaian AT', 'AT', 'KELUAR'),
(3, 'PRODM', 'Pemakaian Produksi', 'PRODUKSI', NULL),
(4, 'KS', 'Koreksi Stok', 'KOREKSI', NULL),
(5, 'STOKAWAL', 'Stok Awal', 'KOREKSI', NULL),
(6, 'TRK', 'Transaksi Barang Keluar', 'TRANSAKSI', 'KELUAR');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_transaksi`
--

CREATE TABLE `jenis_transaksi` (
  `id_jenist` int(11) NOT NULL,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `arah` enum('MASUK','KELUAR') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_transaksi`
--

INSERT INTO `jenis_transaksi` (`id_jenist`, `kode_jenis`, `nama_jenis`, `aktif`, `arah`) VALUES
(1, 'MASUK', 'Barang Masuk', 1, 'MASUK'),
(2, 'KELUAR', 'Barang Keluar', 1, 'KELUAR');

-- --------------------------------------------------------

--
-- Table structure for table `kelompok_barang`
--

CREATE TABLE `kelompok_barang` (
  `id_kelompok` int(11) NOT NULL,
  `kode_kelompok` varchar(20) NOT NULL,
  `nama_kelompok` varchar(100) NOT NULL,
  `tipe_kelompok` enum('RAW_MATERIAL','LOGISTIK','PRODUK_JADI') NOT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `kelompok_barang`
--

INSERT INTO `kelompok_barang` (`id_kelompok`, `kode_kelompok`, `nama_kelompok`, `tipe_kelompok`, `parent_id`) VALUES
(2, 'a1', 'Arang Tempurung Kelapa', 'PRODUK_JADI', NULL),
(3, 'a2', 'Powder', 'RAW_MATERIAL', NULL),
(4, 'c2', 'Bahan Packing', 'LOGISTIK', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mutasi`
--

CREATE TABLE `mutasi` (
  `id_mutasi` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp(),
  `arah` enum('MASUK','KELUAR') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mutasi`
--

INSERT INTO `mutasi` (`id_mutasi`, `id_transaksi`, `tanggal`, `id_jenis`, `keterangan`, `dibuat_oleh`, `dibuat_pada`, `arah`) VALUES
(1, NULL, '2026-02-04', 1, 'Transaksi Barang', 1, '2026-02-04 10:43:34', 'KELUAR'),
(3, NULL, '2026-01-23', 1, 'Pembelian Barang', 1, '2026-01-23 11:01:42', NULL),
(4, NULL, '2026-01-24', 2, NULL, 1, '2026-01-24 10:39:49', NULL),
(5, NULL, '2026-01-26', 2, 'Pemakaian AT', 1, '2026-01-26 10:25:07', NULL),
(6, NULL, '2026-01-26', 5, 'Stok Awal', 1, '2026-01-26 15:41:04', NULL),
(7, NULL, '2026-01-27', 2, 'Pemakaian AT', 1, '2026-01-27 11:58:15', NULL),
(8, NULL, '2026-01-27', 2, 'Pemakaian AT', 1, '2026-01-27 15:53:22', NULL),
(9, NULL, '2026-01-31', 1, 'transaksi Barang', 1, '2026-01-31 10:07:50', 'MASUK'),
(10, NULL, '2026-01-31', 1, 'transaksi Barang', 1, '2026-01-31 15:04:51', 'KELUAR'),
(11, NULL, '2026-02-02', 1, 'transaksi Barang', 1, '2026-02-02 10:07:47', 'MASUK'),
(12, NULL, '2026-02-02', 1, 'transaksi Barang', 1, '2026-02-02 10:37:39', 'MASUK'),
(13, NULL, '2026-02-02', 1, 'transaksi Barang', 1, '2026-02-02 15:02:07', NULL),
(15, 13, '2026-02-04', 1, 'Transaksi Barang', 1, '2026-02-04 13:51:19', 'KELUAR');

-- --------------------------------------------------------

--
-- Table structure for table `mutasi_detail`
--

CREATE TABLE `mutasi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_mutasi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `jenis_pemakaian` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mutasi_detail`
--

INSERT INTO `mutasi_detail` (`id_detail`, `id_mutasi`, `id_barang`, `jumlah`, `jenis_pemakaian`) VALUES
(1, 3, 1, '222.00', NULL),
(8, 6, 2, '1000.00', NULL),
(9, 9, 2, '255.00', NULL),
(10, 10, 1, '22.00', NULL),
(11, 11, 1, '200.00', NULL),
(12, 12, 2, '200.00', NULL),
(13, 13, 1, '231.00', NULL),
(14, 15, 5, '100.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pemakaian_produksi`
--

CREATE TABLE `pemakaian_produksi` (
  `id_pemakaian` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `produksi_detail`
--

CREATE TABLE `produksi_detail` (
  `id_produksi` int(11) NOT NULL,
  `id_mutasi` int(11) NOT NULL,
  `mixer` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `telepon`) VALUES
(1, 'Fadli', 'Polman', '08238827384'),
(2, 'Indah', 'Galur', '08828282828');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal_terima` date NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `jenis_barang` varchar(30) DEFAULT NULL,
  `jenis_transaksi` int(11) NOT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_terima`, `id_supplier`, `jenis_barang`, `jenis_transaksi`, `dibuat_oleh`, `dibuat_pada`) VALUES
(1, '2026-02-04', NULL, '1', 2, 1, '2026-02-04 10:24:40'),
(8, '2026-01-31', NULL, '3', 2, 1, '2026-01-31 08:57:01'),
(9, '2026-01-31', NULL, '2', 1, 1, '2026-01-31 10:04:55'),
(10, '2026-02-02', NULL, '1', 1, 1, '2026-02-02 15:02:01'),
(11, '2026-02-04', NULL, '1', 2, 1, '2026-02-04 10:43:24'),
(12, '2026-02-04', NULL, '2', 1, 1, '2026-02-04 13:29:30'),
(13, '2026-02-04', NULL, '3', 2, 1, '2026-02-04 13:47:44'),
(14, '2026-02-04', NULL, '3', 1, 1, '2026-02-04 15:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id_detail`, `id_transaksi`, `id_barang`, `jumlah`) VALUES
(6, 8, 2, '255.00'),
(8, 8, 1, '200.00'),
(9, 9, 2, '200.00'),
(10, 10, 1, '231.00'),
(11, 11, 3, '100.00'),
(16, 13, 5, '100.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_pengguna` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('ADMIN','STAFF') DEFAULT 'STAFF',
  `aktif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_pengguna`, `nama`, `username`, `password`, `role`, `aktif`) VALUES
(1, 'Diki', 'admin', '$2y$10$2K77I4.MmfX6ROCPeD3Cj.BPYAMRyw0vpod6C7VYlmD/qdwxXf7ue', 'ADMIN', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `at_detail`
--
ALTER TABLE `at_detail`
  ADD PRIMARY KEY (`id_at`),
  ADD KEY `id_mutasi` (`id_mutasi`),
  ADD KEY `fk_at_detail_barang` (`id_barang`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `fk_barang_kelompok` (`id_kelompok`);

--
-- Indexes for table `jenis_barang`
--
ALTER TABLE `jenis_barang`
  ADD PRIMARY KEY (`id_jbarang`);

--
-- Indexes for table `jenis_mutasi`
--
ALTER TABLE `jenis_mutasi`
  ADD PRIMARY KEY (`id_jenis`),
  ADD UNIQUE KEY `kode_jenis` (`kode_jenis`);

--
-- Indexes for table `jenis_transaksi`
--
ALTER TABLE `jenis_transaksi`
  ADD PRIMARY KEY (`id_jenist`),
  ADD UNIQUE KEY `kode_jenis` (`kode_jenis`);

--
-- Indexes for table `kelompok_barang`
--
ALTER TABLE `kelompok_barang`
  ADD PRIMARY KEY (`id_kelompok`),
  ADD UNIQUE KEY `kode_kelompok` (`kode_kelompok`),
  ADD KEY `fk_kelompok_parent` (`parent_id`);

--
-- Indexes for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `id_jenis` (`id_jenis`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `fk_mutasi_transaksi` (`id_transaksi`);

--
-- Indexes for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_mutasi` (`id_mutasi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `pemakaian_produksi`
--
ALTER TABLE `pemakaian_produksi`
  ADD PRIMARY KEY (`id_pemakaian`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD PRIMARY KEY (`id_produksi`),
  ADD KEY `id_mutasi` (`id_mutasi`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_supplier` (`id_supplier`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id_detail`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `at_detail`
--
ALTER TABLE `at_detail`
  MODIFY `id_at` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jenis_barang`
--
ALTER TABLE `jenis_barang`
  MODIFY `id_jbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jenis_mutasi`
--
ALTER TABLE `jenis_mutasi`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kelompok_barang`
--
ALTER TABLE `kelompok_barang`
  MODIFY `id_kelompok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pemakaian_produksi`
--
ALTER TABLE `pemakaian_produksi`
  MODIFY `id_pemakaian` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `fk_mutasi_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
