-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 02:37 AM
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
-- Database: `supplyfinder`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_kategori`
--

CREATE TABLE `tb_kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kategori`
--

INSERT INTO `tb_kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Material'),
(2, 'Peralatan'),
(3, 'Spare Part'),
(4, 'Elektronik');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kos`
--

CREATE TABLE `tb_kos` (
  `id_kos` int(11) NOT NULL,
  `nama_kos` varchar(150) NOT NULL,
  `alamat` text NOT NULL,
  `kota` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `tipe_kamar` varchar(100) NOT NULL,
  `jumlah_kamar` int(11) DEFAULT 1,
  `fasilitas` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('tersedia','penuh') DEFAULT 'tersedia',
  `tanggal_dibuat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_kos`
--

INSERT INTO `tb_kos` (`id_kos`, `nama_kos`, `alamat`, `kota`, `harga`, `tipe_kamar`, `jumlah_kamar`, `fasilitas`, `deskripsi`, `foto`, `status`, `tanggal_dibuat`) VALUES
(2, 'kos bosku 2', 'jln terbang menggapai bintang', 'pelembang', 20000, 'VIP', 4, 'lengkap', '0', '1791383749_6ac658c523273.png', 'tersedia', '2026-10-07 21:35:49');

-- --------------------------------------------------------

--
-- Table structure for table `tb_kos_foto`
--

CREATE TABLE `tb_kos_foto` (
  `id_foto` int(11) NOT NULL,
  `id_kos` int(11) NOT NULL,
  `nama_foto` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_produk`
--

