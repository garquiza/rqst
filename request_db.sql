-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2025 at 08:22 PM
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
-- Database: `request_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `abstract_of_quotation`
--

CREATE TABLE `abstract_of_quotation` (
  `aoq_id` int(11) UNSIGNED NOT NULL,
  `rfq_id` int(11) UNSIGNED NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `project_location` varchar(255) NOT NULL,
  `implementing_office` varchar(255) NOT NULL,
  `approved_budget` decimal(10,2) NOT NULL,
  `prepared_by` varchar(100) NOT NULL,
  `verified_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_me` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `first_name`, `last_name`, `email`, `password`, `remember_me`, `created_at`, `updated_at`) VALUES
(2, 'Mariecris', 'Camasis', 'clientadmin@gmail.com', '$2y$10$v9ek5ePPadwXcHw2k8yVFe9dVV3TOqiHnwcmsGa06Fo1/fzUWvguq', 0, '2024-12-21 05:49:41', '2024-12-28 05:55:45'),
(5, 'group', 'email', 'emailgroup658@gmail.com', '$2y$10$.x4TrvlbBAR30Gg9Xac7wuZAOROpVa592dql/Aa6pIGhNI9SR6wye', 0, '2025-01-09 12:14:23', '2025-01-09 12:29:37');

-- --------------------------------------------------------

--
-- Table structure for table `app`
--

CREATE TABLE `app` (
  `app_id` int(11) NOT NULL,
  `ppmp_id` int(10) UNSIGNED DEFAULT NULL,
  `pmo_end_user` varchar(255) NOT NULL,
  `early_procurement_activity` enum('Yes','No') NOT NULL,
  `mode_of_procurement` varchar(255) NOT NULL,
  `advertisement_posting_ib_rei` date DEFAULT NULL,
  `submission_opening_bids` date DEFAULT NULL,
  `notice_of_award` date DEFAULT NULL,
  `contract_signing` date DEFAULT NULL,
  `source_of_funds` varchar(255) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `mooe` decimal(15,2) DEFAULT NULL,
  `co` decimal(15,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bac_users`
--

CREATE TABLE `bac_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_me` tinyint(1) DEFAULT 0,
  `status` enum('activate','disabled') DEFAULT 'disabled',
  `permission_access` set('APP','PPMP','PR','PMAF','RFQ','AOQ','RESO','NOA','NTP','PO','PMR') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bac_users`
--

INSERT INTO `bac_users` (`id`, `first_name`, `last_name`, `email`, `password`, `remember_me`, `status`, `permission_access`, `created_at`, `updated_at`) VALUES
(1, 'Jane', 'Doe', 'janedoe@gmail.com', '$2y$10$TqgaraDWb7I.YbGpJMenJ.LqimqIO23oHSqZoMn0DFkFCDf1CroH2', 1, 'activate', 'APP,PPMP,PR,PMAF,RFQ,AOQ,RESO,NOA,NTP,PO,PMR', '2024-12-21 05:50:39', '2025-01-02 18:06:28'),
(3, 'Robert', 'Muggah', 'robert@gmail.com', '$2y$10$vTT1taVx0q4G9Crub6iKSuWkyCUDN97jXurC.iTOgDeMW4koWDDLe', 0, 'activate', 'APP,PPMP,PR', '2025-01-02 13:49:27', '2025-01-02 17:31:18'),
(4, 'saudari', 'nasi', 'grace@sample.samp', '$2y$10$9pGlmQ1iLf9BolZAwpghVuOjC.KOepFqg7uKcLd7wwQ7/IbSj.T.W', 1, 'activate', 'PR', '2025-01-09 14:33:34', '2025-01-09 14:34:59');

-- --------------------------------------------------------

--
-- Table structure for table `budget_amount`
--

CREATE TABLE `budget_amount` (
  `id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_amount`
--

INSERT INTO `budget_amount` (`id`, `amount`, `created_at`) VALUES
(11, 0.00, '2025-01-09 19:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `budget_users`
--

CREATE TABLE `budget_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_me` tinyint(1) DEFAULT 0,
  `status` enum('activate','disabled') DEFAULT 'disabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budget_users`
--

