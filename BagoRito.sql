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
(2, 'ppmp_list.php', 'Project Procurement Management Plan', 'Manage, track, and approve PPMPs as an administrator.'),
(3, 'pr.php', 'Purchase Request List', 'Manage and monitor purchase requests for your organization.'),
(4, 'pmf.php', 'Procurement Modality Approval Form', NULL),
(5, 'rfq.php', 'Request for Quotationz', NULL),
(6, 'aoq.php', 'Abstract of Quotationz', NULL),
(7, 'reso.php', 'Resolution Form', NULL),
(8, 'noa.php', 'Notice of Award', NULL),
(9, 'ntp.php', 'Notice to Proceed', NULL),
(10, 'po.php', 'Purchase Order (PO)', NULL),
(11, 'pmr.php', 'Procurement Monitoring Report', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `procurement_titles`
--
ALTER TABLE `procurement_titles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page` (`page`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `procurement_titles`
--
ALTER TABLE `procurement_titles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;