-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2026 at 06:35 AM
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
  `id_supplier` int(11) DEFAULT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `sortir` decimal(10,2) DEFAULT NULL,
  `ma` decimal(10,2) DEFAULT NULL,
  `aa` decimal(10,2) DEFAULT NULL,
  `b_mentah` decimal(10,2) DEFAULT NULL,
  `air` decimal(10,2) DEFAULT NULL,
  `atp` decimal(10,2) DEFAULT NULL,
  `tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `at_detail`
--

INSERT INTO `at_detail` (`id_at`, `id_mutasi`, `id_supplier`, `id_barang`, `sortir`, `ma`, `aa`, `b_mentah`, `air`, `atp`, `tanggal`) VALUES
(2, 7, NULL, 2, 2.00, 2.00, 555.00, 55.00, 55.00, 55.00, '2026-02-06'),
(3, 8, NULL, 2, 55.00, 55.00, 55.00, 55.00, 55.00, 55.00, '2026-02-06'),
(4, 36, NULL, 2, 150.00, 3.00, 8.00, 9.00, 4.00, 10.00, '2026-02-13'),
(5, 39, NULL, 4, 500.00, 50.00, 25.00, 30.00, 60.00, 400.00, '2026-02-24'),
(6, 42, NULL, 2, 255.00, 100.00, 70.00, 30.00, 50.00, 300.00, '2026-02-24'),
(7, 50, NULL, 14, 500.00, 35.00, 35.00, 35.00, 25.00, 25.00, '2026-03-13'),
(8, 51, NULL, 13, 255.00, 25.00, 25.00, 5.00, 2.00, 55.00, '2026-03-14'),
(9, 52, NULL, 13, 500.00, 25.00, 25.00, 25.00, 25.00, 200.00, '2026-03-14'),
(10, 53, 2, 13, 255.00, 25.00, 25.00, 5.00, 15.00, 5.00, '2026-03-14'),
(11, 56, 2, 13, 500.00, 25.00, 55.00, 55.00, 55.00, 300.00, '2026-03-26'),
(12, 57, 1, 13, 400.00, 35.00, 23.00, 15.00, 22.00, 200.00, '2026-03-26'),
(13, 59, 1, 13, 500.00, 25.00, 25.00, 25.00, 15.00, 300.00, '2026-03-26');

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL,
  `kode_barang` varchar(30) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `id_kelompok` int(11) NOT NULL,
  `satuan` enum('Kg','Pcs','Pack','MB') DEFAULT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `stok_minimum` int(11) DEFAULT 20,
  `pakai_supplier` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `kode_barang`, `nama_barang`, `id_kelompok`, `satuan`, `aktif`, `stok_minimum`, `pakai_supplier`) VALUES
(5, 'PC28', 'Plastik C28', 10, 'Pcs', 1, 0, 0),
(6, 'PC22', 'Plastik C22 ¼ KG -- (14/8x16x05)', 10, 'Pcs', 1, 0, 0),
(7, 'AAK', 'Abu Kayak', 5, 'Kg', 1, 0, 0),
(8, 'BC25', 'C25.D / 025', 15, 'Kg', 1, 0, 0),
(9, 'BC26D', 'C26 D / 025', 15, 'Kg', 1, 0, 0),
(10, 'BPET', 'Tapioka', 7, 'Kg', 1, 0, 0),
(11, 'BPESA', 'Sagu Aren', 7, 'Kg', 1, 0, 0),
(12, 'BPEG', 'Gas 12 KG', 7, 'Kg', 1, 0, 0),
(13, 'AT', 'Arang Tempurung Kelapa', 2, 'Kg', 1, 0, 1),
(14, 'P', 'Powder', 3, 'Kg', 1, 0, 1),
(15, 'IBC25N', 'Inner Box C25  1 KG - COCO DIAMOND GRM (ALAMAT LAMA)', 11, 'Pcs', 1, 0, 0),
(16, 'IPP', 'B1 A 180', 18, 'Pack', 1, 0, 0),
(17, 'ABM', 'BATOK MENTAH', 5, 'Kg', 1, 0, 0),
(18, 'BPETD', 'TITANIUM DIOXIDE', 7, 'Kg', 1, 0, 0),
(19, 'PRC25', 'Plastik Rebah C25 1 KG -- (24/16x22x05)', 10, 'Pcs', 1, 0, 0),
(20, 'PBC25', 'Plastik Berdiri C25 1 KG HD -- (19/11x25x05)', 10, 'Pcs', 1, 0, 0),
(21, 'PC15KW', 'Plastik C15 KW 1 KG -- (18,5/9,5x27x05)', 10, 'Pcs', 1, 0, 0),
(22, 'PC25VG', 'Plastik C25 1 KG VIP-GOLD --  (24/16x22x05)', 10, 'Pcs', 1, 0, 0),
(23, 'PC25YR', 'Plastik C25 1 KG CC Yellow REBAH -- (24/16x22x05)', 10, 'Pcs', 1, 0, 0),
(24, 'PC25YB', 'Plastik C25 1 KG CC YELLOW BERDIRI -- (19/11X25X05)', 10, 'Pcs', 1, 0, 0),
(26, 'PCLL', 'Plastik Coco Light LARGE -- (28/14x23x05)', 10, 'Pcs', 1, 0, 0),
(27, 'PB1TCR', 'Plastik B1 TCR -- (41/25x27x05)', 10, 'Pcs', 1, 0, 0),
(28, 'PFS10', 'Plastik FS 10 KG -- (70/44x48x05)', 10, 'Pcs', 1, 0, 0),
(29, 'PC171', 'Plastik C17 1 kg COCOCZAR Large -- (22/14,5x27x05)', 10, 'Pcs', 1, 0, 0),
(31, 'IBC25O', 'Inner Box C25 1 KG - COCO DIAMOND GRM (ALAMAT BARU)', 11, 'Pcs', 1, 0, 0),
(32, 'MBECO10', 'Master Box ECObrasa 10 Kg', 12, 'MB', 1, 0, 0),
(33, 'MBSH10', 'Master Box SHISCHO  10 KG', 12, 'MB', 1, 0, 0),
(34, 'LSCC', 'Sticker COCO COFFEE', 13, 'Pcs', 1, 0, 0),
(35, 'LFP', 'Flyer PANORAMA', 13, 'Pcs', 1, 0, 0),
(36, 'KC25D', 'C25.D / 025', 16, 'Kg', 1, 0, 0),
(37, 'KC26D', 'C26 D / 025', 16, 'Kg', 1, 0, 0),
(38, 'IPPB1A180', 'B1 A 180', 18, 'Pcs', 1, 0, 0),
(39, 'MBFS22SH5', 'MB FS 22 SHISCHO 5 KG', 20, 'Pcs', 1, 0, 0),
(40, 'RPKWC27', 'KW C27', 21, 'Kg', 1, 0, 0),
(41, 'RPKWC25', 'KW C25', 21, 'Kg', 1, 0, 0),
(42, 'BRFS', 'FS', 22, 'Kg', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bkbriket`
--