CREATE TABLE `tb_produk` (
  `id_produk` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `spesifikasi` text DEFAULT NULL,
  `satuan` varchar(50) DEFAULT NULL,
  `stok` int(11) DEFAULT 0,
  `status` enum('tersedia','habis') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_produk`
--

INSERT INTO `tb_produk` (`id_produk`, `id_kategori`, `nama_produk`, `foto`, `deskripsi`, `spesifikasi`, `satuan`, `stok`, `status`) VALUES
(1, 4, 'dawdw', '1791380439_6ac64bd727105.png', 'wdaw', 'wdaw', 'dwa', 1, 'tersedia'),
(2, 4, 'a', NULL, 'awda', 'awda', 'awdsa', 1, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `tb_quotation`
--

CREATE TABLE `tb_quotation` (
  `id_quotation` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `status` enum('menunggu','diproses','selesai') DEFAULT 'menunggu',
  `catatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_quotation_detail`
--

CREATE TABLE `tb_quotation_detail` (
  `id_detail` int(11) NOT NULL,
  `id_quotation` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_sewa`
--

CREATE TABLE `tb_sewa` (
  `id_sewa` int(11) NOT NULL,
  `id_kos` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal_pengajuan` datetime DEFAULT current_timestamp(),
  `tanggal_mulai` date DEFAULT NULL,
  `durasi` int(11) DEFAULT 1,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu','diproses','diterima','ditolak','selesai') DEFAULT 'menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_sewa`
--

INSERT INTO `tb_sewa` (`id_sewa`, `id_kos`, `id_user`, `tanggal_pengajuan`, `tanggal_mulai`, `durasi`, `catatan`, `status`) VALUES
(1, 2, 1, '2026-10-07 21:42:28', '2026-10-30', 1, '', 'ditolak'),
(2, 2, 5, '2026-10-08 00:13:14', '2026-10-10', 6, 'NYAMAN', 'diterima'),
(3, 2, 4, '2026-10-08 00:17:25', '2026-10-13', 6, 'penting kasur nya empuk aja mas', 'selesai'),
(4, 2, 1, '2026-10-08 00:39:11', '2026-10-09', 1, 'siap kan handuk bersih', 'diproses');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('admin','pelanggan') DEFAULT 'pelanggan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `nama`, `email`, `password`, `no_hp`, `alamat`, `role`) VALUES
(1, 'jeki', 'jeki@gmail.com', '$2y$10$h/dMojc71RZJtAsJ8V.SjuAuuXS1lPUAPqa1okNl34xivQvCgWKVO', '082290255406', 'jln.kasnariansyah', 'pelanggan'),
(3, 'Administrator', 'admin@supplyfinder.com', '$2y$10$/2hmqe/CyE1zJhvRh7G8xOFIjJrgNqXyOmjxcOiu1kVPJ85hwVGZ6', '08123456789', 'Palembang', 'admin'),
(4, 'usuf', 'usuf@gmail.com', '$2y$10$0k2cKUmdRfd/H//jHVzFbOKu7gdAMJ20qV.zOBeDqM5ua30RFjJFi', '081368632201', 'jln.talang kelapa', 'pelanggan'),
(5, 'piki', 'piki@gmail.com', '$2y$10$suz/mtb5muIUhL0EbfoFueR9ufwuLXc8VLyB0Z8FphnTUTvi/DapW', '082290255403', 'jln.borang', 'pelanggan'),
(6, 'jaka', 'jaka@gmail.com', '$2y$10$UTCk2deT93emlxLN4hp/zO3GiQiCF0zAWaGw1qpUtJun.rXv0e472', '082290255462', 'jln.jalan jalan', 'pelanggan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `tb_kos`
--
ALTER TABLE `tb_kos`
  ADD PRIMARY KEY (`id_kos`);

--
-- Indexes for table `tb_kos_foto`
--
ALTER TABLE `tb_kos_foto`
  ADD PRIMARY KEY (`id_foto`),
  ADD KEY `id_kos` (`id_kos`);

--
-- Indexes for table `tb_produk`
--
ALTER TABLE `tb_produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `tb_quotation`
--
ALTER TABLE `tb_quotation`
  ADD PRIMARY KEY (`id_quotation`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_quotation_detail`
--
ALTER TABLE `tb_quotation_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_quotation` (`id_quotation`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `tb_sewa`
--
ALTER TABLE `tb_sewa`
  ADD PRIMARY KEY (`id_sewa`),
  ADD KEY `id_kos` (`id_kos`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_kategori`
--
ALTER TABLE `tb_kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_kos`
--
ALTER TABLE `tb_kos`
  MODIFY `id_kos` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_kos_foto`
--
ALTER TABLE `tb_kos_foto`
  MODIFY `id_foto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_produk`
--
ALTER TABLE `tb_produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tb_quotation`
--
ALTER TABLE `tb_quotation`
  MODIFY `id_quotation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_quotation_detail`
--
ALTER TABLE `tb_quotation_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_sewa`
--
ALTER TABLE `tb_sewa`
  MODIFY `id_sewa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_kos_foto`
--
ALTER TABLE `tb_kos_foto`
  ADD CONSTRAINT `tb_kos_foto_ibfk_1` FOREIGN KEY (`id_kos`) REFERENCES `tb_kos` (`id_kos`) ON DELETE CASCADE;

--
-- Constraints for table `tb_produk`
--
ALTER TABLE `tb_produk`
  ADD CONSTRAINT `tb_produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `tb_kategori` (`id_kategori`);

--
-- Constraints for table `tb_quotation`
--
ALTER TABLE `tb_quotation`
  ADD CONSTRAINT `tb_quotation_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`);

--
-- Constraints for table `tb_quotation_detail`
--
ALTER TABLE `tb_quotation_detail`
  ADD CONSTRAINT `tb_quotation_detail_ibfk_1` FOREIGN KEY (`id_quotation`) REFERENCES `tb_quotation` (`id_quotation`),
  ADD CONSTRAINT `tb_quotation_detail_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `tb_produk` (`id_produk`);

--
-- Constraints for table `tb_sewa`
--
ALTER TABLE `tb_sewa`
  ADD CONSTRAINT `tb_sewa_ibfk_1` FOREIGN KEY (`id_kos`) REFERENCES `tb_kos` (`id_kos`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_sewa_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
