-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 02:41 AM
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
(4, 'b2', 'Indah - Galur', 3, 'Kg', 1, 0);

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
  `tipe` enum('TRANSAKSI','AT','PRODUKSI','KOREKSI') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_mutasi`
--

INSERT INTO `jenis_mutasi` (`id_jenis`, `kode_jenis`, `nama_jenis`, `tipe`) VALUES
(1, 'TRANSAKSI', 'Transaksi Barang', 'TRANSAKSI'),
(2, 'AT', 'Pemakaian AT', 'AT'),
(3, 'PRODUKSI', 'Pemakaian Produksi', 'PRODUKSI'),
(4, 'KOREKSI', 'Koreksi Stok', 'KOREKSI'),
(5, 'STOKAWAL', 'Stok Awal', 'KOREKSI');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_transaksi`
--

CREATE TABLE `jenis_transaksi` (
  `id_jenist` int(11) NOT NULL,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `jenis_transaksi`
--

INSERT INTO `jenis_transaksi` (`id_jenist`, `kode_jenis`, `nama_jenis`, `aktif`) VALUES
(1, 'MASUK', 'Barang Masuk', 1),
(2, 'KELUAR', 'Barang Keluar', 1);

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
(2, 'a1', 'Arang Tempurung Kelapa', 'LOGISTIK', NULL),
(3, 'a2', 'Powder', 'RAW_MATERIAL', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mutasi`
--

CREATE TABLE `mutasi` (
  `id_mutasi` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `id_sub` int(11) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mutasi`
--

INSERT INTO `mutasi` (`id_mutasi`, `tanggal`, `id_jenis`, `id_sub`, `keterangan`, `dibuat_oleh`, `dibuat_pada`) VALUES
(3, '2026-01-23', 1, NULL, 'Pembelian Barang', 1, '2026-01-23 11:01:42'),
(4, '2026-01-24', 2, NULL, NULL, 1, '2026-01-24 10:39:49'),
(5, '2026-01-26', 2, NULL, 'Pemakaian AT', 1, '2026-01-26 10:25:07'),
(6, '2026-01-26', 5, NULL, 'Stok Awal', 1, '2026-01-26 15:41:04'),
(7, '2026-01-27', 2, NULL, 'Pemakaian AT', 1, '2026-01-27 11:58:15'),
(8, '2026-01-27', 2, NULL, 'Pemakaian AT', 1, '2026-01-27 15:53:22'),
(9, '2026-01-31', 1, NULL, 'transaksi Barang', 1, '2026-01-31 10:07:50'),
(10, '2026-01-31', 1, NULL, 'transaksi Barang', 1, '2026-01-31 15:04:51'),
(11, '2026-02-02', 1, NULL, 'transaksi Barang', 1, '2026-02-02 10:07:47'),
(12, '2026-02-02', 1, NULL, 'transaksi Barang', 1, '2026-02-02 10:37:39'),
(13, '2026-02-02', 1, NULL, 'transaksi Barang', 1, '2026-02-02 15:02:07');

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
(13, 13, 1, '231.00', NULL);

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
-- Table structure for table `sub_mutasi_bak`
--

CREATE TABLE `sub_mutasi_bak` (
  `id_sub` int(11) NOT NULL,
  `id_jenis` int(11) NOT NULL,
  `nama_sub` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sub_mutasi_bak`
--

INSERT INTO `sub_mutasi_bak` (`id_sub`, `id_jenis`, `nama_sub`) VALUES
(1, 1, 'ATK'),
(2, 1, 'APD'),
(3, 1, 'Sparepart'),
(4, 2, 'Sortir'),
(5, 2, 'MA'),
(6, 2, 'AA'),
(7, 2, 'Bahan Mentah'),
(8, 2, 'Air'),
(9, 2, 'ATP'),
(11, 3, 'Mixer');

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
(7, '2026-01-30', NULL, '1', 1, 1, '2026-01-30 15:32:29'),
(8, '2026-01-31', NULL, '3', 2, 1, '2026-01-31 08:57:01'),
(9, '2026-01-31', NULL, '2', 1, 1, '2026-01-31 10:04:55'),
(10, '2026-02-02', NULL, '1', 1, 1, '2026-02-02 15:02:01');

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
(10, 10, 1, '231.00');

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stok_barang`
-- (See below for the actual view)
--
CREATE TABLE `v_stok_barang` (
);

-- --------------------------------------------------------

--
-- Structure for view `v_stok_barang`
--
DROP TABLE IF EXISTS `v_stok_barang`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stok_barang`  AS SELECT `b`.`id_barang` AS `id_barang`, `b`.`nama_barang` AS `nama_barang`, sum(case `jm`.`arah` when 'IN' then `md`.`jumlah` else -`md`.`jumlah` end) AS `stok` FROM (((`mutasi_detail` `md` join `mutasi` `m` on(`md`.`id_mutasi` = `m`.`id_mutasi`)) join `jenis_mutasi` `jm` on(`m`.`id_jenis` = `jm`.`id_jenis`)) join `barang` `b` on(`md`.`id_barang` = `b`.`id_barang`)) GROUP BY `b`.`id_barang``id_barang`  ;

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
  ADD KEY `fk_mutasi_sub` (`id_sub`);

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
-- Indexes for table `sub_mutasi_bak`
--
ALTER TABLE `sub_mutasi_bak`
  ADD PRIMARY KEY (`id_sub`),
  ADD KEY `id_jenis` (`id_jenis`);

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
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pembelian` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

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
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jenis_barang`
--
ALTER TABLE `jenis_barang`
  MODIFY `id_jbarang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jenis_mutasi`
--
ALTER TABLE `jenis_mutasi`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `jenis_transaksi`
--
ALTER TABLE `jenis_transaksi`
  MODIFY `id_jenist` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kelompok_barang`
--
ALTER TABLE `kelompok_barang`
  MODIFY `id_kelompok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `sub_mutasi_bak`
--
ALTER TABLE `sub_mutasi_bak`
  MODIFY `id_sub` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `at_detail`
--
ALTER TABLE `at_detail`
  ADD CONSTRAINT `at_detail_ibfk_1` FOREIGN KEY (`id_mutasi`) REFERENCES `mutasi` (`id_mutasi`),
  ADD CONSTRAINT `fk_at_detail_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kelompok` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_barang` (`id_kelompok`);

--
-- Constraints for table `kelompok_barang`
--
ALTER TABLE `kelompok_barang`
  ADD CONSTRAINT `fk_kelompok_parent` FOREIGN KEY (`parent_id`) REFERENCES `kelompok_barang` (`id_kelompok`) ON DELETE SET NULL;

--
-- Constraints for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `fk_mutasi_sub` FOREIGN KEY (`id_sub`) REFERENCES `sub_mutasi_bak` (`id_sub`) ON DELETE SET NULL,
  ADD CONSTRAINT `mutasi_ibfk_1` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_mutasi` (`id_jenis`),
  ADD CONSTRAINT `mutasi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id_pengguna`);

--
-- Constraints for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  ADD CONSTRAINT `mutasi_detail_ibfk_1` FOREIGN KEY (`id_mutasi`) REFERENCES `mutasi` (`id_mutasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `mutasi_detail_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);

--
-- Constraints for table `pemakaian_produksi`
--
ALTER TABLE `pemakaian_produksi`
  ADD CONSTRAINT `pemakaian_produksi_ibfk_1` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);

--
-- Constraints for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD CONSTRAINT `produksi_detail_ibfk_1` FOREIGN KEY (`id_mutasi`) REFERENCES `mutasi` (`id_mutasi`);

--
-- Constraints for table `sub_mutasi_bak`
--
ALTER TABLE `sub_mutasi_bak`
  ADD CONSTRAINT `sub_mutasi_bak_ibfk_1` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_mutasi` (`id_jenis`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id_pengguna`);

--
-- Constraints for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `transaksi_detail_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaksi_detail_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