CREATE TABLE `bkbriket` (
  `id_bk` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_barang_briket` int(11) DEFAULT NULL,
  `id_kelompok` int(11) DEFAULT NULL,
  `lokasi` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('LOLOS','KARANTINA') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bkbriket`
--

INSERT INTO `bkbriket` (`id_bk`, `tanggal`, `id_barang_briket`, `id_kelompok`, `lokasi`, `keterangan`, `created_at`, `status`) VALUES
(1, '2026-02-11', 8, 15, 'DCS1', '', '2026-02-11 03:03:20', 'LOLOS'),
(2, '2026-02-13', 8, 16, 'DCS1', '', '2026-02-13 07:16:31', 'KARANTINA'),
(3, '2026-02-18', 8, 15, 'DCS 1', '', '2026-02-18 02:42:55', 'LOLOS'),
(4, '2026-02-19', 9, 15, 'DCS2', '', '2026-02-19 02:30:20', 'LOLOS'),
(5, '2026-03-03', 8, 15, 'DCS 1', '', '2026-03-03 04:02:15', 'LOLOS'),
(6, '2026-03-16', 8, 15, 'DCS 1', '', '2026-03-16 06:47:00', 'LOLOS'),
(7, '2026-03-03', 8, 15, 'DCS 1', '', '2026-03-03 04:02:15', 'LOLOS'),
(8, '2026-03-25', 9, 16, 'DCS 1', '', '2026-03-25 02:05:08', 'KARANTINA');

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

--
-- Dumping data for table `bkbriket_bongkar`
--

INSERT INTO `bkbriket_bongkar` (`id_bongkar`, `id_bk`, `tanggal_bongkar`, `krg`, `add_kg`, `created_at`) VALUES
(1, 1, NULL, 5, 12, '2026-02-11 03:04:18'),
(2, 2, NULL, 100, 23, '2026-02-13 07:17:11'),
(4, 3, '2026-02-18', 15, 1, '2026-02-18 06:56:32'),
(5, 4, '2026-02-19', 30, 5, '2026-02-19 02:30:51'),
(6, 5, '2026-03-03', 5, 1, '2026-03-03 04:02:34'),
(7, 6, '2026-03-16', 11, 12, '2026-03-16 06:47:52'),
(8, 8, '2026-03-25', 2, 1, '2026-03-25 02:05:32');

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

--
-- Dumping data for table `bkbriket_mutasi`
--

INSERT INTO `bkbriket_mutasi` (`id_mutasi`, `id_bk`, `tanggal`, `jenis`, `krg`, `add_kg`, `keterangan`, `created_at`) VALUES
(1, 1, '2026-02-11', 'PACKING', 5, 12, '', '2026-02-11 03:04:18'),
(2, 1, '2026-02-11', 'REPRO', 3, 7, '', '2026-02-11 03:04:18'),
(3, 1, '2026-02-11', 'JUAL', 1, 19, '', '2026-02-11 03:04:18'),
(7, 2, '2026-02-13', 'PACKING', 10, 5, '', '2026-02-13 07:17:22'),
(8, 2, '2026-02-13', 'REPRO', 4, 14, '', '2026-02-13 07:17:22'),
(9, 2, '2026-02-13', 'PACKING', 15, 9, '', '2026-02-13 07:17:22'),
(19, 3, '2026-02-18', 'PACKING', 11, 1, '', '2026-02-18 06:54:18'),
(20, 4, '2026-02-19', 'PACKING', 3, 9, '', '2026-02-19 02:30:51'),
(21, 4, '2026-02-19', 'REPRO', 5, 8, '', '2026-02-19 02:30:51'),
(22, 4, '2026-02-19', 'JUAL', 7, 7, '', '2026-02-19 02:30:51'),
(23, 6, '2026-03-16', 'PACKING', 2, 4, '', '2026-03-16 06:47:52'),
(24, 6, '2026-03-16', 'REPRO', 3, 4, '', '2026-03-16 06:47:52'),
(25, 6, '2026-03-16', 'JUAL', 4, 4, '', '2026-03-16 06:47:52'),
(26, 8, '2026-03-25', 'PACKING', 1, 1, '', '2026-03-25 02:05:32');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_barang`
--

CREATE TABLE `jenis_barang` (
  `id_jbarang` int(11) NOT NULL,
  `jenis_barang` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `tipe` enum('TRANSAKSI','AT','PRODUKSI','KOREKSI','STOKAWAL') NOT NULL,
  `arah` enum('MASUK','KELUAR') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `jenis_mutasi`
--

INSERT INTO `jenis_mutasi` (`id_jenis`, `kode_jenis`, `nama_jenis`, `tipe`, `arah`) VALUES
(1, 'TRM', 'Transaksi Barang Masuk', 'TRANSAKSI', 'MASUK'),
(2, 'AT', 'Pemakaian AT', 'AT', 'KELUAR'),
(3, 'PRODM', 'Pemakaian Produksi', 'PRODUKSI', NULL),
(4, 'KS', 'Koreksi Stok', 'KOREKSI', NULL),
(5, 'STOKAWAL', 'Stok Awal', 'STOKAWAL', 'MASUK'),
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `kelompok_barang`
--

INSERT INTO `kelompok_barang` (`id_kelompok`, `kode_kelompok`, `nama_kelompok`, `tipe_kelompok`, `parent_id`) VALUES
(2, 'AT', 'Arang Tempurung Kelapa', 'PRODUK_JADI', 9),
(3, 'PO', 'Powder', 'RAW_MATERIAL', NULL),
(4, 'BP', 'Bahan Packing', 'LOGISTIK', NULL),
(5, 'AFK', 'AFKIR', 'RAW_MATERIAL', 9),
(6, 'BRI', 'BRIKET', 'RAW_MATERIAL', NULL),
(7, 'BPE', 'Bahan Pendamping & Energi', 'RAW_MATERIAL', 9),
(8, 'BBP', 'Bahan Baku Pendukung', 'LOGISTIK', NULL),
(9, 'BB', 'Bahan Baku', 'RAW_MATERIAL', NULL),
(10, 'IP', 'Inner Plastik', 'LOGISTIK', 8),
(11, 'IB', 'Inner Box Bahan Baku Pendukung', 'LOGISTIK', 8),
(12, 'MB', 'Master Box', 'LOGISTIK', 8),
(13, 'IV', 'Lainnya', 'LOGISTIK', 8),
(14, 'WIP', 'Work In Progress', 'RAW_MATERIAL', NULL),
(15, 'HBO', 'Hasil Bongkar Oven', 'PRODUK_JADI', 14),
(16, 'HBK', 'Hasil Bongkar Karantina', 'PRODUK_JADI', 14),
(17, 'BJ', 'Barang Jadi', 'PRODUK_JADI', NULL),
(18, 'IPP', 'Inner Plastik/Pack', 'LOGISTIK', 17),
(19, 'BJIB', 'Inner Box Barang Jadi', 'LOGISTIK', 17),
(20, 'BJMB', 'Master Box Barang Jadi', 'LOGISTIK', 17),
(21, 'BJRP', 'Reject Packing', 'PRODUK_JADI', 17),
(22, 'BJBR', 'Briket Riset', 'RAW_MATERIAL', 17);

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
  `id_supplier` int(11) DEFAULT NULL,
  `jenis` enum('AWAL','MASUK','KELUAR') DEFAULT 'MASUK'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mutasi`
--

INSERT INTO `mutasi` (`id_mutasi`, `id_transaksi`, `tanggal`, `id_jenis`, `keterangan`, `dibuat_oleh`, `dibuat_pada`, `arah`, `id_supplier`, `jenis`) VALUES
(3, NULL, '2026-01-23', 1, 'Pembelian Barang', 1, '2026-01-23 11:01:42', NULL, NULL, 'MASUK'),
(4, NULL, '2026-01-24', 2, NULL, 1, '2026-01-24 10:39:49', NULL, NULL, 'MASUK'),
(5, NULL, '2026-01-26', 2, 'Pemakaian AT', 1, '2026-01-26 10:25:07', NULL, NULL, 'MASUK'),
(7, NULL, '2026-01-27', 2, 'Pemakaian AT', 1, '2026-01-27 11:58:15', NULL, NULL, 'MASUK'),
(8, NULL, '2026-01-27', 2, 'Pemakaian AT', 1, '2026-01-27 15:53:22', NULL, NULL, 'MASUK'),
(9, NULL, '2026-01-31', 1, 'transaksi Barang', 1, '2026-01-31 10:07:50', 'MASUK', NULL, 'MASUK'),
(10, NULL, '2026-01-31', 1, 'transaksi Barang', 1, '2026-01-31 15:04:51', 'KELUAR', NULL, 'KELUAR'),
(27, NULL, '2026-01-30', 5, 'Stok Awal', 1, '2026-02-10 09:55:41', NULL, NULL, 'AWAL'),
(28, NULL, '2026-01-10', 5, 'Stok Awal', 1, '2026-02-10 09:58:15', 'MASUK', NULL, 'AWAL'),
(29, NULL, '2026-01-10', 0, 'Stok Awal', NULL, '2026-02-10 10:53:56', 'MASUK', NULL, 'AWAL'),
(30, NULL, '2026-01-10', 0, 'Stok Awal', NULL, '2026-02-10 10:54:09', 'MASUK', NULL, 'AWAL'),
(31, NULL, '2026-01-10', 0, 'Stok Awal', NULL, '2026-02-10 10:54:30', 'MASUK', NULL, 'AWAL'),
(43, 24, '2026-03-11', 1, 'Transaksi Barang', 1, '2026-03-11 13:34:58', 'MASUK', NULL, 'MASUK'),
(45, 25, '2026-03-11', 1, 'Transaksi Barang', 1, '2026-03-11 14:21:32', 'MASUK', 1, 'MASUK'),
(46, NULL, '2026-03-12', 5, 'Stok Awal', NULL, '2026-03-12 14:02:45', 'MASUK', NULL, 'AWAL'),
(47, NULL, '2026-03-13', 5, 'Stok Awal', NULL, '2026-03-13 13:57:06', 'MASUK', NULL, 'AWAL'),
(49, NULL, '2026-03-01', 5, 'Stok Awal', NULL, '2026-03-13 14:15:40', 'MASUK', NULL, 'AWAL'),
(50, NULL, '2026-03-13', 2, 'Pemakaian AT', 1, '2026-03-13 14:24:20', NULL, NULL, 'MASUK'),
(51, NULL, '2026-03-14', 2, 'Pemakaian AT', 1, '2026-03-14 11:26:43', NULL, NULL, 'MASUK'),
(52, NULL, '2026-03-14', 2, 'Pemakaian AT', 1, '2026-03-14 13:21:15', NULL, NULL, 'MASUK'),
(53, NULL, '2026-03-14', 2, 'Pemakaian AT', 1, '2026-03-14 14:04:39', NULL, 2, 'MASUK'),
(54, NULL, '2026-03-14', 3, 'Produksi - Pemakaian Produksi', 1, '2026-03-14 15:31:32', 'KELUAR', 2, 'KELUAR'),
(55, NULL, '2026-03-26', 5, 'Stok Awal', NULL, '2026-03-26 08:52:11', 'MASUK', NULL, 'AWAL'),
(56, NULL, '2026-03-26', 2, 'Pemakaian AT', 1, '2026-03-26 10:44:38', NULL, 2, 'MASUK'),
(57, NULL, '2026-03-26', 2, 'Pemakaian AT', 1, '2026-03-26 10:45:07', NULL, 1, 'MASUK'),
(58, NULL, '2026-03-26', 3, 'Produksi - Pemakaian Produksi', 1, '2026-03-26 10:46:28', 'KELUAR', 1, 'KELUAR'),
(59, NULL, '2026-03-26', 2, 'Pemakaian AT', 1, '2026-03-26 11:55:52', NULL, 1, 'MASUK'),
(63, NULL, '2026-02-01', 5, 'Stok Awal', NULL, '2026-03-28 09:37:52', 'MASUK', NULL, 'AWAL');

-- --------------------------------------------------------

--
-- Table structure for table `mutasi_detail`
--

CREATE TABLE `mutasi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_mutasi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `jenis_pemakaian` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mutasi_detail`
--

INSERT INTO `mutasi_detail` (`id_detail`, `id_mutasi`, `id_barang`, `id_supplier`, `jumlah`, `jenis_pemakaian`) VALUES
(1, 3, 1, NULL, 222.00, NULL),
(9, 9, 2, NULL, 255.00, NULL),
(10, 10, 1, NULL, 22.00, NULL),
(23, 27, 6, NULL, 500.00, NULL),
(27, 31, 3, NULL, 200.00, NULL),
(28, 31, 4, NULL, 100.00, NULL),
(37, 43, 7, NULL, 100.00, NULL),
(38, 43, 13, 1, 100.00, NULL),
(39, 43, 13, 2, 200.00, NULL),
(40, 45, 14, NULL, 100.00, NULL),
(41, 45, 14, NULL, 100.00, NULL),
(42, 46, 7, NULL, 255.00, NULL),
(43, 46, 13, 1, 2000.00, NULL),
(44, 47, 14, NULL, 2000.00, NULL),
(45, 48, 14, 1, 1000.00, NULL),
(46, 49, 14, 2, 1000.00, NULL),
(47, 54, 14, 2, 2.00, NULL),
(48, 55, 13, 2, 2000.00, NULL),
(49, 58, 14, 1, 250.00, NULL),
(50, 60, 7, NULL, 2555.00, NULL),
(51, 61, 7, NULL, 255.00, NULL),
(52, 62, 5, NULL, 25.00, NULL),
(53, 63, 13, 1, 806.00, NULL);

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

--
-- Dumping data for table `produksi`
--

INSERT INTO `produksi` (`id_produksi`, `tanggal`, `id_barang_atp`, `id_supplier`, `keterangan`, `dibuat_oleh`, `dibuat_pada`) VALUES
(11, '2026-03-14', 14, 2, '', 1, '2026-03-14 08:31:32'),
(12, '2026-03-26', 14, 1, '', 1, '2026-03-26 03:46:28');

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

--
-- Dumping data for table `produksi_detail`
--

INSERT INTO `produksi_detail` (`id_detail`, `id_produksi`, `id_barang_atp`, `mixer`) VALUES
(10, 11, 14, 2.00),
(11, 12, 14, 250.00);

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

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id_supplier`, `nama_supplier`, `alamat`, `telepon`) VALUES
(1, 'FADLI', 'POLMAN', '08238827384'),
(2, 'INDAH', 'GALUR', '08828282828'),
(3, 'ANSEL', 'NTT', '08888888222'),
(4, 'MARCELLINO', 'NTT', ''),
(5, 'HASAN', 'SURABAYA', ''),
(6, 'CV AFLAHA', 'MAMUJU', ''),
(7, 'WAHYU', 'SANDEN', ''),
(8, 'RISAL', 'BANTUL', ''),
(9, 'RISTANTO', 'BANTUL', ''),
(10, 'HAMZAH', 'SULAWESI', '');

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

--
-- Dumping data for table `tambahan`
--

INSERT INTO `tambahan` (`id_tambahan`, `id_barang`, `jumlah`, `keterangan`, `tanggal`) VALUES
(2, 10, 5.00, '', '2026-03-03');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `tanggal_terima` date NOT NULL,
  `jenis_transaksi` int(11) NOT NULL,
  `id_kelompok` int(11) DEFAULT NULL,
  `dibuat_oleh` int(11) DEFAULT NULL,
  `dibuat_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `tanggal_terima`, `jenis_transaksi`, `id_kelompok`, `dibuat_oleh`, `dibuat_pada`) VALUES
