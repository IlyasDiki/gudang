-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2026 at 03:59 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
  `atp` decimal(10,2) DEFAULT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `stok_minimum` int(11) DEFAULT 20,
  `parent_id` int(11) DEFAULT NULL,
  `pakai_supplier` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bkbriket`
--

CREATE TABLE `bkbriket` (
  `id_bk` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_barang_briket` int(11) DEFAULT NULL,
  `lokasi` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('LOLOS','KARANTINA') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bkbriket_bongkar`
--

CREATE TABLE `bkbriket_bongkar` (
  `id_bongkar` int(11) NOT NULL,
  `id_bk` int(11) NOT NULL,
  `tanggal_bongkar` date DEFAULT NULL,
  `krg` decimal(10,0) NOT NULL DEFAULT 0,
  `add_kg` decimal(10,0) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bkbriket_mutasi`
--

CREATE TABLE `bkbriket_mutasi` (
  `id_mutasi` int(11) NOT NULL,
  `id_bk` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jenis` enum('PACKING','REPRO','JUAL') NOT NULL,
  `krg` decimal(10,0) NOT NULL DEFAULT 0,
  `add_kg` decimal(10,0) NOT NULL DEFAULT 0,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_barang`
--

CREATE TABLE `jenis_barang` (
  `id_jbarang` int(11) NOT NULL,
  `jenis_barang` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_mutasi`
--

CREATE TABLE `jenis_mutasi` (
  `id_jenis` int(11) NOT NULL,
  `kode_jenis` varchar(20) NOT NULL,
  `nama_jenis` varchar(50) NOT NULL,
  `tipe` enum('TRANSAKSI','AT','PRODUKSI','KOREKSI','STOKAWAL') NOT NULL,
  `arah` enum('MASUK','KELUAR') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `arah` enum('MASUK','KELUAR') DEFAULT NULL,
  `id_supplier` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produksi`
--

CREATE TABLE `produksi` (
  `id_produksi` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_barang_atp` int(11) NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produksi_detail`
--

CREATE TABLE `produksi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_produksi` int(11) NOT NULL,
  `id_barang_atp` int(11) NOT NULL,
  `mixer` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_fisik_at`
--

CREATE TABLE `stok_fisik_at` (
  `id_stok_fisik` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `jumlah` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL,
  `nama_supplier` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tambahan`
--

CREATE TABLE `tambahan` (
  `id_tambahan` int(11) NOT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `jumlah` decimal(15,2) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  ADD KEY `fk_barang_kelompok` (`id_kelompok`),
  ADD KEY `fk_parent` (`parent_id`);

--
-- Indexes for table `bkbriket`
--
ALTER TABLE `bkbriket`
  ADD PRIMARY KEY (`id_bk`),
  ADD KEY `idx_bkbriket_tanggal` (`tanggal`),
  ADD KEY `fk_bkbriket_barang` (`id_barang_briket`);

--
-- Indexes for table `bkbriket_bongkar`
--
ALTER TABLE `bkbriket_bongkar`
  ADD PRIMARY KEY (`id_bongkar`),
  ADD KEY `idx_bongkar_idbk` (`id_bk`),
  ADD KEY `idx_bongkar_tanggal` (`tanggal_bongkar`);

--
-- Indexes for table `bkbriket_mutasi`
--
ALTER TABLE `bkbriket_mutasi`
  ADD PRIMARY KEY (`id_mutasi`),
  ADD KEY `idx_mutasi_idbk` (`id_bk`),
  ADD KEY `idx_mutasi_tanggal` (`tanggal`),
  ADD KEY `idx_mutasi_jenis` (`jenis`);

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
  ADD KEY `fk_mutasi_transaksi` (`id_transaksi`),
  ADD KEY `fk_mutasi_supplier` (`id_supplier`);

--
-- Indexes for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_mutasi` (`id_mutasi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id_produksi`),
  ADD KEY `FK_id_barang_atp` (`id_barang_atp`);

--
-- Indexes for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_produksi` (`id_produksi`),
  ADD KEY `id_barang` (`id_barang_atp`);

--
-- Indexes for table `stok_fisik_at`
--
ALTER TABLE `stok_fisik_at`
  ADD PRIMARY KEY (`id_stok_fisik`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id_supplier`);

--
-- Indexes for table `tambahan`
--
ALTER TABLE `tambahan`
  ADD PRIMARY KEY (`id_tambahan`);

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
  MODIFY `id_at` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bkbriket`
--
ALTER TABLE `bkbriket`
  MODIFY `id_bk` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bkbriket_bongkar`
--
ALTER TABLE `bkbriket_bongkar`
  MODIFY `id_bongkar` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bkbriket_mutasi`
--
ALTER TABLE `bkbriket_mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_barang`
--
ALTER TABLE `jenis_barang`
  MODIFY `id_jbarang` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_mutasi`
--
ALTER TABLE `jenis_mutasi`
  MODIFY `id_jenis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kelompok_barang`
--
ALTER TABLE `kelompok_barang`
  MODIFY `id_kelompok` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stok_fisik_at`
--
ALTER TABLE `stok_fisik_at`
  MODIFY `id_stok_fisik` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tambahan`
--
ALTER TABLE `tambahan`
  MODIFY `id_tambahan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_parent` FOREIGN KEY (`parent_id`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE;

--
-- Constraints for table `bkbriket`
--
ALTER TABLE `bkbriket`
  ADD CONSTRAINT `fk_bkbriket_barang` FOREIGN KEY (`id_barang_briket`) REFERENCES `barang` (`id_barang`);

--
-- Constraints for table `bkbriket_bongkar`
--
ALTER TABLE `bkbriket_bongkar`
  ADD CONSTRAINT `fk_bongkar_bk` FOREIGN KEY (`id_bk`) REFERENCES `bkbriket` (`id_bk`) ON DELETE CASCADE;

--
-- Constraints for table `bkbriket_mutasi`
--
ALTER TABLE `bkbriket_mutasi`
  ADD CONSTRAINT `fk_mutasi_bk` FOREIGN KEY (`id_bk`) REFERENCES `bkbriket` (`id_bk`) ON DELETE CASCADE;

--
-- Constraints for table `mutasi`
--
ALTER TABLE `mutasi`
  ADD CONSTRAINT `fk_mutasi_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`),
  ADD CONSTRAINT `fk_mutasi_transaksi` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`) ON DELETE CASCADE;

--
-- Constraints for table `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `FK_id_barang_atp` FOREIGN KEY (`id_barang_atp`) REFERENCES `barang` (`id_barang`);

--
-- Constraints for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD CONSTRAINT `produksi_detail_ibfk_1` FOREIGN KEY (`id_produksi`) REFERENCES `produksi` (`id_produksi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `produksi_detail_ibfk_2` FOREIGN KEY (`id_barang_atp`) REFERENCES `barang` (`id_barang`) ON UPDATE CASCADE;

--
-- Constraints for table `stok_fisik_at`
--
ALTER TABLE `stok_fisik_at`
  ADD CONSTRAINT `fk_stok_fisik_barang` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