INSERT INTO `budget_users` (`id`, `first_name`, `last_name`, `email`, `password`, `remember_me`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Shikhar', 'Upadhayay', 'shikhar@gmail.com', '$2y$10$TIJ9WohpDzdnA6ZXqglUgu68babF.8dTvlhSfkVqB9KhZpazhAVW2', 0, 'disabled', '2024-12-28 05:36:43', '2025-01-02 17:32:43'),
(3, 'Roanne', 'Marcelo', 'roanne@gmail.com', '$2y$10$hEbQpTCCNX5OU3qRiUYjr.9AjdWKY7ir6yEL3Y2rKUyCDeSomiCFK', 1, 'activate', '2025-01-02 18:09:07', '2025-01-02 18:12:10'),
(4, 'mochi', 'chi', 'monch@gmail.com', '$2y$10$XYbcjPtPoNmwdWm792SyueTiIUoTtjRVb3ltD9vEU7okyqIuMg37y', 0, 'activate', '2025-01-09 15:27:13', '2025-01-09 15:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `company_details`
--

CREATE TABLE `company_details` (
  `company_id` int(11) UNSIGNED NOT NULL,
  `aoq_id` int(11) UNSIGNED NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `bidders_specification` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `end_users`
--

CREATE TABLE `end_users` (
  `id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_me` tinyint(1) DEFAULT 0,
  `status` enum('activate','disabled') DEFAULT 'disabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `end_users`
--

INSERT INTO `end_users` (`id`, `first_name`, `last_name`, `email`, `password`, `remember_me`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Ace', 'Inovero', 'acejonas.inovero02@gmail.com', '$2y$10$Ln5uhOowjd08ZF.mJCKY0O56jBGHxXzICFmAnDCulEBloD1gyDZtW', 0, 'activate', '2025-01-03 17:42:46', '2025-01-03 17:42:55'),
(3, 'John', 'Wick', 'johnwick@gmail.com', '$2y$10$UnDzEbGiAsps5Hv0CvDqLOV02kH1SSLBektjFA4SirWPeTXXqvV2q', 0, 'activate', '2025-01-02 10:44:29', '2025-01-02 10:44:37'),
(4, 'Grace Nicole', 'Arquiza', 'gracearquiza21@gmail.com', '$2y$10$bp/fsxnk5kY2NxR67/HBduy9qp9BkWD8FMVxua/fGu9rLER6VybeS', 0, 'activate', '2025-01-09 12:17:02', '2025-01-09 12:20:35');

-- --------------------------------------------------------

--
-- Table structure for table `fund`
--

CREATE TABLE `fund` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `totalabc` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `savings` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fund`
--

INSERT INTO `fund` (`id`, `name`, `totalabc`, `amount`, `savings`) VALUES
(4, 'Cute', 31.00, 532.00, -501.00);

-- --------------------------------------------------------

--
-- Table structure for table `fund_sources`
--

CREATE TABLE `fund_sources` (
  `id` int(11) NOT NULL,
  `fund_source` varchar(255) NOT NULL,
  `total_abc` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `savings` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `history_logs`
--

CREATE TABLE `history_logs` (
  `id` int(11) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history_logs`
--

INSERT INTO `history_logs` (`id`, `title`, `description`, `created_at`) VALUES
(8, 'Purchase Request Updated', 'Purchase Request ID 13 has been updated to status \'Approved\' by Mariecris Camasis.', '2025-01-08 10:11:37');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `item_no` varchar(20) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `item_description` text NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `item_no`, `unit`, `item_name`, `item_description`, `unit_cost`, `created_at`, `updated_at`) VALUES
(2, 'IM-2025-01-03-619', 'Piece', 'None', 'None', 1000.00, '2025-01-03 18:22:18', '2025-01-03 18:22:18'),
(10, 'IM-2025-01-08-750', 'Pieces', 'Desktop Computer', 'PC', 100.00, '2025-01-08 10:03:05', '2025-01-08 10:03:05'),
(11, 'IM-2025-01-08-231', 'Stick', 'Cigar', 'Marlboro Puti', 250.00, '2025-01-08 10:03:35', '2025-01-08 10:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `it_is_hereby_statements`
--

CREATE TABLE `it_is_hereby_statements` (
  `it_is_hereby_id` int(11) UNSIGNED NOT NULL,
  `resolution_id` int(11) UNSIGNED NOT NULL,
  `statement` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notice_of_award`
--

CREATE TABLE `notice_of_award` (
  `noa_id` int(11) UNSIGNED NOT NULL,
  `authorized_representative` varchar(255) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `contract_amount_words` varchar(255) NOT NULL,
  `contract_amount_figures` decimal(10,2) NOT NULL,
  `philgeps_reference` varchar(255) NOT NULL,
  `rfq_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_of_award`
--

INSERT INTO `notice_of_award` (`noa_id`, `authorized_representative`, `designation`, `company_name`, `project_title`, `contract_amount_words`, `contract_amount_figures`, `philgeps_reference`, `rfq_id`, `created_at`, `signature`) VALUES
(24, 'mochi koy', 'CLA', 'hillx', 'Procuring of PC', 'tw', 42.00, '42', NULL, '2025-01-09 13:09:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `title`, `message`, `user_id`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 'New PPMP Created', 'A new PPMP titled \'Project#1\' has been created by Ace Inovero.', 2, 0, '2025-01-05 11:21:34', '2025-01-05 11:21:34'),
(2, 'New Purchase Request Submitted', 'A new purchase request (PR Number: PR-2025-01-318) has been submitted by Ace Inovero.', 2, 0, '2025-01-05 11:22:58', '2025-01-05 11:22:58'),
(3, 'New PPMP Created', 'A new PPMP titled \'Project#1\' has been created by Ace Inovero.', 2, 0, '2025-01-05 13:08:55', '2025-01-05 13:08:55'),
(22, 'New PPMP Created', 'A new PPMP titled \'Procuring of Aircon\' has been created by John Wick.', 3, 0, '2025-01-08 10:04:19', '2025-01-08 10:04:19'),
(23, 'New Purchase Request Submitted', 'A new purchase request (PR Number: PR-2025-01-112) has been submitted by John Wick.', 3, 0, '2025-01-08 10:08:12', '2025-01-08 10:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `pmaf`
--

CREATE TABLE `pmaf` (
  `id` int(11) NOT NULL,
  `modality` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`modality`)),
  `project_title` int(11) UNSIGNED DEFAULT NULL,
  `fund` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`fund`)),
  `mooe_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`mooe_items`)),
  `co_amount` decimal(10,2) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pmaf`
--

INSERT INTO `pmaf` (`id`, `modality`, `project_title`, `fund`, `mooe_items`, `co_amount`, `submitted_at`, `created_at`, `updated_at`, `signature`) VALUES
(35, '\"[\\\"Shopping (Section 52.1.b)\\\"]\"', 1, '\"[\\\"GoP\\\\n                Delete\\\",\\\"GoP\\\\n                Delete\\\"]\"', '\"[\\\"gsa\\\\n                Delete\\\",\\\"gsa\\\\n                Delete\\\"]\"', 42.00, '2025-01-09 12:46:44', '2025-01-09 20:46:44', '2025-01-09 20:46:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ppmp_form`
--

CREATE TABLE ppmp_form (
  ppmp_form_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  year varchar(4) NOT NULL,
  code varchar(100) NOT NULL,
  general_description text NOT NULL,
  quantity_size varchar(100) NOT NULL,
  estimated_budget decimal(15,2) NOT NULL,
  schedule longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(schedule)),
  ppmp_id int(11) UNSIGNED NOT NULL,
  mode_of_procurement varchar(255) NOT NULL,
  unit_measurement varchar(100) NOT NULL,
  unit_cost decimal(15,2) NOT NULL,
  date_created timestamp NOT NULL DEFAULT current_timestamp(),
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (ppmp_form_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------

--
-- Table structure for table `ppmp_list`
--

CREATE TABLE `ppmp_list` (
  `ppmp_id` int(11) UNSIGNED NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `approver` varchar(100) DEFAULT 'pending',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_bound` year(4) DEFAULT NULL,
  `status` enum('approved','pending','rejected','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ppmp_list`
--

INSERT INTO `ppmp_list` (`ppmp_id`, `project_title`, `user_id`, `approver`, `date_created`, `status`, `created_at`, `updated_at`, `signature`) VALUES
(1, 'Project#1', 2, 'Donut Doe', '2025-01-05 13:08:55', 'approved', '2025-01-05 13:08:55', '2025-01-05 13:09:38', 0x524946461a450000574542505650384c0e4500002f2505f71047272420fc1fea8609aa4020d91f748d0402c9fea06b2410204469f27f20e360d2978aa3b86d1b47da7fece47a7d46c404003827f855195bc30d1bf7e44c6e89636d5ccdfc97a47378fcffffb654feffeeaf9772282dc5cb482c29a4eccea5ec714a0e4da2a869534aa89d84e135ebe5350ea519898e6657883453cad9728852666a106a6f8709c9de2a141eafcfe7ff983df7e7733dd7dab3f75eedcbe713d1ff092093b6f16fbedb9788089446febfffdf7ffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cfff525bd168b4bababa3a1a291d6445a353ababddeca2de6097f4b1a2d1a9d5d50dae2bdea763a57822d1ea7ad715b3d356291d3b3aa7da4d8a3f9d923856746a754393f83a5de2c68a56d7bb6909c2d23556b4ba2129c15992c68a5637242558534580cef688fe3d8e2389ce69484a00c70a7fa3f788882c3c2e243aa7be49023a8182bf23d9bf3dcec31a57db24015e8782ff2469df3d8e635c6d9304b4dbb62e6ea3f0bf95908ae333a2d5ae047373221e41f1b0d31f4cd5f117d16a578238e3269ca885e2e22061371e5f11a9762570336ec2a988a2287915f5e6f11391390d6909d654a3138b5a28625e4b6d3e4e225a9b9420cdb84e2c8ae2e78dd486e320ac71f569313dd3ac2fe33a15368aa493a8178f77b0a73688e96e5dcc86ad27958845504c9d4aad3eaec19ed32466bb75b108b237ab35c62328b6de41ad387e21529b1493ddba5804749ccb242a508cbd9b5a729cc2b8fab498db9c8847a1b191688ca1483b97aa3d1e615c8398db5c576141b355972593b051b47d907aecb883487d5a4c4dd55558f0d4761aeb62168ab8d5d47f8e2fb0ab9362682a11b351f47e94aa3a8ec09aea8a99a944cc46517c1135efb88171f562642611b351347f9a9a7d7c40a4362d2636c72328aa2fa7661e0760cf498a898d310bc5f604755bd8cf9aea8a819944058af12f5293c37d766d5abc4fd54550a47f859a10e68b3688f7cd711bc5fbcdd40da13d6b6a523c6f8c5928eabf495d13d2b36bd3e271265161a1d8ff01551eca1bd7205e37c62c8400775343c277d69ca478dc1cb7110efc96ea13b6b3ebd3e26daa2e82d06086fa57b86e9c2b1e272a1022fca7d02786e9a626c55b37662154781af53bc2735393e2692a6e236c68533f84e5aceaa47899a98b208418a1be0cc759d569f1b23986706294da1e8a9b9a162f135184156fa0de0ac1459bc4c3946321bc5849ad0fbdd9ae78e8c6106abc875a1572b36ac5c3440421c787a845e1b68ab4684f3916428f4f52d56136ab41b4bb3184215750f110dbd4b468ce246c8423d753b785d66c5734671c0b61c937a91bc36a1569d19b712c8427b7515784d46a456fcab110a6fc921a124ab39b446bc641c8f207aa4f18ad222d5aeb2c842d7fa7fe15429b235a13364297a70a7d62f8ac5e74364711c2bc80ca206c663588c68c83506639d51236b39a44a36b239c39997a3d64166912f54c05c29af7512bc365565ad45d0ba1cd5aca0995594da21e4788f3656a5a98cc6a12e5540461ce0fa9312132ab49941b2d843af751fd42644da25c8790e71f54b7f058bd28c710f2ec216c06a1b198a86622087bf6a75a42631151cd4410fa1c43bd1e16b3d22a9908c29fb7512bc3624da2988920045a453921b1b828666c84419fa1a685c3ecb442268250680335261ce68a6214e1d06d54bf50585c146308897e47750b83d969853a8445ff6632088325846f4658b44cd89630982d7c26121a194cbd1e064b28c4101abd915a1902b3856f4478f43eca09812514ec10c9326a4af8cb16de4188f41d6a44f8cbe15256982445fd2bfc95e462089176163683d05785d02984492fa43e0d7f25b858a8e43a6a7de8cb123a63854ae2d4e3a1af18e72054fa34353df495e0ec70c96b5479e82b4d35225cfa35754ed82b22743c64f237730c61af3867874b7a0bfb79e8cba59a112e1d43bd1afa4a53f190c92caa2eec65091d0d99d452f784bda29c1532d9448d0d7bc5a91442a69f517dc35e755463d8e4287542d8cba59c90492f61f7214c160d9994536f85be24c47207b52c5466e73b9dcf193efc923e3d4f3bb150b680ba2f54863c77ca37d2ee81e51556216c0b353eec65e7518ef07f344c2f7c7d43f50b7b45a9e6fc6684a8bf756d81ab9bd00893b9f9cd471a44d6f42d688da45a8e9bea7c548bb805ad3ba8974365c9bc6688685e54c87a8a72426592d79ca94bee2860bd4ddd14fab2f326ecd2f56501eb07aa5fe80bf993a34b8615ac4e171ac74fa1555755c1aa9c6a0999d9f90d1c4d6ec16a16f57208ac998ae63918e36c725dd7fdad40f60ce584c0dc7caafd614e41cca56e0a812528275fbae05041ec27aa5f08cca1eaf2a575422f2e549d213442666e7ed3f99ce1fdbab75926fc6585aa72aa250c16a5d279cd946f4444d6024f08ff350ad5b3a897c3601625761ee348f6839b44f1a982d5335475180c19aa227f1921baf79f5fb072a99b42612ee5e42f1f69bb0705eb9fa87ea130876acc5b3a1fd5b50505eb33844628ac824ae72d4344f7e8c25539d5120eb32989e62be76bfa6d1c0ad773a897c361c85075f94aa7635a7ebe1a05ece728272496a09af215ecd6f1dd652864efa4c687c4629458f9cab31abe1a8a82f65f54ef9098c5c5f29513334a6ff64741bbbfb0871012433395c857305161ef9d28704fa1b60650e76e65bd2f1e32ba5ff7904d1d95ce5b30ec076651190add0ba9a501d0e9acc8d553e63efedce60f777e9efaf1d73fa5fdb5e19a282591bc059d9edc97e5878da350f87e8b9ae99f4efd6f769e59ffde1707c5c383a11a6428277f0160573837f64141fc276aa41fba0e9dfaf8c6afc4c8a5a19a462a99d714cecf12baab59675c36b3ee8d6fc5e409619a38259130c8586a2f8c3d293aff95efc5fc9a308dcd25c2200f501bccb8f8f665cde2d3cd611aa4a87418641de578567643cddb87c4c7cb4235714a6221903dd4784f06c65f4a89dfa7856a6cae31fcd145e8deda86ddb7f91709c096aea11a345262873e2ea37e85d651f31b7f94801c89704d8c8b873ee294ab3638bebe5502b375324236c850c9d0c76aaa963b77cab2cf2508ff3af463f2b31defd7567647e826414924ecd1424d69ef943135ef8b6fff6cfdf4b5d50bee9d72d5f04bfaf43ced4414ef03225c22e471a2d0fdb20c8ebf72407cf8f7178d4fcc9e54deaf074283014851692bdc3182fa1de871d3b39f8bf13fbcb762def57d113e2c8853e2843beea63e9ddd9811b3bf5a5f5339cc4258b1c0e6d256a8e3b976fe27f2b718bd7f63d5d516c28d0568a4c4097534b763f4a1771ebfb117429009155c32cc718298be7dc9d48b10964c408a925888e32e938e7ef0483942950d312e19d2e83a7ee95e31f5b7771f1dd30961cb06a428898530caa6aefd510ccdbcf1c8d82e282277b647f4ef9147c539376c71c1dd1b7f1343772e1a67a1b83c7a8f88c8c2fcc9ca50120d530c79f05d31f4c0fa7bfaa3e8ec48f69fca7be5497038373471c62c570cddb6e0daae28424f12f6a97ef991c549341cd1f7d1fd62e48feb665e8422f5564a5afae54548706e1862f0922362624bdd0d5d50b4eef407272d3df2229b9358e861f03231f1edaa61286a0f12d5585e8404970c390c5e26de1f7c61fa7928760f565a9f1f453871c20c91a5e2f9ff447aa008de5be9e5fc088d5cda0a2d5cb4f8a898f8198ae13d946ab344e28e131f91cfd89c382185de4f1e1633d716c5fea954098c5dbc47b2be33227f4182133b8cd0ebd15fc4cb3fa9b94531645406385f0bfb72fe6265b844f8c0767e100f3ffccf24a1cb8b6329851f5a45f1b1bc050e27d19041a4362dda0f35ded30fb885eb561c6b56503f3a286fb1529c5b84eb6c8fe8d75d5379bde8df31e35f685b4bed4571dcf54862790b629c5414dd46ef111159da55add3c406d1bf6306dadd4aad2f923de9554dfe821497b48a6c8e64df7ab5c2f0055f8bfe9d33401ea3aa8a64955e6dce63a29c38c5b549422eedd3ced9d7cc7b473cdc33fb04908384bea63876e9135ebd94c7c0e5c42eaa6d65e4a7d75e5ab170e16bdf89a7df5775033d9d3bb30876e963bbc5f385f94c44c12da675fa8332b1d52983e212ea7b14bb073dbc5d4cbc279f41829358116d9018ddea9441f9436a5371ebc279ae183a2eafb1335cda2a9e9d6952ab53068d47a87f17b3266e16734fcd6be070d2503cc32e635a9d32e8ec2bf4d8a2d569b33f11839f437e83142715c533c790af9c32e8bd993ba35835fd5b31ba3cdf892a24ada2195a4d7877fac9d0fd28954291fa49f1faf77ddc47c877d0c8495df10caf79f5d7ea6be0e16bd4fa22558d783d6fa5f0f7e63f5686936851a0fbb4655b16df7d55d021e649f2d1fef0743f757f71eae2c39e2d17feb31ef90f620a49ab0830b945b2d6041d466ed7d6587932bc2d13faf2e2548d185e893c082e277585bf27a4fd6d6501871e4f7da1e3b34706c0f36bb86ec5a96586bd88bcc8ce70122df4ad10d6093a00236b763207df78ecd6fe30f141ea4b14a737999519901fc151485a053d6b83d0ad65c107a0e795374cbc6dd6bc8727f581b12f516b8b5435663d883c09cd9cd415f27abf2f8a537302ce1c34a2bfdda3330cfe928a17a9fa18f526f2a68882440b77437f10d5ba602b9f7c5fddfa8fbef953b21ffd39b9fba3e7e3d1eede9d22f4a5c1545e5eee97cee70cefd7bd2084970c3a30307f82a390b60a75d7887a53709d3ffd95df447bd3ea99a7787329776210cdd8212299677d31e51b1191b541d5ed82f273f2a85eaf99731bf2283473d250a0bb5574760ea46ed72dd8255e7ffdf0b95edc45ed4600cf96ec3b7ce048f6834173fe3d8bd77fb0f78888c8b12f5f7bf6815b07e743409d298b91574514a4a2201717ad6706cf90fb36fd2646a6175fa26f05b52a80ae91f65b8c1b21ed2f0d906ed7d5ee118dcbf2225cb1ea4713de3f29bf82a390b60b70cf8ade8b82e68a3562f0813bb4eda4ee0ca0858438a67d44c884801872dfa6a3a23b2f02badcb0f86baf0e8d409e85664edcc2db3ba2f9d260b9719318be4c5327a18704d07e6699619d8f32355e953b59effc9737639e172f67e6476dc7aef6e616e45d11058917d84efc4e740f0b907fdcf69e98bfa3a796911c82b75cd8d70c1b22ec662f4657bd7e44dadf32c3d676ebebe2f1257913f08217d3907fc151904841ed1cd1df2f30ac78b3f8f2512d716a7b00dd48b9869553aebe193b4479c3793abaddf389783e3b8fdae2c10ce46370159a0a6951f1f09c80e8e424c5a7bf5ea8e305eae9007a38d866ec109ddfdfa0e6ec1303d7e44fa344ff0ce467768693bac2d948f1b27b308cdd2afe5da2632f551940eb826cc60ed1dcd24561e81b62e417f9d333fa36234f435c41a285b261e269e720e8b1487c7dba5a0fa12f30a0b33da27f0fa33e0fae8bdf10fd55dcec5fc5d0bca9e7417d35791b1a159256616ca0789a41004e6a11cf7ffdd26d4aa6b58d521b4b65e0fde83d22220b0d3a4902eb9656f1f05be6fc75626cde344ff46786e46d5686938682587f51bf8c6af1df79abc4cb036beebda5bcefa9c86ed991f2787d93d21d6ad5d49bde3992fd5b738604568d785bd95ee5375270d8e981bc9eb7a142416205b00bfe52fab94f39e5fa6ee677a2ffc3870642ef05f33fe416a96da16a3c9b24edbbc6dc19507d368ac76eb61ecf8a9787f66efd3c1f9b209e3a791bea14d276fe76ed0c674cb7c0291bdebf8752ef43a2ba1b0892feeb4577ebe2d1f0d4a1b6a81da06ef06c2b2115a6acf6d3151e8cdf279e4f6833b259747ff6c8c8de5d01201f5be7cd1bf99b95e2a4295f1bb1594464cfe8e0e8377be96b5ffc2e22b250e19c1f45f52d04cab5df88fed6c5fff2c4a66a957a0b5de655a73f982a53f6f8693cb5891b2706d600b8e9a0e8ddf1e850b44f65f2a4ce7f7823ddf2364414a42e3f738e49762710caa6d42785fd962a4b89ea1204ca9da2379345e48b2bbcc06ea6526912f50dbc1e24ec46434e133f5552cf5363c5c4cdc05cd17af4d961a0a96ff2a4e1e2f1a0fc0d8e8244f3b179420ef3dd798e2bca2e71fae7a2fa5fb4bd857acd4f8f89d6bdff91f6935dbc7088d6ee4ab5d47acfca29d7906b7c7517b58cb95c74eedb54737dfc656a199e149d99da8ba148edce93cef1e88f4e791c5c85b4957fcd14769317275a679ed377c0b0cbfaf7f060c8e28ce8ac68a7db0e515d84ac55d4521f3d2f3ab74f59286ccc0bb4b63719ca1f52f70590e3ab07a985c428d1f85605da5650d35e148d3f3cda1baafda80ff2a4cedf78b315f99c9de1a421ef9a2ff467c4c9e70ebd7e7ad5e275eec74d5f24f7fff2dbdf422ed435e679d15c95adcb07a2ba14d957510ff8e703d1e9e019a1b77802274bcb48a81fa3a201b4d3578f52ff6e6fa8a87f3c05d9ad16a265ab68aced09e5580bd590276186da12aa32af438582c4f3ac79c2ff347afcccea67d67ff055463cfd56cbadaf8bf68d593abf21aaabd1ae4b4df2cb89df8846b71c7385dfeb0dc6389b964deb0af581429f1c3cb6f86a0935b79d11a27e2fc87e2ded7d2feabf8e87f2f56f09bf3c5fc21b2a2f60dcf6f65e447e87848244f2aa4af1a7ab56f9897858dbe61f8da2ba1eedefa146faa48fe87480a8a87aa47d26b51b9e8fa1de346396bf5653d3b30d16e5839783eed792a5f598685c05c50baa768a6a4dde74a6421580936bb2dd8d7ccf4a2934591d2f3b1ab57d52267ead50b87c93785ad96699a86e0199a1cef7c7a5a2d12d070688ea017faca2567a7723b5d18c75feda404dccd24394df3b0d8addaeacda545bf99568bd811931f3a53f457d7ade845dd42c64ed3d7343fdbcab91f721a220f51dab68ad9b16114956983778c68a26df5451172c136f5bbb039823aa2ec89385eee68bdea2d101d0eb80d21e7fb45033bc9b42bd6844ff3ffdf536756d969f949e81de35a2b76aa8dda55bff6befacdd7a48b41e1c903f39cc1ce4cc4a380a52d181b2eb858c99d4fbe605ef1e163f6f24ba3efc8b783c17c055a2bafd14e63cea77f8b1a7a8ef2c0770eadba2fca12f4e113ae2dd0c6a95118b44f575c3b651a3db6c1755077a678b3f6f411eddda5e150a0e7015d27647c9aa163e6ac669575535ee17dfd7b6777b8b78be03e8b957a5e54cb023a96ffc707aabda969e00b05ed457f8a29c3a0aefe3d4d3269cfb8bd2cb86eda606017849541de89d20fe5c8bbcdac9d2321905083bc3495307299214c58477c3ee5ef385046365b6cb5f1713c763b328eeeb037a02b5dd07a77e20caf568fb8468bcdb1771ea0303aaa8274c704479a561fba8bec0e3a2ea40ef68f1e721e4d9639c4dcba675472102150a52d7218aa645d99b72c795e06ceddea6db13a2ff63eab185a2f8c320f0716a93799d3689f2e3683b5d748ef2c51a6a9101ffa51c034efe5aadd6b09fa89e9829aa0ef45ef0a33fb6a2e01d8a3a0589768062a2d1d256eebcf38704ea6400b8a959746f9c71d623d407a2f86b39141752cf9ab75e94a7a06df96f5abafae233ea560316500f18305dd41dc38e50d6d5c7541ce83ded7df1e5528409ac6685b4d5e1898ace8896f287b6a425605b4602386fa5686e757a0368a154ffbc1eaa2f5055c62d10d52317a06dcf5da2f37bf8f144a12f32e0292a6e80ab619e59ff10baef57a2b8089ad78bea80df4cf864224205886438713b3a91b48e0c9447ded7f0a304e6b1433f7dfbd5eeedef2d9bd615c0ccef446fab53060015e2e1ed507e9f8a993653545b90fd15d1eafa6204f51b0c5c46dde9dd58d138d3ac53a93f5e11c50fa17996a89e852aef7ea9ea8c9001620a12efd844d2a233c19d79ebca7de2ebdf921f372e73ee1c7fd9d0fe7d7a9dd1b513e8210da2b7d52943d6351efc17ea7ba92b0dbbe2a8cac7c8fe84e85de18bbba80f4da8a76ef36ead8e4ab3caa8df443109cd7df6abdc00e0798f0e3e73110ae3ad6854904847269216ad51e292f8ab47c49f4777bfbab2e69e9b2eeb6bc1cbaadf44eb01a70cd97b1dd2b7061affa02e32cbfe5c145b907dba689eef8b95d4d3263c4fddead940d139deacdeccff44f1b733752544f111b47dc68bed73cf42a13cd64a2924ad8e4b242d5aeb90b5d3d58f6f179f3e34e1e21360e0d0b745ef8abe687f8e68ffc0d27086d0ddcc7a5514f79d92adfc375d37fae253ea3613d653377ab658cbd5665d42fc4fe97a689e2c8aab90bde6575d2f8e43013d161105a9efb044d2a2b519007a4e497c2bfebd0846ceca8856f75ab0ef69fbfe12681c48a561749d28fe741eb2f6dc25ba2ff2c33f84bec48457a96bbdea7b58cb28b34632a2782f349ff1b9c246b4ffaf69aed291d7abaf2b4341bd168e82c43a2831d16cc39ab6e5a8f879074c2cab17adad71d0a344fb38e8bc8e6a36ea6e51fced52647f45741f851f07537fc1c437a92bbc7a5cb40e31ebdaf6fe278a0ba0fb29e13fec4400e87b4bd58ab7f7c9cf5f7ef4eaeadaf86014de73e12aa4ed8e88d5209a13972fd92f3eaf35e18616d1fa8c0dbe56db1c689d496d34e90a519d80ec4f88f64f7c319dfac48877a8728f7afea8e762b32adbf99f2826a0fb92bfb86f2e4071b1d7ca70d2d401a9488be6dfb789b1bfebbbd180ff8ad677af82e229295d75d05b432d36a8db7695f9c83e5df4aff4c5b3d43223b618f480e8ed63d6dc6cff13c56da76a7b4af8bb10f2408582d47534ac06f179b2feb64bffd276e474cf06be2e3a7faf82f264d1dc08cdf5d45c839e11c515c85efe9b07b37db18d9a61c47a83766bea65564d36e52ba0bbef61ee35843e50a720d18e45455afc9cac8fd940f716d1fe3abc9e7150746e1e06f5573435f7d4f51635c19c9828ba2767ebb55bf8635cb92f8e52c38c586bceada2f90cb396e97910da1f17be3c04826685b4d581b01ac4bfc9fadb2e40d6f5a2bfcaa3d35788ce837168ec2b7ad3a3a0fb0b6a883117ef57f87e00b26f12c5b9dc697ee82f7427235698b3595737b35ed1b201da7b1de016210c12c970e2761c2ad2e2d3e4da3b2f41fb35e2e1286f66348bce972e86ceb99aa640fb21ea0c633689e28dc8be5814ab975329f8b192da05239f366698e8ee6c96abe3fb0bf539427f756628043105713a0856adf8f2d0c6f830d09345f1576a1fbcecb252747e7b07f4beabe72168ef26ec1198fab028ce47f6b9a2b806ef531b7d514b25cc5860cc225d7fc1ec161d95d0ff26371df9ec095d4e3fbb4fffa197f5eb5ef842a382443a04d1a4f8f0aba5379f01c5614754dea0125e0c7c5f74267a43ef40d1ba0afa2fa63e33e51a515c81ec378ae24727e300f5882fdea3669bf10835c10beb7b5d870ddbaf6109f49f7284da823cb3739f2ba7fd27e16e6bdef3cd8f996342ae2d7c592985a495fbac5a31ff3de732a877df218a730f53d33c98f48368fc6106743fac6547370faea1b618d2a349616bd76c83f62bfc380065424ff0c5216a9419f753777b3143741f30ec98c8df0adb4ef5608cd0f7e40bdd2ebe76d613eb3ede2ffa0f16bc105190869c174d8ae13fbd34b30fb4ae15c5a7a60bdd47dfbf45e7868ba17dbb96b1f07026b5d490e5c21f1a89ac3d3e16c51b816bb8de7e385fe82e66dc4d3deac5dbdabe31cb12f52be0610d777ec7efec6b1f5cd7f48b18b8b4e085b882c4729b552b66ef5f724357687644713356511f43778fb5a2f1b77ba17f94e8ac81978f520f9a314d146722fb8ba2381fc00354067e9c40ed8199b751f51e44857e8eda63566fb527e0e5fb948b8efc090363b5effc2ce64e2878c15548477259242946bf36fd34689f2c8a5f9c873dd463ba466c178d6f0d87878feb70ffe9c9f3d46d465cb45f6129b23f268a090078917ac3178f50eb0cb9997ac38367b9d1549359c394f69cee89d0733b6a6563e6afdd2da6d714beac0c274d56eeaa1693bf7a6c083c1c7650e1d895182df4359aa66644e37fe0698b8663a3e1e987d458235e117e6bd76c3151fca4479b2fa8fff86213f58021d752497dbdd2d4a797521f993546e976787909777e07acf7a4856ffe28bedc5cf8425441ea7255a449ccfdebe5899de065f76da2380370a8fd27e9a9118ddbaf86a76344e383f0f63b6aa009f70a7f6824b20e3da8f0fb65007092d0637df10d35d690724aba689b2ff43de5d4fb66ddacb2119e56522de8589f35aee68d83e2df650530d42948456eaa167377565d008f5f14c505005cea05e8ecb95e34d67681b78b356c86b727085de65de71b8f2acc44d62e1f88e22cb41dce75f7c369429719723237545b13d7f3526aa75977a85ceacd22aabe03d5ffcee7bf119f4f2b84a159216de7a0489318bbfe3a78fe8c28be02e05ca1a7eb28df25ea072ae171a77d6ae9811ef5e6e0f9e83da2b814d9978ae233c83a93fa127e2ca77e84a9bba8f9ba6e16fa055c40ed31cb517802debe4dddd7411af5c0e68cf8bfa56b41ccce70e2e69e6a31f5efe746c3fb1a516c3a0b4025779e8699bf8bfa0743e0f57851bf1b1e9753fb3d7344756bd76cb345f1bd93b22da556fb224ebd69cc0aea755d0ddc049c4e7d67d60a6ecfe91e1da0c6767c3a8f7ee85d09c891288821a6204e8e893489a9cf5c02031f10c55f4703c02a6a0bd41789c6155de1f92ab517e1758cfad4ab49a2fa571459af38a6f0f31064df4acdf2453db5c0986954e6643d4385de0180fac5ac55dcedf0b6a7d076c7a6cb1555affd2141d93a1905322414249a4bac6a31f63998788fa84e45dbbdd45ca5deaf8ac679f0defa51e9db3e9e39d416af3e50aa42d6b25da23815ed1ea286faa2899a6c4c3f4ac6e8a9e5ee057080396ad66aaa191e8fa50ea3e3dae972e7b55f25007f3bd8ba77f7f6f76a2bbba36066352ba4addc114d8ab9b609b78bea23687ba9d097aa5cf3a5a87f7d1d0c9c22ca31785e4b3de7d50195d791fd05515c8076fb0addc5177f53fd8d41867a44cb993f523f9701d8c3c819463552d55e3d486defa80cb86b5dabf8b675fbfadab9375d36b47fefb3ba9f8c0e7a3c220ad2902bac5a31d885819344752db25651fba158291a5f3d1f26bea4b402de3f473dead17c513c3c34db43a2b805ed5fcbc18f03843d06735fa7ded53257e8a701e043aabc9dcef688fe3d3c73a9b15eada3ea3b22651397ec123ffeb8b3f1e9f9b78cb64f403e588fb882c473433429eafff32066c0f5a2fad169d9dea01a15ee128d0b6164afbf553e2f33600315f766e411957b9175bc287e7b11318d727d5149ed30c8a18e96e9d8c98d68b3869a956df41e1191855e7d4a8df0ea63eabe8ec63fcb9d778e89f94dcfcd1edd1579653e1a15249203ac06313a03ef2f17d51f06236bd91fd4fddc03a27e683accbc535427c0c077a85b3de9ba55141b90b5f75e959b4056512ff86211b5caa0c1944cd03051e84d68fb10f54c1647b27febd15eaa9f575f50633b147def58f3ad987ee4c3a76f1ff40fe49ffd5646a1c90abca969313be1dd7051be09d96f127a285523ea9f8c82a1afab3c0b1377525779b242147fee9f6d8328fe07ec53d4225fbc43dd63105aa93a0d9bb85bb2dc4cb96d2649fbae373f533dbdda4f0dee307419fbf8c762f88f6f3c3eb12ff2d57e4415a43ee06c57b4fee945c4b3fea27c3fdaada57e01fbb4a8af391d865e248a998b8cd84b45bc70447516b23aa2b81ef43a6a9e2f7ea62e33691995541b25f42e648d50c73a03d84a488527c7a82e5efd4e9dd731b864f6fa0362f4812d8f5cdf1379ed00380a521168d5a2f73df1b0195ef716e527d0fec7d4462621ea0fc1d8f92a3530f267aaa7078ea8be80ace345718fcdb9d4643f9c2db465d2144aca959671f3b39d42c9b540a73f982a2f4e15f6183c3e49e86eb9cfae5cd62226ffe52eb8f93ce4bf0be02aa4ede08a26456b2adaac70908b7b5526cacfa2fd73849ed35ed73745f9bb0930778f42f224338e519df4dd29f237f75d9f2cbdf7aa5c0bfe73ea723f5c4bed83c9e772b3557a1fa27eee990def530e3048d88d5e9c43fde85519f51772fba937d66e17938fbc5e350a79f2043bc3891b547683e8adb32a85ffe80bcef2e854515e037222d7bf9db2cf45f9adfe3077a828ce8591dd844d43fb0491bf85bf0d59d78be2fd50fc85bac80ff7530d46e1236a93cac3423f8576e3d4918198406df12242edf1ea42ea402e8bfed73d2606671ae70e411e3d01150ae20453755ab4a6a2c03bdca6614227e0ed09a2bc116c1d7510d9fb1e12e5a73bc1e0a71576c2ccf3a8afb48d16e5b5c8faa028ae816267a12d3faca51cb36aa9b4c2897bb861edf5a7e445dc45adf3a29cfac8abe1d457b9aa7f7c4b460cfe7dcbbcc1c8b337a04e41a201144d8a5e07c055c25f1de72abce929caef82de4e3d9f6d88a857c1e83f14261a3284daaaeb02513ed82fcb35a2b8eb4c955ed46ff0633375a359d751d28f9b26f47a909f5252eb50cbbd98406df1ea526a5b2e3a2fb666bf18fcd7db0f8d401e3ec26a56485a41633788dee60800cce5d6a2894ac1d3cb44f913d0e70a3d2d4bb9a8cf81d1d70bff160c1d436dd2749aa83f80b6bd76ab5c0dd521d45e5ffc45f5310bdc34ee7d6e3cf30427add4222fa6536bbd1a447d9c6bfa4e5ff3ad187cecfdff94234f1f814886938680a94e8b5e0759ebb911b6d08e27b78bf21ef09338bbcd24519f0eb35f55e86fcaadd46a3d9d457de78959368ae23c285f4b7de8877ec2fe0ec3d7532ba90aa13f067b8d02ef78711fb5d8ab0ba9cf7349bf3b5fdc2f06ffe63e767527e4ef2b1057905890542445af6b23fb6e6a1de29ced458d28b742f129ea2000cc13e53f6f81d903857f01a6de43d569394b344e41db3a515c0df569d47a3f4ca4b69b369f6aa11ab8bb28acd237c08bc7a8ff78752ed59a2b06dcb3fe273138f3e623634f417e3f038d0a693b306c57f466e268d7127a169aa866789810e55fa1ba8b5a0be02951cedc00c337299c608c43393afa8bc68d681b13c55d676aa8a216fba1865a695a3925dd88d1427f6371171cd0d5022f975271afcea00ee782a1f736fe222637d55658c8ff7758194e9a02c2aa16cd8d16da1fcdd9b6d0710f1a441daae7093d197845945b87c2f06b857f08be98ada15c7496b7e9d6a2f05b39343e463dec87466a8e699dd3d41862255703c5f9ba9ef0e465aad2abae949c126cff883ef4c6ef6272ba211e41617007a20a52170853d3a23715053b97da8938676b3be15d51ce40f956ee746c17e5af7ac1f497b80cfc31456d82e87c066d1d519c0e9d8ba9b97ed8475d6e1adea29cf6fa1da58e5ea4820f355de3c95bd4f55efd931b185c5daea9f940cc6eaaadb050301c024741a2fe8bbaa2d9b1403f4a3968a29aa1fb824f45f93ba83f4d1dc45ba2dc74024c1f26fc8d3eb94e69a6e8fca9779be147159e80d695d44c5d03fa75d7778ad06718f728f57a7b4f08bd12ca37fca965393cfd90bacc2b7c414d0aa6736eaedd2166fff84a3c8282e212342ba42d9f59f5a2d9b5a1584bcdb3858eeb1abd57945ba0f16b6aed3a51fe10e63fc3ed874f46ab3c245a6bd1f655e15f81de17a9c95a622ffe20226bb50da3f6c3f8b154a69db2fd5cb91ac66634b4f6f6e6136a9867ab2927704e2abfbf61bf18feb673e54928344eb1339c34f8ab3a2d7a5315505e46cd8c73b6a6eb7e16e58fa0b1b7d0cf8af21b30ffbc435c955f06293c277aa36dee16fed3324d1ba90a1df592fda0ae69d41be675a16468b6f9426f86cef2efd5ee81b7bba98867f750ad23bb0548bfdb96368be99fd74d284321720a620a12f75134299a1d0bea6ba8ca26aa197aef3c26caaf43e7144efd25f8d0117e985f2ee0de17bd1f03409ffd5c7a2434bf455da52129ed2fd554473d691e3ea1aab2746ee1266ac1b02f55ee80c75f521779369c1239fae9da07afefe5b353064e725efcf488989e7979fa8528546e4142211df18bed8a66d786cef5d44ca1e35aace5a2fe32b42ef7a4163eecb68fdb06bf9ccd746a12cd0fb44908ef40f747d448b5af859da0e76d6aaa0f1651ef64b95de84fa0f9e2a77e6152d7c0eb14d5dbb3130e72d9bf7d6dc1adfd7cd073f884f8d36f7e2b7efcf0dfc350c81c63a53869f287552d9a5315d0bb995acad93acab78bfa4ae86df5623efc78b7f08e6f4e23067c2de4dfdc45006e11be11da9ba94b94160b5da3e7476aa00fc6517f9cdce61d6eb62ee0d47b3fcfb6ebbffde1f97eaaa767785547d623db96dd77db75c37b77f3eccc21e3672f78f1c3d45fe2d34f168deb8202e7184414a4ce0f53d3a237e340f73b548a6a86c6f83151af85def3c5c329f0e57685614655519ddb1bf383e8de04a0fb6eeeb7a1fabea6ce579923fc662d670addc907a752320ec018a1bf3d4d1f80f28a58dc89c0c45fa81ededda88d3cfa7df35b2f2e7ef8ce09e5578eb976dc8489b756de3e63d63df1fb1e7cf8b135eede63e2e7f76bc69c8c02e81a380a12352eea8ae6461bda13141f572b5b2d1a1d689ee2c115f0e544e1b7c1e83b29b43be188689f06a056f82ae86fa5ca146e11c55a2d57529fc38f5ba90500d6703508c6235457efe07a16d07fbcf950f41f2890ce81ab90b4ccb2ea45732a0a0fefd716511abb5b348e85eee7f55d017f6e5170cc9aa8c311fd874e072a847f0f1ea6a96edce97b542ab5cca55ef685436d052efe8b3a7a5140fc497532a032f76436cd1b8a42ea1e3bc3498351d569d19b89c3d31ba8ff3129a83e281a8ff686f68cb66be0cf2b4471985957aaf5de201ebe07584d0a577b718c3a817b42145bbb6ba9a7fee38b724acaf0a8d0ab108c9d84fd0b267e9253be7de1cefe28b4ee418582c4cc89264573c282b77d28ba4ec17e59347ed805dafb8aeeebe0d384c236983d4869fc5ef1b216a8157e11bc141af408519d0cad3ba989bee87c88baaeac95bb3c20ba52878de8bd2d57b42c9b722e0ab183905048db86d80da2d98dc0f3639a22dcf8af45632d3c9caeab023e1df0b7826398ade288b7535021fceeeebe7859e1dd4ba1f728d5df17d8402d74847e1d017906f5b311f8d79b39e0a305379c8642ed222bc5499319d569d19b8ac1c0dd7a52a01dd1791fbc6cd07413fcfaa4280e33ac1b3538d2201e9f6335294c821f6e12bea51ff45e24f409fe98477dd2ca4d098a5ed47766e094f581f6ddfaf9e527a090bb081105710c882645b363c1c497f42498de1b4467253c3daaa7127e3deb27856d303dc3d48ac6cfa65229ac16fe39f8e223850a689e487d097f8ea4145bfe19147da8af0d01ae7a3e980ebdf368c55928f84e82a32011af6c573437da30d3d153415cf595684c5e054f2f14ad33e0dbfb45d131ee3546e78ba738d48645c2ef2ef3c51ce16ba1bb866af009bed6f62082b23fb5db1860d8822ddf04caefdb97c6faa130bc09ae42d2f2c4aa16cda9284cbd400fdabfe39868dcdc1fdeced1321bbeedf499ca30e31ef6e4696009758f285e033ffc6b1fd76469db44d5f865b1aed6b2c0184a6d37a86defeb1f58db12005facfff7f83e28208fb2339cd479519114bd99380cfe5447637b35a2f32178fda68efbe1df29a2b80dc65feb8503601da5fa207c51237c05b427a9297e19abcb41605e46bd675856ebd219cbb61df5c5af5fbcbdfad1ca819d51681e8598825468b31b4473c282c9f375c4b39dfabc68dc76253c178d0e7cfc828a63de59faf68e0780b7f5bd045f5c7c987b19dabb093dc82ff85a4f6b59709c49d5fa217bbfc9354b5f7ea7f9bbdf0cf87edb86250f565e71e1a92854af42a342dad2549d16bd6e04665fa2c3ced2d7158d755de0f9000d8fc3c76766548699875dba36f446db666d5f9eeb8f95c25fa6ef52ee24df2cd6e32040773195fe214fe935f0ca89b31eae5bb3e59d37b7bcdab07edddad5ab963fbbb876e1e335ce5de387f544e17b9695e2a4414b34297a533118ffa15a33da967f29eafb26c1c007d59e829f6f17c56df0e1a39a1c64ff5edb38f8e22ae15740ff9dd45ef876ac9e0b83c4215abb0740117016a20a1257b31a44b363c1fc7fabd5b599f2aba8afb561e2874acbe0eb57541c3ff4fb53c7def168f70f5d0e0ceb926d0bf7cb451e2ca636fb075febf81881dadade64849950a790b655e6a445af6bc38f83ff528a0227d589fa91bb61a6a8ae86afcff95de5523f20a161e93968f754d19c808919eaac2c5385af8287af530b7c54a3e3ae608193a56524c24d68e6c4e5224da23755019f2e5102aef840d4778c86998fa9bc027fcf14c516f8f22aa5add781ecade93d18b997baa8cdc94ddc279dbdd8474df3d105bfab7d794ac0608cb369d9b4ae083b4514244e58d5a237e358f06bbf230a1f594f8ac6b5a7c3cc4a51fcbca7cf5e55a9f3071ee70e55fd13ec303ddfc2cc6dd4f0360f0a3f111e76127ab48fb05ced4184189721ae209176a249d1db68c3c74f2ae8ad86a1c344b51cfe3eff2f956b7d82e5c4d78f0f047f839e32435ea5c60038ff276e03bcbc983bd34fa394be2b0be9c15568ca62d58bde5414be3eefb0570727c3d053f7aa4c87cf678be2b79dfd82978e66597f4b67a8ced2321086d6533703582afce59e8ca732f0f506957908ebd9194e1c001569d19a71e0f72f3dda390ca6be268a4fc0ef6fa8ac847fad8afb6f1a7c1a34ced771214c5d48dd015c29fc73f0f47e6abbbfae56d8d9391fea6c8fe8df237c800a05895a0da2b7d186ef33debc76364cad13c534fcde4f5427fa48ff44b58f2d187b3f350fd8c21d1be4cd2a6aadbfb094ab401e3c7a8f88c8d2aea103342a24d3a2355501ff47c4d3d59d61ea2c51dde2bbfb540e9e164083fe50590683a7510b314df85a78fb01f56f9fe127268e7cb7db79431cc9beb55fe8c0ca709a1d0b01e878b210c65e7154a9ca77aeca5a04f1630a3530791cf556d9b7dccfbd3dfa919ae437d4b7737034f2d4cef68809773fb6c6ddfb87f023c206887ae6da0844d7833fef86b1bd5a4439eab788a84e0e24ac64d60c87d143a903eb8477e0ad25f420df61fa2e11d9517b3af2ccaefdc6ce7cb4fe8ddd3f8bee23a103d47993892120457fcb5530f71551deff0fbf55a91cec1e4c78bc9d862b6178574af5d3533c1aca75f51fd065e8e9c8233b0f9b59bb61e7cfe2fd8ba1032be5459d85808cea7ba517ccbd57d45f81dfb7aaac455047e20df5f78db561feb7fa26c1e36ba8561414cbe7ae69117307840d10d5978a22301d5dadb360f0f0231ae6fa6da8a84e0e2cffbea1ed45783d8b720b07e58e2b86df153a80a3cb4180ba7a924ff786c96f8ac6a17ebb53e560f70e479dae23033d7b92da501828775cf1a11b3ea8d6e3da0852a193bfb739b07c2cccfeb768dc07bf2f53598b0ee74c5d0e3cdf402dcafb3a5ff9dff7c4a72f840dec26d199892350a35c05ca2f39e754987eb9e85cebbb6d2a933b1e97697a0bde375377e5755daf7de263f1716dc82096169dae8d608d7316fc78e2562d77faedb43f150e76ef789ca1e7b7e106fc4a8dcddbba8dabdd293eaf0c15580da2d545d026a8147cf98468edebb7a828ae450774979679f0fe0ca12fcccb4e1abbe013f17f1dc204d1a4688e064d1395f0c5f5a2f533f83dae32b923325fc70618388c3b31ef3ae1ca9a8f24080fd7204c502dda9356b05842c7fdd0bd59cf0adfd52bfcdcbd2312d1f04d5f136ea1be477e5deebc27befeedfb5d6ed6e71fbb182102ab413cac0b962817f1c312d17babef3e53a84787749bda2d30b18afa208f2a775cf1ebf7db362c79b0f28a0b4f4561775724299e4603c5e1e0c349a2f95f7e3b531427744c26283d04235751abf3a472c7151ffeb4fda5c7ef1a3fac270ac5b36269f13669054923e5fae0ecaf34ed80dfaf564876ee9860b9c23d30d3a59c3ca8dc71c5f49fdc67e6dcd0bf2b0acdabeac5732748d294e3837ad15cebbbfb159e4607f5dc1d540c867e4355e639c31e7ceb9898fdc5aa59e567a040bdc96e128da99882440244e89879d344f758dfbda250de51c1c92bdadb741d0ced24f4a83c6640bce180187decbdc7aeeb8e42f6a4685a34365a70149a8223ca458cbba055d7df27faee3b6e1b3ab095af1c13f976d37530b62fd7334fe9737bfdd762f4cf1be2c350f05e344734666200d0cc891318310ec6bf2cbadf87df7b09ef746480cec3bac3e4aba93f90875a15b59f88d1fbd7cdea8f82f8a07ad1d86ca36d44216d078543b9c6cd16edfff65d85c2d08e8de953a82ff28e48bc212d26a7d64cef8382f91ccb158d0eda75387183c2a5ea4ceb7b40dfa5be7b94db827cf23e6a4b5e6155d43689c9dfafb9fd3c14d4d7449a443d530132c549454024a9b869cb45fb11f8fe6d2e96573c492dc91f22f186b418fce3ba3bfba3e03e269216f5661b6c5421690583d051c3c68afe4dfefb954a76c92bd65273f303aba2b6490cce6c7e60140af25b62a23101c53a4e9c4088729661ef28eca4e2be2b137a31f2cab7a9f179c0a8073667c4e0ad8f5fd70d85fa29b5a29e8941d54a7162070eccbe5bf88d420ff4dd706e6c7eb19b1adac1eb7fe7f3df88c19f2fabb451c85f522feaa908d42b14dc208853ae593d535ceb342a03df9f49ed467e79803ab703d7bb72598b18bc7ffdec4128f40fa917f5460b3a1b39a90800876a34ab56f869aba84dfec32ee681fca293d02777d0ecd8735f8bc1bfbde55cd11945c01df5a2ee40af9de19296ffea28c7a8cb84df8c2fa87b03e001a215f9654fea5774c0cb263eb35b4cfe7861c5192812aeb05c51cec4a0dbe1c4f19f4bc58ddaa4501e117a6800e0cd76b621cf1c4ceded6875babcfa9d636270cbd229e7a18838c26a12e54c04fa535cda0e94a849b709bf18f750471088e3f7b67919f9e658eae30ed580bbd6b58ac1fbd6ccbc1845c60d56932837dbf030ca4983efd23eb13ee3be3f172f525b82015d86c6eeb69177de4e6dec30954d5cb24b0ccebc3a7f388a9013ac26516eb4e069232751bf096d195423fc5ce01beafe80c853aba8151da24e973bef1c1383b72fbcc142717281d524ca09786c67b86490c0dcc17f70ef0311a18717321652351d9f7e77ae6b1583f7ad9ed617c5cb0176529463f0dce1c4c9452f0a3f0e88534751c85c41cde9d874bbe1c94fc4e0c3af3d380ac5cd7eab4994633030c5a5eddc73b3f0ab0134506f1634d653b7746006c71bd362f0f645e34f47d133df6a12e5184c8c72d2e02b9b4a19d3693bf76b7f0007a8aa82c65bd4151d94336f7e66b718fce5aadbfaa0289aef8a6a2602331b3989fa294ab9c63c287c358088d0a30a1a9f50033a229756bdfebb987b70437c088aa6f5f5a29a89c0503bc32573cc8507b9e62e00e2d49f28687e4d957534ecca557bc5e06d0baf3b15c5d4f87a51cd4460acc389935b96095f09000dd43b858d03d43f3b129dae78c41583bf7b7ee68528b6b6c744351381b9568a4bdbb9a4df1f5c03daa6a9eac2c631e6003a8c17ce7cfe7b31f7d83b4ef90928c2a6c744351581c9159c2472c902e12f6d1311babca0d155d8cf3a06a75ebb60bb18bc6bc9c4321469cb2b44b5d982d92e2791dc71f6016e11dac6391434cfa6dc0e4064ce869fc5dcd675770d4011373c9256c9d830dc567073c7c34227cbb234506e61a33ff57a8e3b63c2e25d62eeb1779ccb3ba1b8db6d2545311381f10e2751bf38549d11ddf67277216b9a720a1ba3a88db96cf8fd5b0e8bb9bb964c2c43f1b7bb41143311986fa53837081c23e608bdbb539688d0e5858d2ba9977255af292bbe14737f78f9ee01280e673ba21a831f2b38a9c80dff68e6e6226b9c4361f35a6a752efa6779f5bb7f89b9effff7ea935034ae8e8a6a0cfe74b9646eb85de86fba676ba0dc02c7786a45cee9337dcd3762ee572b2b6d1495a3adb44a0c3e8d7212cb091f700eb2a729a7c031915a92534e19fbf84762eeaf9be70f47d139da15c5047cdbc82573c14d42a7ed6c11a1cb0b1c53a82773c780bbd7ff24e6ee5854d11dc5e866471413f0afcd899303b6708b903dcea1c0399daac90dddc7d73589b9dfbf30b31f8ad5c951516c869f135cda0abc3142ff35a09d06ca2d74cca29c1c30ecbe577f1563ff7cd7b9bc138ad8c5765a2163fbcae6c4f15bd2f66c1db70aeda629a7d011cf216593967d21e67ebef4d6b351e42e6e10c50af8dbe1d2b60f2a2871bd1a29fca876224297173ae6e78a110fbcfe8718fb4be3dc212882075788a2039f5b194a123e887212f36839b71eedc639143a1fca0567ddb2ec4b3177db82ebbaa138de6b25155cf8dee1c436cf56a8f3e6e263dcd8f61a28b7e037b2eacda3626c6af5f4be289ef7d6099fb1fc67a5b88479c870ae378b847e03eda729a7e0715fa0f59cbcfc2b31f68f371fba0cc5f5dc8828461080314ea2e625cc39f717ee96f6224297173cee09ae51556f1d1363772fb9b90c45f7dc2685380231c5b9e645b9a4278ed01fa3fd388782e78c60ea3565e55e31f6a7f5b32328cad7c68577118c314ea2c6214549cc8b666e26d140b9858fa9d4630131fae1b7ff14633f7cf49a5350ac8fb5d35cc60e08b89c6b5e8c4b5bfac60afd5967224d39858f89d45301704ee5aabd62ecdee762bd51cc8f6d10de41504639891a677112d7b79cbb0fed47842e2f7c54502bfd16fdf7bb7f8ba987b73c3012c5fed6a8f0cd084e97738d83cb25b59d7e80faee0c22cea1f039967ac14ff6d4fa7d62eca775379e8e1060abab100d90082751e3ea3889e8ba43e845201b28b7007239d5e89bab6b3f1263f7af9bd51f21c1d4a8f07508d204d7685c5421a66b0b378a49534e01a43ff5a63f86fe779718fbd163634e427830d5e55256a0d89cd8a6c1e5a29a2e11fa7d9011a1cb0b20dda98f7d70e17def8ba9ad2fccb800e1c2d288f01508d60497302eca4534395c9c89732884fec67c615ad98c57c5d4ad35577746e8b034c1b908589b13db34d43129686ea67eb79906ca2d88ec65f61bd565d2f3bf8a99dfafbda30f4289a1b6f0d1a041824b180797886a1a2bf40b60d3945310798ff9dd9cce639ffd4eccfca0e6cace082b86263817811be5d29671565d3b0e342fe76e6222429717445e64c436e3eccad5df8991dfad99763ec28c9db6f0d1e0814b89631c50d1981171a3d07cfa01eaeb4e4c9c43417411153360d0bcd78e8a91ef3f72c509083976263817011ce5d296791edf21f402b00d945b18a9a4967874ca350b778a91dfacbefd3c84203bd35c3488e052e204cc166e0495a69cc2c8d9d4762f06c75f392846bef79ff27f221c991913da4520c7b864b05c22b40b362274796104bb1899a2e9bcdbd7b68a91a944ec3c8427331bb968302145492c501c6e3615e75020ada5e4d3aa8bb833ca673de3fe24666e73a20857565a42bb08e818970c9466ea702faa81720b25377122f2e5a6276794df34cb79e665f72731769b330ca1cbca18170b2aa4288905c858a1d7804e53d58592b3957cd8f2d84884312b5d2a6305569c6b0a90e5dc8d5444e8f242099ef3d7b7cf5c839066a42d7402816d65288906867580daf30f2acea1603ada4799b5134f41683332ce5504171cce0d8c0aa11f03dd40b98513bcec974d779421cc19d944a510e05686123b286ab9a15c9aaa2ea08cf5c5967bce47c8b3d116ba2ec890e0ea82a2897a1b7444e8f2020a1e376ecb3de723fcd958c14502cde6d20111117a0e17e750505d6ed4967bce4728b4b18e6a46b027288905439cbb986ba0dcc20a5e32a579d9ede7232cdad844390167736e303450dbc0a7a9ea020baedbe4ddf6ba9b7b224cda287445c0c1a5c40e02eb2855c345842e2fb400d76df260ef4bf3ca3b216c9a18e5aca08b718920a810fa0a2ecea1107bc1e4da0f7e576a6d7c684c0f8452131daa19819fa1d25600d452fbc037506e4126eba031e327df31fb81aabb6e1d3bf2a2b293105e4d6ca4ea82af8e9258007c45ad504853d5859bf06c629a8a059fcd35f92f22f4242e227479298888d076f0c1a524e2bb3875f8142ecea11444944a2107c6b884efdea236826fa0dc921031cacd05c85069cb6796d0772ba4a9ea92100ee5e4843a4a623e1bcff5e22242979784a8cb3936d7e4b315d436f0710e25215caa2227c0a524e2affd94a3d040b9a527a2b921c6257c3548e8810a69aaba34449a8ae40664a8b4e5a787a87de02342979786101a39b28e92989f3ea69e568873280961e5229b6bf4d1a9425fa5d040b9a521a2949b2be05262f9e70aea3014d35475e9961817f3cf03d48b0a11a1cb4bb7204335fae7156a96429c4309973a4a2cdf7c475da6d040b9a55c225ccc2f6542f7504853d5a55c90a21afd328eda0f3e22747949973a4a2c9fd4506f29cce150d225c2c57cf20655abb081724bbb204535fa244d4d57384839a5226c4a72491d25962ffa083d9ceb2f74b454047255848bf9e23aae2b7737f5374abc204535fae20e2a057e3df54ee9880c65e5923a4a2c3ffc9b7a4de127eae1d2112e15cd25112ee687a5d452ae9fd0a34a53c472095254a31f1aa9f9dc9dd431948e68a49c9c524789e5836dd44ddc3aeacd12120ed59853225ccc07df5243b9fd545509890aaa29a7204535fae018753a7581d0234a484428c92d7554dabc33843d047a06f5074a48808be494082511e30650bbb9e7a9d74a4a3453f19c820c15376e0cb589fb9ebabfa44423d5985b1a29d7b818f51475bed0434b4ac4a9746e895162dc83d45c6a1a75042525229444728acd5598b680aaa056539b4b4b2043c5730a52549d694ba94ba96fa87925261aa9646e49504da6ada50631e7093db8c4448c92484ea9a0c4326c13752113a37e4589099b4be4148b8b19e652e732f5d4c652134851e99c82662a61d84eea0c661f152f39e15012cb297554d2b0afa8aec43942474a4ed85c32a744298998f503f54f620a9546c909b894c47209b8b85947983f40aea43694a08871c99cd248351a7582b0bf305f53b34b5058194a62b9244ea58d3a8d6a257a0addbf0405125cd2ca21114aa2269d43ed256ea57e46290a9b1327872043c54dea4fed269653eb4b5220c149248724a8844923a8edc497d45da5296c85a61c12a79a4c2aa7dcf6ca84ee579a02094eea72479412df4da27e44890a5b416239035cd46f4ba9974a55c051484772463315f7dbe7d4cc9215487192b67245824af8ec5f425f58ba22aa204d568e88534d3ebb89da8fd215a85390262b374429f1d912ea855216564a419aac9c002eeaaf16ea8e521688aa483292139aa9b8af7a08dda7a405622a928ee6820495f0d58dd477286d81848a482c07c4a9265f3d45ad2975015749eaadc08b52e2ab66eaf69217564a499291a00317f55117a17b97bc4024a3243227e89aa9b88f86522994be4024a326ae1d6c09aace47312a510a03918c9aa4e7045a9c727db490bab7240622193511d70eb028d5e4a3b7a85b4a6320d2ac41d2d5c16553e2a3035479890c58cd1a4492d1a00267fbe66ca17b96ca00123a441aec806aa6a2a68ca6b6b5b99afa0da53310d722e96a2b905c2a66ca60eacb3695d4ae521a88667488a4a706511de5987211f5539b79d4a6921ab05c2d22c968f03854c29473a9636d16524b4b6b008e1e11371a3451ca35e5744a4e05b09a7aa8d406a2293d220d7670a54d39853b07c01bd46d253760d56912a9b78304949802ee1200cdd4d5a53780684a9348bd1d501153f653e500f653fd4a71008e2e917a3b305c2a6acaa7d46d00fea4ba95e640c4d525526f074423e598b2857a04e8216c06253a80584a9748bd1d088e1f9ea3d602fda896d21db09c8c2e11371a0071ca35e5516a2b504ebd5ec203b013da44dca8efa27e9849ed07a6502b4b7a00b6ab4dc48dfa2c42254db98692b3f000e594f800a2ae361137ea2b5062ca859c8367a8c9253f80684a9b881bcd2d2771bbb1911a56020488a5b489b8d15c82d728b97a3fd5bd2408104b691371a37e4951515326715b843d881221402ca54dc48dfac3f5c329bf53fcb6d22140ccd526d23435476099b6674b890051579b4872aa9513aed7767b691120ea6a1349575b39e09f5feb1a566a0488bada44d2f576e0e15e4dfbbb941e01a2ae361171c7051dded4f3284a910051579f48b2da0ab69e5a0e9d5fa20488bafa44a43e6a469ab20cc24c1d8b51b20488ba1e8824e7d806080da3b7681858ca0488ba1e8848c354af2cff6086d24d286d02445d2f44d2f5e33ca9a09a0dc3801ddc4d287902d8092f44245d3fd5d2e6508da6a1d31262c74d288502d8094fda36cc89e869a21ce380918fb5b4d93103c7718f056c27e38d88a41be6442d8548add0153e00d07f70efd3705cf75cc072521e654dbad5d553a3d168746a756d52142d7f1cff3d18402ce59d97cd289d03441bfd132fa903d875199fd82576002b96f24302257700441219d33256491e00b146b3e228d503d84eca9c044af800b0e3cd662450e207801d6bf42c538112406da38eeb412a6ea15450db683ce16a68762238ceff8fac5634ee38756edb84138b5a38feff941284fce7fffff71ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce73ffff9cf7ffef39ffffce7ffff6bd1ff29910c),
(13, 'Procuring of PC', 3, 'pending', '2025-01-08 10:04:19', 'approved', '2025-01-08 10:04:19', '2025-01-08 10:07:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `procurement_monitoring_report`
--

CREATE TABLE `procurement_monitoring_report` (
  `id` int(11) NOT NULL,
  `code_app` varchar(255) NOT NULL,
  `procurement_project` varchar(255) NOT NULL,
  `pmo_end_user` varchar(255) NOT NULL,
  `early_procurement_activity` enum('Yes','No') NOT NULL,
  `mode_of_procurement` varchar(255) NOT NULL,
  `pre_proc_conference` date DEFAULT NULL,
  `ads_post_ib` date DEFAULT NULL,
  `pre_bid_conference` date DEFAULT NULL,
  `eligibility_check` date DEFAULT NULL,
  `sub_open_bids` date DEFAULT NULL,
  `bid_evaluation` date DEFAULT NULL,
  `post_qualification` date DEFAULT NULL,
  `bac_recommendation` date DEFAULT NULL,
  `notice_of_award` date DEFAULT NULL,
  `contract_signing` date DEFAULT NULL,
  `delivery_completion` date DEFAULT NULL,
  `inspection_acceptance` date DEFAULT NULL,
  `total_abc` decimal(15,2) DEFAULT NULL,
  `mooe_abc` decimal(15,2) DEFAULT NULL,
  `co_abc` decimal(15,2) DEFAULT NULL,
  `total_contract_cost` decimal(15,2) DEFAULT NULL,
  `mooe_contract_cost` decimal(15,2) DEFAULT NULL,
  `co_contract_cost` decimal(15,2) DEFAULT NULL,
  `pre_bid_invitation` date DEFAULT NULL,
  `eligibility_check_invitation` date DEFAULT NULL,
  `sub_open_bids_invitation` date DEFAULT NULL,
  `bid_evaluation_invitation` date DEFAULT NULL,
  `post_qualification_invitation` date DEFAULT NULL,
  `delivery_completion_invitation` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `invited_observers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--

-- Table structure for table `procurement_titles`
--

CREATE TABLE `procurement_titles` (
  `id` int(11) NOT NULL,
  `page` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement_titles`
--

INSERT INTO `procurement_titles` (`id`, `page`, `title`, `subtitle`) VALUES
(1, 'app.php', 'Annual Procurement Plan', 'Consolidate all the details from the PPMP'),
(2, 'ppmp_list.php', 'PPMP List (Admin)', 'Manage, track, and approve PPMPs as an administrator.'),
(3, 'pr.php', 'Admin - Purchase Request List', 'Manage and monitor purchase requests for your organization.'),
(4, 'pmf.php', 'Procurement Modality Approval Form', NULL),
(5, 'rfq.php', 'Request for Quotation (RFQ)', NULL),
(6, 'aoq.php', 'Abstract of Quotation', 'Fill out the project details below'),
(7, 'reso.php', 'Resolution Form', NULL),
(8, 'noa.php', 'Notice of Award', 'Please fill out the form below to create a Notice of Award.'),
(9, 'ntp.php', 'Notice to Proceed', NULL),
(10, 'po.php', 'Purchase Order (PO)', NULL);

-- --------------------------------------------------------

-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) UNSIGNED NOT NULL,
  `philgeps_ref_no` varchar(50) DEFAULT NULL,
  `project_location` varchar(255) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `mode_of_procurement` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `telephone_no` varchar(50) DEFAULT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `place_of_delivery` varchar(255) DEFAULT NULL,
  `delivery_terms` varchar(100) DEFAULT NULL,
  `date_of_delivery` date DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `project_name` varchar(255) DEFAULT NULL,
  `noa_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `philgeps_ref_no`, `project_location`, `supplier`, `mode_of_procurement`, `address`, `city`, `telephone_no`, `tin`, `place_of_delivery`, `delivery_terms`, `date_of_delivery`, `payment_terms`, `project_name`, `noa_id`, `created_at`, `signature`) VALUES
(15, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 21, '2025-01-08 10:47:02', NULL),
(16, '42', 'CLA', 'Secret', 'Hiyes', 'Antipolo', 'Antipolo City', '08122356482', '416514f', 'Robinson', 'Cute', '2025-01-09', 'GCASH', 'Procuring of PC', 24, '2025-01-09 13:13:54', NULL),
(17, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 24, '2025-01-09 13:14:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) UNSIGNED NOT NULL,
  `purchase_order_id` int(11) UNSIGNED DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_items`
--

INSERT INTO `purchase_order_items` (`id`, `purchase_order_id`, `unit`, `quantity`, `description`, `unit_cost`, `amount`) VALUES
(7, 15, '1', 231.00, '0', 31.00, 7161.00),
(8, 17, 'Laptop', 65.00, 'fas', 53.00, 3445.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `pr_id` int(11) NOT NULL,
  `pr_number` varchar(20) NOT NULL,
  `approver` varchar(100) DEFAULT 'Pending',
  `submitted_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `pr_process_status` varchar(255) DEFAULT 'Not Applicable',
  `pr_process_link` varchar(255) DEFAULT NULL,
  `end_user_id` int(11) UNSIGNED NOT NULL,
  `ppmp_id` int(11) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`pr_id`, `pr_number`, `approver`, `submitted_date`, `status`, `pr_process_status`, `pr_process_link`, `end_user_id`, `ppmp_id`, `updated_at`, `signature`) VALUES
(1, 'PR-2025-01-318', 'Donut Doe', '2025-01-05 11:22:58', 'Approved', 'Completed', NULL, 2, 1, '2025-01-05 13:33:42', ''),
(13, 'PR-2025-01-112', 'Mariecris Camasis', '2025-01-08 10:08:12', 'Approved', 'Not Applicable', NULL, 3, 13, '2025-01-08 10:11:37', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_request_items`
--

CREATE TABLE `purchase_request_items` (
  `pri_id` int(11) NOT NULL,
  `pr_id` int(11) NOT NULL,
  `department` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_cost` decimal(10,2) NOT NULL,
  `total_cost` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `unit_cost`) STORED,
  `purpose` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_request_items`
--

INSERT INTO `purchase_request_items` (`pri_id`, `pr_id`, `department`, `section`, `inventory_id`, `quantity`, `unit_cost`, `purpose`, `created_at`, `updated_at`) VALUES
(1, 1, 'Life and Death', 'RA-12524', 2, 100, 1000.00, 'Unkown', '2025-01-05 11:22:58', '2025-01-05 11:22:58'),
(10, 13, 'COS', 'One', 10, 5, 100.00, 'Com-Lab', '2025-01-08 10:08:12', '2025-01-08 10:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `resets`
--

CREATE TABLE `resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(32) NOT NULL,
  `role` tinyint(1) NOT NULL COMMENT '1-admin,2-bac,3-bud,4-end',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resets`
--

INSERT INTO `resets` (`id`, `email`, `code`, `role`, `timestamp`) VALUES
(1, 'paganahinutak@gmail.com', '872152', 1, '2025-01-09 07:37:20'),
(2, 'paganahinutak@gmail.com', '786433', 1, '2025-01-09 07:38:42'),
(3, 'paganahinutak@gmail.com', '816471', 1, '2025-01-09 08:09:02'),
(4, 'emailgroup658@gmail.com', '204604', 1, '2025-01-09 12:07:17'),
(5, 'emailgroup658@gmail.com', '379068', 1, '2025-01-09 12:14:49'),
(6, 'gracearquiza21@gmail.com', '325047', 4, '2025-01-09 12:19:18'),
(11, 'emailgroup658@gmail.com', '204759', 1, '2025-01-09 12:29:07');

-- --------------------------------------------------------

--
-- Table structure for table `resolution`
--

CREATE TABLE `resolution` (
  `resolution_id` int(11) UNSIGNED NOT NULL,
  `aoq_id` int(11) UNSIGNED NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `total_amount_figures` decimal(10,2) NOT NULL,
  `total_amount_words` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq`
--

CREATE TABLE `rfq` (
  `rfq_id` int(11) UNSIGNED NOT NULL,
  `project_title` varchar(255) NOT NULL,
  `pr_request_number` varchar(20) DEFAULT NULL,
  `end_user` varchar(100) DEFAULT NULL,
  `date_created` date NOT NULL,
  `deadline_submission` date NOT NULL,
  `approved_budget` decimal(10,2) DEFAULT NULL,
  `procurement_mode` varchar(255) DEFAULT NULL,
  `signature` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq_items`
--

CREATE TABLE `rfq_items` (
  `id` int(11) NOT NULL,
  `rfq_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `general_name` varchar(255) NOT NULL,
  `tech_specification` text NOT NULL,
  `unit_cost` decimal(15,2) NOT NULL,
  `bidder_offer_specification` text DEFAULT NULL,
  `quoted_unit_price` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sector`
--

CREATE TABLE `sector` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `budget` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sector`
--

INSERT INTO `sector` (`id`, `name`, `budget`, `created_at`, `updated_at`) VALUES
(5, 'CLA', 0.00, '2025-01-09 18:16:15', '2025-01-09 18:16:15');

-- --------------------------------------------------------

--
-- Table structure for table `tup_specifications`
--

CREATE TABLE `tup_specifications` (
  `tup_id` int(11) UNSIGNED NOT NULL,
  `aoq_id` int(11) UNSIGNED NOT NULL,
  `tup_specification` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whereas_statements`
--

CREATE TABLE `whereas_statements` (
  `whereas_id` int(11) UNSIGNED NOT NULL,
  `resolution_id` int(11) UNSIGNED NOT NULL,
  `statement` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abstract_of_quotation`
--
ALTER TABLE `abstract_of_quotation`
  ADD PRIMARY KEY (`aoq_id`),
  ADD KEY `rfq_id` (`rfq_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `app`
--
ALTER TABLE `app`
  ADD PRIMARY KEY (`app_id`),
  ADD KEY `ppmp_id` (`ppmp_id`);

--
-- Indexes for table `bac_users`
--
ALTER TABLE `bac_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `budget_amount`
--
ALTER TABLE `budget_amount`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budget_users`
--
ALTER TABLE `budget_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `company_details`
--
ALTER TABLE `company_details`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `aoq_id` (`aoq_id`);

--
-- Indexes for table `end_users`
--
ALTER TABLE `end_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `fund`
--
ALTER TABLE `fund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fund_sources`
--
ALTER TABLE `fund_sources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `history_logs`
--
ALTER TABLE `history_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD UNIQUE KEY `item_no` (`item_no`);

--
-- Indexes for table `it_is_hereby_statements`
--
ALTER TABLE `it_is_hereby_statements`
  ADD PRIMARY KEY (`it_is_hereby_id`),
  ADD KEY `resolution_id` (`resolution_id`);

--
-- Indexes for table `notice_of_award`
--
ALTER TABLE `notice_of_award`
  ADD PRIMARY KEY (`noa_id`),
  ADD UNIQUE KEY `project_title` (`project_title`),
  ADD UNIQUE KEY `project_title_2` (`project_title`),
  ADD KEY `rfq_id` (`rfq_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pmaf`
--
ALTER TABLE `pmaf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_title` (`project_title`);

--
-- Indexes for table `ppmp_form`
--
ALTER TABLE `ppmp_form`
  ADD PRIMARY KEY (`ppmp_form_id`),
  ADD KEY `ppmp_id` (`ppmp_id`);

--
-- Indexes for table `ppmp_list`
--
ALTER TABLE `ppmp_list`
  ADD PRIMARY KEY (`ppmp_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `procurement_titles`
--
ALTER TABLE `procurement_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page` (`page`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_name` (`project_name`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`pr_id`),
  ADD UNIQUE KEY `pr_number` (`pr_number`),
  ADD UNIQUE KEY `pr_number_2` (`pr_number`),
  ADD KEY `end_user_id` (`end_user_id`),
  ADD KEY `ppmp_id` (`ppmp_id`);

--
-- Indexes for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD PRIMARY KEY (`pri_id`),
  ADD KEY `pr_id` (`pr_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `resets`
--
ALTER TABLE `resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resolution`
--
ALTER TABLE `resolution`
  ADD PRIMARY KEY (`resolution_id`),
  ADD KEY `aoq_id` (`aoq_id`);

--
-- Indexes for table `rfq`
--
ALTER TABLE `rfq`
  ADD PRIMARY KEY (`rfq_id`),
  ADD KEY `pr_request_number` (`pr_request_number`);

--
-- Indexes for table `rfq_items`
--
ALTER TABLE `rfq_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rfq_id` (`rfq_id`);

--
-- Indexes for table `sector`
--
ALTER TABLE `sector`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tup_specifications`
--
ALTER TABLE `tup_specifications`
  ADD PRIMARY KEY (`tup_id`),
  ADD KEY `aoq_id` (`aoq_id`);

--
-- Indexes for table `whereas_statements`
--
ALTER TABLE `whereas_statements`
  ADD PRIMARY KEY (`whereas_id`),
  ADD KEY `resolution_id` (`resolution_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abstract_of_quotation`
--
ALTER TABLE `abstract_of_quotation`
  MODIFY `aoq_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `app`
--
ALTER TABLE `app`
  MODIFY `app_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bac_users`
--
ALTER TABLE `bac_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `budget_amount`
--
ALTER TABLE `budget_amount`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `budget_users`
--
ALTER TABLE `budget_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `company_details`
--
ALTER TABLE `company_details`
  MODIFY `company_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `end_users`
--
ALTER TABLE `end_users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fund`
--
ALTER TABLE `fund`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fund_sources`
--
ALTER TABLE `fund_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `history_logs`
--
ALTER TABLE `history_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `it_is_hereby_statements`
--
ALTER TABLE `it_is_hereby_statements`
  MODIFY `it_is_hereby_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notice_of_award`
--
ALTER TABLE `notice_of_award`
  MODIFY `noa_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `pmaf`
--
ALTER TABLE `pmaf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `ppmp_form`
--
ALTER TABLE `ppmp_form`
  MODIFY `ppmp_form_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ppmp_list`
--
ALTER TABLE `ppmp_list`
  MODIFY `ppmp_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `procurement_titles`
--
ALTER TABLE `procurement_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `pr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  MODIFY `pri_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `resets`
--
ALTER TABLE `resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `resolution`
--
ALTER TABLE `resolution`
  MODIFY `resolution_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `rfq`
--
ALTER TABLE `rfq`
  MODIFY `rfq_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `rfq_items`
--
ALTER TABLE `rfq_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `sector`
--
ALTER TABLE `sector`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tup_specifications`
--
ALTER TABLE `tup_specifications`
  MODIFY `tup_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `whereas_statements`
--
ALTER TABLE `whereas_statements`
  MODIFY `whereas_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abstract_of_quotation`
--
ALTER TABLE `abstract_of_quotation`
  ADD CONSTRAINT `abstract_of_quotation_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfq` (`rfq_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_abstract_of_quotation_rfq_id` FOREIGN KEY (`rfq_id`) REFERENCES `rfq` (`rfq_id`) ON DELETE CASCADE;

--
-- Constraints for table `app`
--
ALTER TABLE `app`
  ADD CONSTRAINT `app_ibfk_1` FOREIGN KEY (`ppmp_id`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE CASCADE;

--
-- Constraints for table `company_details`
--
ALTER TABLE `company_details`
  ADD CONSTRAINT `company_details_ibfk_1` FOREIGN KEY (`aoq_id`) REFERENCES `abstract_of_quotation` (`aoq_id`) ON DELETE CASCADE;

--
-- Constraints for table `it_is_hereby_statements`
--
ALTER TABLE `it_is_hereby_statements`
  ADD CONSTRAINT `it_is_hereby_statements_ibfk_1` FOREIGN KEY (`resolution_id`) REFERENCES `resolution` (`resolution_id`) ON DELETE CASCADE;

--
-- Constraints for table `notice_of_award`
--
ALTER TABLE `notice_of_award`
  ADD CONSTRAINT `fk_notice_of_award_rfq_id` FOREIGN KEY (`rfq_id`) REFERENCES `rfq` (`rfq_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notice_of_award_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfq` (`rfq_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `end_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pmaf`
--
ALTER TABLE `pmaf`
  ADD CONSTRAINT `fk_pmaf_project_title` FOREIGN KEY (`project_title`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pmaf_ibfk_1` FOREIGN KEY (`project_title`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE CASCADE;

--
-- Constraints for table `ppmp_form`
--
ALTER TABLE `ppmp_form`
  ADD CONSTRAINT `ppmp_form_ibfk_1` FOREIGN KEY (`ppmp_id`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE CASCADE;

--
-- Constraints for table `ppmp_list`
--
ALTER TABLE `ppmp_list`
  ADD CONSTRAINT `fk_ppmp_list_user_id` FOREIGN KEY (`user_id`) REFERENCES `end_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ppmp_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `end_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_purchase_orders_project_name` FOREIGN KEY (`project_name`) REFERENCES `notice_of_award` (`project_title`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`project_name`) REFERENCES `notice_of_award` (`project_title`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `fk_purchase_requests_end_user_id` FOREIGN KEY (`end_user_id`) REFERENCES `end_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_purchase_requests_ppmp_id` FOREIGN KEY (`ppmp_id`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`end_user_id`) REFERENCES `end_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`ppmp_id`) REFERENCES `ppmp_list` (`ppmp_id`) ON DELETE SET NULL;

--
-- Constraints for table `purchase_request_items`
--
ALTER TABLE `purchase_request_items`
  ADD CONSTRAINT `purchase_request_items_ibfk_1` FOREIGN KEY (`pr_id`) REFERENCES `purchase_requests` (`pr_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_request_items_ibfk_2` FOREIGN KEY (`inventory_id`) REFERENCES `inventory` (`inventory_id`) ON DELETE CASCADE;

--
-- Constraints for table `resolution`
--
ALTER TABLE `resolution`
  ADD CONSTRAINT `fk_resolution_aoq_id` FOREIGN KEY (`aoq_id`) REFERENCES `abstract_of_quotation` (`aoq_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resolution_ibfk_1` FOREIGN KEY (`aoq_id`) REFERENCES `abstract_of_quotation` (`aoq_id`) ON DELETE CASCADE;

--
-- Constraints for table `rfq`
--
ALTER TABLE `rfq`
  ADD CONSTRAINT `fk_rfq_pr_request_number` FOREIGN KEY (`pr_request_number`) REFERENCES `purchase_requests` (`pr_number`) ON DELETE CASCADE,
  ADD CONSTRAINT `rfq_ibfk_1` FOREIGN KEY (`pr_request_number`) REFERENCES `purchase_requests` (`pr_number`) ON DELETE CASCADE;

--
-- Constraints for table `rfq_items`
--
ALTER TABLE `rfq_items`
  ADD CONSTRAINT `rfq_items_ibfk_1` FOREIGN KEY (`rfq_id`) REFERENCES `rfq` (`rfq_id`) ON DELETE CASCADE;

--
-- Constraints for table `tup_specifications`
--
ALTER TABLE `tup_specifications`
  ADD CONSTRAINT `tup_specifications_ibfk_1` FOREIGN KEY (`aoq_id`) REFERENCES `abstract_of_quotation` (`aoq_id`) ON DELETE CASCADE;

--
-- Constraints for table `whereas_statements`
--
ALTER TABLE `whereas_statements`
  ADD CONSTRAINT `whereas_statements_ibfk_1` FOREIGN KEY (`resolution_id`) REFERENCES `resolution` (`resolution_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

----arquiza
ALTER TABLE end_users ADD COLUMN sector VARCHAR(255) NOT NULL AFTER last_name;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_no` varchar(10) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_no`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(25, 'CA-00025', 'Office ', 'opis', '2025-01-10 21:22:34', '2025-01-10 21:23:42');



CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_no` varchar(50) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit_of_measurement` varchar(50) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`item_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `item_no`, `item_name`, `unit_of_measurement`, `category_id`, `created_at`, `updated_at`) VALUES
(30, 'ITEM-0001', 'Chair', 'piece', NULL, '2025-01-10 21:20:41', '2025-01-10 21:21:10'),
(31, 'ITEM-0002', 'tables', 'piece', NULL, '2025-01-10 21:20:55', '2025-01-10 21:20:55'),
(32, 'ITEM-0003', 'Bond Paper', 'bundle', 25, '2025-01-10 21:22:48', '2025-01-10 21:23:19');
=======
-- Table for access dates

CREATE TABLE `access_dates` (
  `id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `access_dates` (`id`, `start_date`, `end_date`) VALUES
(1, '2025-01-11', '2025-01-30');

ALTER TABLE `access_dates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `access_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


--Table for 'enable/disable' Update PPMP 
CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `updates_enabled` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


INSERT INTO `settings` (`id`, `updates_enabled`) VALUES
(1, 0),
(2, 0);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--PR--
ALTER TABLE bac_users ADD COLUMN position VARCHAR(255) NULL;

CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    updates_enabled TINYINT(1) NOT NULL DEFAULT 0
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE access_dates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    start_date DATE,
    end_date DATE
);

ALTER TABLE ppmp_list ADD date_bound DATE;

ALTER TABLE settings ADD COLUMN pr_updates_enabled TINYINT(1) DEFAULT 0;

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
