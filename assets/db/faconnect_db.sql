-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2025 at 08:16 AM
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
-- Database: `faconnect_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `dob` date DEFAULT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `acc_type` enum('Super Admin','Admin') NOT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `phone_no`, `email`, `acc_type`, `picture_path`, `date_created`) VALUES
(1, '1817482100', 'Jerome Mark', 'Alcaraz', 'Gerong', NULL, 'Male', '2002-12-22', '09554708183', 'gerongjeromemark@gmail.com', 'Super Admin', '../profile/Jerome-Pfp.jpg', '2024-12-06 02:17:44');

-- --------------------------------------------------------

--
-- Table structure for table `admin_accounts`
--

CREATE TABLE `admin_accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_accounts`
--

INSERT INTO `admin_accounts` (`account_id`, `user_id`, `rfid_no`, `password`, `date_created`) VALUES
(1, 1, '1817482100', '$2y$10$C7wBDgx8UT8FKPvsCVuvAuPr6SLIcM2BTvuqMIPTOgMd5nSYS24W.', '2024-12-06 04:03:55');

-- --------------------------------------------------------

--
-- Table structure for table `admin_addresses`
--

CREATE TABLE `admin_addresses` (
  `address_id` int(11) NOT NULL,
  `region` varchar(150) DEFAULT NULL,
  `province` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `brgy` varchar(150) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `address_dtl` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rfid_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_addresses`
--

INSERT INTO `admin_addresses` (`address_id`, `region`, `province`, `city`, `brgy`, `zip_code`, `address_dtl`, `user_id`, `rfid_no`) VALUES
(1, 'Metro Manila', 'Metro Manila', 'Quezon City', 'San Agustin', '1117', '07 Osmena St., T.S Cruz Subdivision', 1, '1817482100');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `attendance_records_id` int(11) NOT NULL,
  `attendance_id` int(11) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `rfid_no` varchar(10) NOT NULL,
  `department` varchar(250) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `position` varchar(50) NOT NULL,
  `time_in` varchar(100) NOT NULL,
  `time_out` varchar(100) DEFAULT NULL,
  `date_logged` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`attendance_records_id`, `attendance_id`, `picture_path`, `fname`, `mname`, `lname`, `suffix`, `rfid_no`, `department`, `dept_id`, `position`, `time_in`, `time_out`, `date_logged`) VALUES