(8, '2026-01-31', 2, 9, 1, '2026-01-31 08:57:01'),
(9, '2026-01-31', 1, NULL, 1, '2026-01-31 10:04:55'),
(19, '2026-03-09', 1, 8, 1, '2026-03-09 14:13:45'),
(20, '2026-03-10', 1, 4, 1, '2026-03-10 14:20:06'),
(21, '2026-03-10', 1, 6, 1, '2026-03-10 14:20:15'),
(22, '2026-03-11', 1, 14, 1, '2026-03-11 10:23:29'),
(23, '2026-03-11', 1, 17, 1, '2026-03-11 10:33:19'),
(24, '2026-03-11', 1, 9, 1, '2026-03-11 13:34:52'),
(25, '2026-03-11', 1, 3, 1, '2026-03-11 14:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_detail`
--

CREATE TABLE `transaksi_detail` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `id_supplier` int(11) DEFAULT NULL,
  `jumlah` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `transaksi_detail`
--

INSERT INTO `transaksi_detail` (`id_detail`, `id_transaksi`, `id_barang`, `id_supplier`, `jumlah`) VALUES
(6, 8, 2, NULL, 255.00),
(8, 8, 1, NULL, 200.00),
(9, 9, 2, NULL, 200.00),
(21, 24, 7, NULL, 100.00),
(24, 24, 13, 3, 200.00),
(26, 25, 14, 1, 100.00),
(27, 25, 14, 2, 100.00);

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
  ADD KEY `fk_at_detail_barang` (`id_barang`),
  ADD KEY `fk_at_supplier` (`id_supplier`);

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`),
  ADD UNIQUE KEY `kode_barang` (`kode_barang`),
  ADD KEY `fk_barang_kelompok` (`id_kelompok`);

--
-- Indexes for table `bkbriket`
--
ALTER TABLE `bkbriket`
  ADD PRIMARY KEY (`id_bk`),
  ADD KEY `idx_bkbriket_tanggal` (`tanggal`),
  ADD KEY `fk_bkbriket_barang` (`id_barang_briket`),
  ADD KEY `fk_bkbriket_kelompok` (`id_kelompok`);

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
  ADD KEY `id_barang` (`id_barang`),
  ADD KEY `fk_mutasi_detail_supplier` (`id_supplier`);

--
-- Indexes for table `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id_produksi`),
  ADD KEY `FK_id_barang_atp` (`id_barang_atp`),
  ADD KEY `fk_produksi_supplier` (`id_supplier`);

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
  ADD KEY `dibuat_oleh` (`dibuat_oleh`),
  ADD KEY `fk_transaksi_kelompok` (`id_kelompok`);

--
-- Indexes for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_supplier` (`id_supplier`);

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
  MODIFY `id_at` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `barang`
--
ALTER TABLE `barang`
  MODIFY `id_barang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `bkbriket`
--
ALTER TABLE `bkbriket`
  MODIFY `id_bk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bkbriket_bongkar`
--
ALTER TABLE `bkbriket_bongkar`
  MODIFY `id_bongkar` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bkbriket_mutasi`
--
ALTER TABLE `bkbriket_mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id_kelompok` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `mutasi`
--
ALTER TABLE `mutasi`
  MODIFY `id_mutasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id_produksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stok_fisik_at`
--
ALTER TABLE `stok_fisik_at`
  MODIFY `id_stok_fisik` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id_supplier` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tambahan`
--
ALTER TABLE `tambahan`
  MODIFY `id_tambahan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
  ADD CONSTRAINT `fk_at_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`);

--
-- Constraints for table `barang`
--
ALTER TABLE `barang`
  ADD CONSTRAINT `fk_barang_kelompok` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_barang` (`id_kelompok`);

--
-- Constraints for table `bkbriket`
--
ALTER TABLE `bkbriket`
  ADD CONSTRAINT `fk_bkbriket_barang` FOREIGN KEY (`id_barang_briket`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_bkbriket_kelompok` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_barang` (`id_kelompok`) ON UPDATE CASCADE;

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
-- Constraints for table `mutasi_detail`
--
ALTER TABLE `mutasi_detail`
  ADD CONSTRAINT `fk_mutasi_detail_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`);

--
-- Constraints for table `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `FK_id_barang_atp` FOREIGN KEY (`id_barang_atp`) REFERENCES `barang` (`id_barang`),
  ADD CONSTRAINT `fk_produksi_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL;

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

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `fk_transaksi_kelompok` FOREIGN KEY (`id_kelompok`) REFERENCES `kelompok_barang` (`id_kelompok`) ON UPDATE CASCADE;

--
-- Constraints for table `transaksi_detail`
--
ALTER TABLE `transaksi_detail`
  ADD CONSTRAINT `fk_detail_supplier` FOREIGN KEY (`id_supplier`) REFERENCES `supplier` (`id_supplier`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
