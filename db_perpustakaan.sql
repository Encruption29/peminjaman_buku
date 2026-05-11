-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 11, 2026 at 03:26 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_perpustakaan`
CREATE DATABASE IF NOT EXISTS `db_perpustakaan` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `db_perpustakaan`;
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `kode_buku` varchar(10) NOT NULL,
  `nama_buku` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `tahun_terbit` year NOT NULL,
  `harga_sewa_hari` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`kode_buku`, `nama_buku`, `penerbit`, `tahun_terbit`, `harga_sewa_hari`) VALUES
('A043', 'Multimedia', 'Bumi Aksara', '2010', 24000.00),
('K001', 'Basis Data II', 'Erlangga', '2011', 15000.00),
('L0A2', 'Algoritma Pemograman', 'Yudhistira', '2012', 17000.00),
('M104', 'MS. Office', 'Gema Insani', '2015', 12500.00),
('NJ80', 'Jaringan Komputer', 'Erlangga', '2014', 26000.00);

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjam` int NOT NULL,
  `nama_peminjam` varchar(100) NOT NULL,
  `alamat` varchar(150) NOT NULL,
  `kode_buku` varchar(10) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `lama_pinjam` int NOT NULL,
  `status_pengembalian` varchar(20) NOT NULL,
  `biaya` decimal(12,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjam`, `nama_peminjam`, `alamat`, `kode_buku`, `tanggal_pinjam`, `lama_pinjam`, `status_pengembalian`, `biaya`) VALUES
(1121, 'Aisyah Sholeha', 'Jl. Yos Sudarso', 'NJ80', '2017-12-13', 1, 'Kembali', 26000.00),
(1345, 'Tika Sari', 'Jl. Pramuka', 'A043', '2017-12-04', 4, 'Kembali', 96000.00),
(1456, 'Fuadi', 'Jl. Sembilang', 'L0A2', '2017-12-14', 1, 'Kembali', 17000.00),
(2902, 'Heru Purwanto', 'Jl. Yos Sudarso', 'K001', '2017-12-12', 3, 'Kembali', 45000.00),
(3003, 'Aditya Warman', 'Jl. Sekolah', 'L0A2', '2017-12-04', 4, 'Kembali', 68000.00),
(3245, 'Aris Wandana', 'Jl. Pelajar', 'K001', '2017-12-07', 5, 'Kembali', 75000.00),
(3563, 'Nuri Indah', 'Jl. Sembilang', 'M104', '2017-12-10', 5, 'Belum', 62500.00),
(3782, 'Heri Wanto', 'Jl. Pramuka', 'M104', '2017-12-11', 5, 'Belum', 62500.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`kode_buku`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjam`),
  ADD KEY `kode_buku` (`kode_buku`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`kode_buku`) REFERENCES `buku` (`kode_buku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