(13, 32, '../profile/Veritas_Ratio.jpg', 'Veritas', '', 'Ratio', '', '2692489172', 'Information Technology', 0, 'Professor', '12:53:12 AM', '12:53:23 AM', '2024-04-23'),
(14, 33, '../profile/Veritas_Ratio.jpg', 'Veritas', '', 'Ratio', '', '2692489172', 'Information Technology', 0, 'Professor', '12:10:14 PM', '12:11:18 PM', '2024-09-23');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_today`
--

CREATE TABLE `attendance_today` (
  `attd_id` int(11) NOT NULL,
  `attd_ref` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late') NOT NULL,
  `date_logged` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `building`
--

CREATE TABLE `building` (
  `bldg_id` int(11) NOT NULL,
  `bldg_name` varchar(255) NOT NULL,
  `bldg_description` varchar(255) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `building`
--

INSERT INTO `building` (`bldg_id`, `bldg_name`, `bldg_description`, `date_created`) VALUES
(1, 'Bldg-1A', 'Academic Building', '2024-04-13'),
(2, 'Bldg-2B', 'Admin Building', '2024-04-13'),
(3, 'Bldg-3C', 'TechVoc Building', '2024-04-13'),
(11, 'Bldg-3D', 'Hatdugan Building', '2024-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `buildings`
--

CREATE TABLE `buildings` (
  `id` int(11) NOT NULL,
  `building_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buildings`
--

INSERT INTO `buildings` (`id`, `building_name`) VALUES
(1, 'Main Building');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `class_name`, `department`, `dept_id`, `date_created`) VALUES
(1, 'SBIT-3B', 'Information Technology', 27, '2024-04-08'),
(2, 'SBIT-3A', 'Information Technology', 27, '2024-04-08'),
(3, 'SBIT-3C', 'Information Technology', 27, '2024-04-08'),
(4, 'SBIT-3D', 'Information Technology', 27, '2024-04-08'),
(8, 'SBIT-3E', 'Information Technology', 27, '2024-04-14'),
(10, 'SBCS-3A', 'Information Technology', 27, '2024-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `dean`
--

CREATE TABLE `dean` (
  `prof_id` int(11) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `rfid_no` varchar(10) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `sex` varchar(20) NOT NULL,
  `dob` varchar(50) NOT NULL,
  `house_no` varchar(20) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `subd` varchar(50) DEFAULT NULL,
  `brgy` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `phone_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `department` varchar(250) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dean`
--

INSERT INTO `dean` (`prof_id`, `picture_path`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `house_no`, `street`, `subd`, `brgy`, `city`, `province`, `zip_code`, `phone_no`, `email`, `position`, `dept_id`, `department`, `date_created`) VALUES
(20, '\n../profile/Prof.jpg', '2089479561', 'Welt', '', 'Yang', '', 'Male', '1995-07-28', '16', 'Astral', 'Express', 'Station', 'Alaminos', 'None', '2323', '09790873265', 'welt.yang@gmail.com', 'Dean', 0, '', '2024-04-20');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `dept_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`dept_id`, `department_name`) VALUES
(1, 'Information Technology'),
(2, 'Information System'),
(3, 'Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `dob` date DEFAULT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `acc_type` enum('Professor','Program Chair','Dean') NOT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`user_id`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `phone_no`, `email`, `acc_type`, `picture_path`, `date_created`) VALUES
(1, '2089479561', 'Harold ', 'R', 'Lucero', NULL, 'Male', '1995-07-28', '09790873265', 'harold_ramirez_lucero@yahoo.com', 'Dean', '\n../profile/Prof.png', '2024-12-05 09:12:19'),
(2, '2086577801', 'Machael ', 'C', 'Lucero', NULL, 'Male', '1990-09-11', '09987637621', 'michael_lucero@gmail.com', 'Program Chair', '\n../profile/Prof.png', '2024-12-05 09:16:42'),
(3, '2692489172', 'Roy Rouie ', 'S', 'Arsenal', NULL, 'Male', '1990-03-17', '09455470818', 'roy_rouie_arsenal@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:16:42'),
(4, '2691583412', 'Eliseo', NULL, 'Candaza', 'Jr.', 'Male', '1990-04-26', '09476041745', 'eliseo_candaza@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:21:26'),
(5, '2094052233', 'Valeree Maree', NULL, 'Asuncion', NULL, 'Female', '1989-05-20', '09552371839', 'valeree_maree_asuncion@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:21:26'),
(6, '2080617945', 'Noel', 'C', 'Gagolingan', NULL, 'Male', '1990-08-18', '09543242343', 'noel_gagolingan@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:29:20'),
(7, '2085309865', 'Andy', NULL, 'Gonzales', NULL, 'Male', '1990-10-17', '09287646382', 'andy_gonzales@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:30:29'),
(8, '0166064034', 'Richard', NULL, 'Williams', NULL, 'Male', '1989-11-08', '09458772313', 'richard_micheal@gmail.com', 'Professor', '\n../profile/Prof.png', '2024-12-05 09:31:27'),
(9, '9716824297', 'Abegail ', 'V', 'Fulong', NULL, 'Female', '1990-09-11', '09321876221', 'abegail_fulong@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 00:57:17'),
(10, '5244084191', 'Mary Cristine', '', 'Macaraeg', NULL, 'Female', '1990-09-11', '09321876224', 'mary_macaraeg@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 00:58:14'),
(11, '0334380521', 'Kristine', NULL, 'Nomabilis', NULL, 'Female', '1990-09-11', '09321876221', 'kristine_nomabilis@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 00:58:55'),
(12, '7402133740', 'Rollie', NULL, 'Pineda', NULL, 'Male', '1990-09-11', '09321876232', 'rollie_pineda@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 00:59:53'),
(13, '2388993990', 'Audie Alejandro', NULL, 'Tadena', NULL, 'Male', '1990-09-11', '09321876123', 'audie_alejandro@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 01:00:39'),
(14, '6644730915', 'Edgardo', NULL, 'Velasco', NULL, 'Male', '1990-09-11', '09321876789', 'edgardo_velasco@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 01:01:40'),
(15, '6489525071', 'Sil William', '', 'Carullo', NULL, 'Male', '1990-09-11', '09321876456', 'sil_william_carullo@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 01:08:10'),
(16, '4974986662', 'Guillermo', NULL, 'Durana', NULL, 'Male', '1990-09-11', '09321878765', 'guillermo_durana@gmail.com', 'Professor', '\r\n../profile/Prof.png', '2024-12-08 01:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_accounts`
--

CREATE TABLE `faculty_accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_addresses`
--

CREATE TABLE `faculty_addresses` (
  `address_id` int(11) NOT NULL,
  `region` varchar(150) DEFAULT NULL,
  `province` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `brgy` varchar(150) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `address_dtl` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rfid_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `professor`
--

CREATE TABLE `professor` (
  `prof_id` int(11) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `rfid_no` varchar(10) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `sex` varchar(20) NOT NULL,
  `dob` varchar(50) NOT NULL,
  `house_no` varchar(20) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `subd` varchar(50) DEFAULT NULL,
  `brgy` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `phone_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `department` varchar(250) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professor`
--

INSERT INTO `professor` (`prof_id`, `picture_path`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `house_no`, `street`, `subd`, `brgy`, `city`, `province`, `zip_code`, `phone_no`, `email`, `position`, `dept_id`, `department`, `date_created`) VALUES
(13, '\n../profile/Prof.jpg', '2692489172', 'Veritas', '', 'Ratio', '', 'Male', '1990-03-17', '13', 'Makisig', 'Intelligentsia Guild', 'BGC', 'Alaminos', 'None', '2468', '09455470818', 'veritas.ratio@gmail.com', 'Professor', 27, 'Information Technology', '2024-04-01'),
(14, '\n../profile/Prof.jpg', '2691583412', 'Serval', '', 'Landau', '', 'Female', '1990-04-26', '7', 'Roxas', 'Belobog', 'Llano', 'Alaminos', 'None', '1425', '09476041745', 'serval.landau@gmail.com', 'Professor', 28, 'Information System', '2024-04-02'),
(16, '\n../profile/Prof.jpg', '2094052233', 'Jing', '', 'Yuan', '', 'Male', '1989-05-20', '44', 'Xianzhou', 'Luofu', 'Nagkaisang Nayon', 'Alaminos', 'None', '1113', '09552371839', 'jing.yuan@gmail.com', 'Professor', 30, 'Computer Science', '2024-04-02'),
(23, '\n../profile/Prof.jpg', '2080617945', 'Shin', '', 'Yukong', '', 'Female', '1990-08-18', '9', 'Xianzhou', 'Loufu', 'Quintet', 'Alaminos', 'None', '4141', '09543242343', 'madam.yukong@gmail.com', 'Professor', 30, 'Computer Science', '2024-04-06'),
(26, '\n../profile/Prof.jpg', '2085309865', 'Himeko', '', 'Astral', '', 'Female', '1990-10-17', '16', 'Astral', 'Express', 'Nameless', 'Alaminos', 'None', '4026', '09287646382', 'himeko.astral@gmail.com', 'Professor', 27, 'Information Technology', '2024-04-06'),
(27, '\n../profile/Prof.jpg', '0166064034', 'Kafka', '', 'Williams', '', 'Female', '1989-11-08', '5', 'Pteruges', 'Stellaron', 'Huntaria', 'Alaminos', 'None', '9832', '09458772313', 'kafcat.williams@gmail.com', 'Professor', 28, 'Information System', '2024-04-06');

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `uploaded_at` date NOT NULL DEFAULT current_timestamp(),
  `fullName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `picture_path`, `uploaded_at`, `fullName`) VALUES
(11, '../profile/Kiko_Pfp.png', '2024-03-30', 'Jerome'),
(12, '../profile/Jane_pfp.jpg', '2024-03-30', 'Jerome'),
(13, '../profile/Kiko_Pfp.png', '2024-03-30', 'Jerome Mark Alcaraz Gerong'),
(14, '../profile/Jane_pfp.jpg', '2024-03-31', ''),
(15, '../profile/Rendon-Pfp.png', '2024-03-31', '1321'),
(16, '../profile/Mau-Pfp - Copy.jpg', '2024-03-31', ''),
(17, '../profile/Serval_Landau.jpg', '2024-04-02', 'Serval Landau');

-- --------------------------------------------------------

--
-- Table structure for table `program_chair`
--

CREATE TABLE `program_chair` (
  `program_chair_id` int(11) NOT NULL,
  `picture_path` varchar(255) NOT NULL,
  `rfid_no` varchar(10) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `lname` varchar(100) NOT NULL,
  `suffix` varchar(50) DEFAULT NULL,
  `sex` varchar(20) NOT NULL,
  `dob` varchar(50) NOT NULL,
  `house_no` varchar(20) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `subd` varchar(50) DEFAULT NULL,
  `brgy` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `province` varchar(50) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `phone_no` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `position` varchar(50) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `department` varchar(250) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_chair`
--

INSERT INTO `program_chair` (`program_chair_id`, `picture_path`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `house_no`, `street`, `subd`, `brgy`, `city`, `province`, `zip_code`, `phone_no`, `email`, `position`, `dept_id`, `department`, `date_created`) VALUES
(34, '\n../profile/Prof.jpg', '2086577801', 'Jing', '', 'Liu', '', 'Female', '1990-09-11', '2', 'Xianzhou', 'Cangcheng', 'High Cloud Quintet', 'Alaminos', 'None', '1234', '09987637621', 'jing.liu@gmail.com', 'Program Chair', 27, 'Information Technology', '2024-04-07'),
(36, '\n../profile/Prof.jpg', '2082906329', 'Ruan', '', 'Mei', '', 'Female', '2002-12-22', '44', 'Herta', 'Space', 'Station', 'Alaminos', 'None', '3213', '09783512768', 'ruan.mei@gmail.com', 'Program Chair', 28, 'Information System', '2024-04-23'),
(37, '\n../profile/Prof.jpg', '2095141273', 'Black', '', 'Swan', '', 'Female', '2002-11-08', '1', 'Penacony', 'Greenfield III', 'High Cloud Quintet', 'Alaminos', 'None', '4323', '09963728615', 'black.swan@gmail.com', 'Program Chair', 30, 'Computer Science', '2024-04-23');

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `room_id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `room_description` varchar(255) NOT NULL,
  `bldg_id` int(11) NOT NULL,
  `bldg_name` varchar(255) NOT NULL,
  `bldg_description` varchar(255) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room`
--

INSERT INTO `room` (`room_id`, `room_name`, `room_description`, `bldg_id`, `bldg_name`, `bldg_description`, `date_created`) VALUES
(6, 'AB 501', '5th Floor', 1, 'Bldg-1A', 'Academic Building', '2024-04-14'),
(7, 'ADM 101', '1st Floor', 2, 'Bldg-2B', 'Admin Building', '2024-04-14'),
(8, 'ADM 201', '2nd Floor', 2, 'Bldg-2B', 'Admin Building', '2024-04-14'),
(9, 'ADM 301', '3rd Floor', 2, 'Bldg-2B', 'Admin Building', '2024-04-14'),
(10, 'ADM 401', '4th Floor', 2, 'Bldg-2B', 'Admin Building', '2024-04-14'),
(11, 'ADM 501', '5th Floor', 2, 'Bldg-2B', 'Admin Building', '2024-04-14'),
(12, 'TV 101', '1st Floor', 3, 'Bldg-3C', 'TechVoc Building', '2024-04-14'),
(13, 'TV 201', '2nd Floor', 3, 'Bldg-3C', 'TechVoc Building', '2024-04-14'),
(14, 'TV 301', '3rd Floor', 3, 'Bldg-3C', 'TechVoc Building', '2024-04-14'),
(15, 'TV 401', '4th Floor', 3, 'Bldg-3C', 'TechVoc Building', '2024-04-14'),
(16, 'TV 501', '5th Floor', 3, 'Bldg-3C', 'TechVoc Building', '2024-04-14'),
(18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', '2024-04-21'),
(19, 'AB 201', '2nd Floor', 1, 'Bldg-1A', 'Academic Building', '2024-04-21'),
(20, 'AB 301', '3rd Floor', 1, 'Bldg-1A', 'Academic Building', '2024-04-21'),
(21, 'AB 401', '4th Floor', 1, 'Bldg-1A', 'Academic Building', '2024-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `building_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `building_id`) VALUES
(1, 'Lab-1', 1),
(2, 'Lab-2', 1),
(3, 'Lab-3', 1),
(4, 'Lab-4', 1),
(5, 'Lab-5', 1),
(6, 'PB204', 1),
(7, 'PB205', 1),
(8, 'PB208', 1),
(9, 'PB206', 1),
(10, 'PB207', 1),
(11, 'Net Lab', 1),
(12, 'Tech Room', 1),
(13, 'GYM', 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `sched_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `rfid_no` varchar(10) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `room_name` varchar(255) DEFAULT 'None',
  `room_description` varchar(255) DEFAULT NULL,
  `bldg_id` int(11) DEFAULT NULL,
  `bldg_name` varchar(255) DEFAULT NULL,
  `bldg_description` varchar(255) DEFAULT NULL,
  `class_id` int(111) DEFAULT NULL,
  `class_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `subject_name` varchar(255) DEFAULT 'Other Agendas',
  `description` varchar(255) DEFAULT 'None',
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`sched_id`, `type`, `start_date`, `end_date`, `start_time`, `end_time`, `rfid_no`, `fname`, `mname`, `lname`, `suffix`, `room_id`, `room_name`, `room_description`, `bldg_id`, `bldg_name`, `bldg_description`, `class_id`, `class_name`, `department`, `dept_id`, `subject_id`, `subject_name`, `description`, `date_created`) VALUES
(56, 'Class', '2024-04-22', '2024-04-22', '07:00:00', '10:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 14, 'TV 301', '3rd Floor', 3, 'Bldg-3C', 'TechVoc Building', 4, 'SBIT-3D', 'Information Technology', 27, 6, 'SIA102', 'System Integration and Architecture 2', '2024-04-22 20:54:27'),
(58, 'V A C A N T', '2024-04-22', '2024-04-22', '10:00:00', '11:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 'Other Agendas', 'None', '2024-04-22 21:14:58'),
(59, 'Class', '2024-04-22', '2024-04-22', '11:00:00', '13:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 6, 'AB 501', '5th Floor', 1, 'Bldg-1A', 'Academic Building', 4, 'SBIT-3D', 'Information Technology', 27, 6, 'SIA102', 'System Integration and Architecture 2', '2024-04-22 21:16:48'),
(72, 'Class', '2024-10-20', '2024-10-20', '17:10:00', '18:10:00', '2692489172', 'Veritas', '', 'Ratio', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-10-20 17:10:58'),
(73, 'Class', '2024-11-24', '2024-11-25', '14:00:00', '15:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:17:53'),
(74, 'Class', '2024-01-01', '2024-01-01', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:19:56'),
(75, 'Class', '2024-01-08', '2024-01-08', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:19:56'),
(76, 'Class', '2024-01-15', '2024-01-15', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:19:56'),
(77, 'Class', '2024-01-22', '2024-01-22', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:19:56'),
(78, 'Class', '2024-01-29', '2024-01-29', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:19:56'),
(79, 'Class', '2024-02-05', '2024-02-05', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:26:39'),
(80, 'Class', '2024-02-12', '2024-02-12', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:26:39'),
(81, 'Class', '2024-02-19', '2024-02-19', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:26:39'),
(82, 'Class', '2024-02-26', '2024-02-26', '13:00:00', '14:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:26:39'),
(83, 'Class', '2024-11-24', '2024-11-24', '01:00:00', '02:00:00', '2095141273', 'Black', '', 'Swan', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-11-24 13:28:00'),
(84, 'Class', '2024-12-06', '2024-12-06', '11:00:00', '13:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 18, 'AB 101', '1st Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-12-06 10:20:07'),
(85, 'V A C A N T', '2024-12-06', '2024-12-06', '13:00:00', '14:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 'Other Agendas', 'None', '2024-12-06 10:21:36'),
(86, 'Class', '2024-12-06', '2024-12-06', '14:00:00', '16:00:00', '2692489172', 'Veritas', '', 'Ratio', '', 6, 'AB 501', '5th Floor', 1, 'Bldg-1A', 'Academic Building', 10, 'SBCS-3A', 'Information Technology', 27, 11, 'AL101', 'Algorithm and Complexity', '2024-12-06 10:22:24');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `dept_id`, `date_created`) VALUES
(1, 'BSIT 1-1', 1, '2025-01-02 20:27:16'),
(2, 'BSIT 1-2', 1, '2025-01-02 20:27:16'),
(3, 'BSIT 1-3', 1, '2025-01-02 20:27:16'),
(4, 'BSIT 1-4', 1, '2025-01-02 20:27:16'),
(5, 'BSIT 1-5', 1, '2025-01-02 20:27:16'),
(6, 'BSIT 1-6', 1, '2025-01-02 20:27:16'),
(7, 'BSIT 2-1', 1, '2025-01-02 20:27:16'),
(8, 'BSIT 2-2', 1, '2025-01-02 20:27:16'),
(9, 'BSIT 2-3', 1, '2025-01-02 20:27:16'),
(10, 'BSIT 2-4', 1, '2025-01-02 20:27:16'),
(11, 'BSIT 3-1', 1, '2025-01-02 20:27:16'),
(12, 'BSIT 3-2', 1, '2025-01-02 20:27:16'),
(13, 'BSIT 4-1', 1, '2025-01-02 20:27:16'),
(14, 'BSIT 4-2', 1, '2025-01-02 20:27:16'),
(15, 'BSIT 4-3', 1, '2025-01-02 20:27:16'),
(16, 'BSIT 4-4', 1, '2025-01-02 20:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) NOT NULL,
  `suffix` varchar(10) DEFAULT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `dob` date DEFAULT NULL,
  `phone_no` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `acc_type` enum('Student') NOT NULL,
  `picture_path` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`user_id`, `rfid_no`, `student_number`, `fname`, `mname`, `lname`, `suffix`, `sex`, `dob`, `phone_no`, `email`, `acc_type`, `picture_path`, `date_created`, `section_id`) VALUES
(1, '2085309865', '21-2244', 'Juan', '', 'Dela Cruz', NULL, 'Male', NULL, NULL, 'delacruz.juan.01302002@gmail.com', 'Student', NULL, '2024-11-29 17:43:10', 1);

-- --------------------------------------------------------

--
-- Table structure for table `student_accounts`
--

CREATE TABLE `student_accounts` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_addresses`
--

CREATE TABLE `student_addresses` (
  `address_id` int(11) NOT NULL,
  `region` varchar(150) DEFAULT NULL,
  `province` varchar(150) DEFAULT NULL,
  `city` varchar(150) DEFAULT NULL,
  `brgy` varchar(150) DEFAULT NULL,
  `zip_code` varchar(10) DEFAULT NULL,
  `address_dtl` varchar(500) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rfid_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_description` text NOT NULL,
  `for_year` enum('1','2','3','4') NOT NULL,
  `cover_picture_path` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_code`, `subject_description`, `for_year`, `cover_picture_path`, `date_created`) VALUES
(1, 'COMP1', 'Introduction to Computing', '1', NULL, '2024-12-08 09:39:21'),
(2, 'CPRO1', 'Computer Programming 1', '1', NULL, '2024-12-08 09:39:21'),
(3, 'GE01', 'Understanding the Self', '1', NULL, '2024-12-08 09:39:21'),
(4, 'GEE01', 'Environmental Science', '1', NULL, '2024-12-08 09:39:21'),
(5, 'GEE03', 'Religion and Religious Spirituality and Experiences', '1', NULL, '2024-12-08 09:39:21'),
(6, 'GE04', 'Mathematics in the Modern World', '1', NULL, '2024-12-08 09:39:21'),
(7, 'NSTP1', 'National Service Training Program 1', '1', NULL, '2024-12-08 09:39:21'),
(8, 'PE1', 'Self Testing Activities', '1', NULL, '2024-12-08 09:39:21'),
(9, 'HCI', 'Human Computer Interaction', '2', NULL, '2024-12-08 09:40:13'),
(10, 'INFOMAN', 'Information Management', '2', NULL, '2024-12-08 09:40:13'),
(11, 'NET1', 'Data Communication and Networking 1', '2', NULL, '2024-12-08 09:40:13'),
(12, 'ACCTG', 'Accounting Fundamentals', '2', NULL, '2024-12-08 09:40:13'),
(13, 'GE03', 'Contemporary World', '2', NULL, '2024-12-08 09:40:13'),
(14, 'GE05', 'Purposive Communication', '2', NULL, '2024-12-08 09:40:13'),
(15, 'GE07', 'Science, Technology, and Society', '2', NULL, '2024-12-08 09:40:13'),
(16, 'PEO3', 'Individual and Dual Sports', '2', NULL, '2024-12-08 09:40:13'),
(17, 'DBSYS', 'Fundamentals of Database Systems', '3', NULL, '2024-12-08 09:41:04'),
(18, 'CAP0', 'Capstone Project Proposal', '3', NULL, '2024-12-08 09:41:04'),
(19, 'MMEDIA', 'Multimedia', '3', NULL, '2024-12-08 09:41:04'),
(20, 'ITP', 'Integrative Programming Technologies', '3', NULL, '2024-12-08 09:41:04'),
(21, 'QM', 'Quantitative Methods', '3', NULL, '2024-12-08 09:41:04'),
(22, 'GE08', 'Ethics for IT', '3', NULL, '2024-12-08 09:41:04'),
(23, 'ITELEC2', 'IT Electives', '3', NULL, '2024-12-08 09:41:04'),
(24, 'CAP2', 'Capstone Project 2', '4', NULL, '2024-12-08 09:42:10'),
(25, 'SAM', 'System Administration and Maintenance', '4', NULL, '2024-12-08 09:42:10'),
(26, 'INFOSYS2', 'Information Assurance and Security 2', '4', NULL, '2024-12-08 09:42:10'),
(27, 'ITELEC4', 'IT Elective 4', '4', NULL, '2024-12-08 09:42:10');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`subject_id`, `subject_name`, `description`, `date_created`) VALUES
(0, 'Other Agendas', 'None', '2024-04-16'),
(1, 'CC106', 'Application Development and Emerging Technologies', '2024-04-13'),
(4, 'MS102', 'Quantitative Research', '2024-04-14'),
(6, 'SIA102', 'System Integration and Architecture 2', '2024-04-14'),
(7, 'HUM2', 'Ethics', '2024-04-14'),
(8, 'IAS101', 'Fundamental of Information Assurance and Security 1', '2024-04-14'),
(11, 'AL101', 'Algorithm and Complexity', '2024-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `user_department`
--

CREATE TABLE `user_department` (
  `user_id` int(11) NOT NULL,
  `rfid_no` varchar(50) NOT NULL,
  `dept_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_department`
--

INSERT INTO `user_department` (`user_id`, `rfid_no`, `dept_id`) VALUES
(1, '2089479561', 1),
(2, '2086577801', 1),
(3, '2692489172', 1),
(4, '2691583412', 1),
(5, '2094052233', 1),
(6, '2080617945', 1),
(7, '2085309865', 1),
(8, '0166064034', 1),
(9, '9716824297', 1),
(10, '5244084191', 1),
(11, '0334380521', 1),
(12, '7402133740', 1),
(13, '2388993990', 1),
(14, '6644730915', 1),
(15, '6489525071', 1),
(16, '4974986662', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`,`rfid_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `admin_addresses`
--
ALTER TABLE `admin_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`attendance_records_id`);

--
-- Indexes for table `attendance_today`
--
ALTER TABLE `attendance_today`
  ADD PRIMARY KEY (`attd_id`,`attd_ref`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `building`
--
ALTER TABLE `building`
  ADD PRIMARY KEY (`bldg_id`);

--
-- Indexes for table `buildings`
--
ALTER TABLE `buildings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `dean`
--
ALTER TABLE `dean`
  ADD PRIMARY KEY (`prof_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`dept_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`user_id`,`rfid_no`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `faculty_accounts`
--
ALTER TABLE `faculty_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `faculty_addresses`
--
ALTER TABLE `faculty_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `professor`
--
ALTER TABLE `professor`
  ADD PRIMARY KEY (`prof_id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `program_chair`
--
ALTER TABLE `program_chair`
  ADD PRIMARY KEY (`program_chair_id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `building_id` (`building_id`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`sched_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`user_id`,`rfid_no`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `student_accounts`
--
ALTER TABLE `student_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `student_addresses`
--
ALTER TABLE `student_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`,`rfid_no`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`subject_id`);

--
-- Indexes for table `user_department`
--
ALTER TABLE `user_department`
  ADD PRIMARY KEY (`user_id`,`rfid_no`,`dept_id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_addresses`
--
ALTER TABLE `admin_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `attendance_records_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `building`
--
ALTER TABLE `building`
  MODIFY `bldg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `buildings`
--
ALTER TABLE `buildings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `dean`
--
ALTER TABLE `dean`
  MODIFY `prof_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `faculty_accounts`
--
ALTER TABLE `faculty_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty_addresses`
--
ALTER TABLE `faculty_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `professor`
--
ALTER TABLE `professor`
  MODIFY `prof_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `program_chair`
--
ALTER TABLE `program_chair`
  MODIFY `program_chair_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `sched_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_accounts`
--
ALTER TABLE `student_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_addresses`
--
ALTER TABLE `student_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_accounts`
--
ALTER TABLE `admin_accounts`
  ADD CONSTRAINT `admin_accounts_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `admin` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `admin_addresses`
--
ALTER TABLE `admin_addresses`
  ADD CONSTRAINT `admin_addresses_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `admin` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `attendance_today`
--
ALTER TABLE `attendance_today`
  ADD CONSTRAINT `attendance_today_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `faculty` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_accounts`
--
ALTER TABLE `faculty_accounts`
  ADD CONSTRAINT `faculty_accounts_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `faculty` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `faculty_addresses`
--
ALTER TABLE `faculty_addresses`
  ADD CONSTRAINT `faculty_addresses_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `faculty` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`building_id`) REFERENCES `buildings` (`id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `student_accounts`
--
ALTER TABLE `student_accounts`
  ADD CONSTRAINT `student_accounts_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `students` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_addresses`
--
ALTER TABLE `student_addresses`
  ADD CONSTRAINT `student_addresses_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `students` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_department`
--
ALTER TABLE `user_department`
  ADD CONSTRAINT `user_department_ibfk_1` FOREIGN KEY (`user_id`,`rfid_no`) REFERENCES `faculty` (`user_id`, `rfid_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_department_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `department` (`dept_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
