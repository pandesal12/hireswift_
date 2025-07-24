-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 23, 2025 at 07:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hireswift`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `resume_pdf_path` varchar(255) NOT NULL COMMENT 'Path to the uploaded PDF resume',
  `parsed_resume_path` varchar(255) DEFAULT NULL COMMENT 'Path to the parsed resume JSON file',
  `status` enum('Pending','Reviewing','Shortlisted','Interviewed','Accepted','Rejected') DEFAULT 'Pending',
  `score` decimal(5,2) DEFAULT 0.00 COMMENT 'Match score from 0-100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `job_id`, `applicant_name`, `email`, `phone`, `resume_pdf_path`, `parsed_resume_path`, `status`, `score`, `created_at`, `updated_at`) VALUES
(23, 23, 'RHODA JACKSON', 'rhodajackson@email.com', '123) 456-7890\n', 'Uploads/graphic-designer-resume-example_687e49331258b.pdf', 'Uploads/parsed/graphic-designer-resume-example_687e49331258b_parsed.json', 'Pending', 40.00, '2025-07-21 14:05:39', '2025-07-21 14:06:01'),
(24, 1, 'Malik Rabb', 'mrabb@email.com', '315) 245-0902 ', 'Uploads/Sample-Resume-Car-Sales_687e4bb75a16c.pdf', 'Uploads/parsed/Sample-Resume-Car-Sales_687e4bb75a16c_parsed.json', 'Accepted', 0.00, '2025-07-21 14:16:23', '2025-07-21 14:32:47'),
(25, 1, 'Cindy Lou Who', 'cindylou@nova.edu', '954-555-1212 ', 'Uploads/sample_687e4c6d1fca7.pdf', 'Uploads/parsed/sample_687e4c6d1fca7_parsed.json', 'Rejected', 60.00, '2025-07-21 14:19:25', '2025-07-21 14:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `employment_type` enum('Full Time','Part Time','Contract','Internship') NOT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `description` text NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_by` varchar(64) DEFAULT NULL,
  `link_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `employment_type`, `skills`, `education`, `description`, `status`, `created_by`, `link_id`, `created_at`, `updated_at`) VALUES
(1, 'Senior Software Engineer', 'Full Time', '[\"PHP\", \"JavaScript\", \"MySQL\", \"Laravel\", \"Vue.js\"]', '[\"Bachelor\'s Degree in Computer Science\", \"Master\'s Degree preferred\"]', 'We are seeking a highly skilled Senior Software Engineer to join our dynamic development team. The ideal candidate will have extensive experience in web development, database design, and modern frameworks. You will be responsible for designing, developing, and maintaining complex web applications while mentoring junior developers.', 'Active', '8', 1, '2025-06-09 18:18:40', '2025-07-20 17:34:32'),
(2, 'Data Analyst', 'Part Time', '[\"Python\", \"SQL\", \"Excel\", \"Tableau\", \"Power BI\", \"Statistics\"]', '[\"Bachelor\'s Degree in Statistics\", \"Mathematics\", \"Computer Science\"]', 'Join our analytics team as a Data Analyst where you will transform raw data into actionable insights. You will work with various stakeholders to understand business requirements, create reports, and develop dashboards that drive strategic decision-making.', 'Active', '1', NULL, '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(3, 'UI/UX Designer', 'Full Time', '[\"Figma\", \"Adobe XD\", \"Photoshop\", \"Illustrator\", \"Sketch\", \"Prototyping\"]', '[\"Bachelor\'s Degree in Design\", \"HCI\", \"Related field\"]', 'We are looking for a creative UI/UX Designer to enhance user experience across our digital platforms. You will conduct user research, create wireframes and prototypes, and collaborate with development teams to implement intuitive and visually appealing interfaces.', 'Active', '1', NULL, '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(4, 'Project Manager', 'Full Time', '[\"Agile\", \"Scrum\", \"JIRA\", \"Project Planning\", \"Risk Management\", \"Leadership\"]', '[\"Bachelor\'s Degree\", \"PMP Certification preferred\"]', 'Lead cross-functional teams and drive project success as our Project Manager. You will be responsible for planning, executing, and closing projects while ensuring they are delivered on time, within scope, and budget. Strong communication and leadership skills are essential.', '', '1', NULL, '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(5, 'DevOps Engineer', 'Contract', '[\"Docker\", \"Kubernetes\", \"AWS\", \"Jenkins\", \"Git\", \"Linux\", \"Python\"]', '[\"Bachelor\'s Degree in Engineering\", \"Computer Science\"]', 'Join our infrastructure team as a DevOps Engineer to streamline our development and deployment processes. You will work with containerization, cloud platforms, and automation tools to ensure reliable and scalable application delivery.', 'Active', '1', NULL, '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(6, 'Marketing Intern', 'Internship', '[\"Social Media\", \"Content Creation\", \"Analytics\", \"Adobe Creative Suite\"]', '[\"Currently pursuing Bachelor\'s Degree\", \"Marketing\", \"Communications\"]', 'Gain hands-on experience in digital marketing with our internship program. You will assist in creating marketing campaigns, managing social media accounts, and analyzing marketing metrics while learning from experienced professionals.', 'Active', '1', NULL, '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(17, 'Bodyguard', 'Full Time', '[\"999 strength\",\"muscle\",\"humble\"]', '[\"PhD in Chemistry\"]', 'we need to cook', 'Active', 'joe@gmail.com', NULL, '2025-07-10 14:08:03', '2025-07-10 14:08:03'),
(20, 'Talent Manager', 'Full Time', '[\"Talent managament\",\"Familiarity with laws pertaining to media\",\"Social media management\",\"Finance management\"]', '[\"Degree in Business Management optional\"]', 'We\'re looking for someone to manage our company\'s talents', 'Active', 'johndoe@abcdmail.com', NULL, '2025-07-18 17:40:36', '2025-07-18 18:31:29'),
(21, '325235', 'Full Time', '[\"5316\"]', '[\"7574\"]', 'gfgfdgdgh', 'Active', 'johndoe@abcdmail.com', NULL, '2025-07-18 18:33:46', '2025-07-18 18:33:46'),
(23, 'Art Designer', 'Part Time', '[\"Adobe Photoshop\",\"Illustrator\",\"Figma\",\"Creativity\",\"Color Theory\"]', '[\"Bachelor\'s Degree in Fine Arts\",\"B.F.A.\"]', 'We are looking for a creative and detail-oriented Art Designer to join our team.\r\nYou will be responsible for producing high-quality visual content across digital and print platforms.\r\nYour work will involve designing graphics, layouts, and branding materials that align with our project goals and company image.\r\nStrong knowledge of design tools like Adobe Photoshop, Illustrator, and Figma is essential.\r\nYou should have a solid understanding of visual hierarchy, color theory, and typography.\r\nThe ideal candidate can take creative direction and bring fresh ideas to life while meeting tight deadlines.', 'Inactive', '8', 1, '2025-07-21 13:19:42', '2025-07-21 14:44:20'),
(24, 'Event Coordinator', 'Contract', '[\"Event Planning\",\"Budget Management\",\"Communication\",\"Vendor Coordination\",\"Time Management\",\"Problem Solving\",\"Logistics\"]', '[\"Bachelor\'s Degree in Hospitality Management\"]', 'We are seeking a highly organized and proactive Event Coordinator to manage and execute company events, conferences, and meetings. You will be responsible for planning logistics, managing budgets, coordinating with vendors, and ensuring seamless on-site execution. Strong communication and multitasking skills are essential. The ideal candidate thrives under pressure and can handle last-minute changes without affecting the overall quality of the event.', 'Inactive', '9', 2, '2025-07-21 15:04:35', '2025-07-21 15:05:32');

-- --------------------------------------------------------

--
-- Table structure for table `link`
--

CREATE TABLE `link` (
  `link_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `link`
--

INSERT INTO `link` (`link_id`, `user_id`, `company`) VALUES
(1, 8, 'E-Trabaho'),
(2, 9, 'kiela');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `phone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `phone`) VALUES
(8, 'john@gmail.com', 'b5e42f49ab3acf8f6c2ccf99f604a17f', 'John Aldrine Lim', '9951231234'),
(9, 'kiel@gmail.com', '0194328768bc83f494224f92256cf996', 'Kiel Talampas', '1231231234');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_job_id` (`job_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_score` (`score`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_link_id` (`link_id`);

--
-- Indexes for table `link`
--
ALTER TABLE `link`
  ADD PRIMARY KEY (`link_id`),
  ADD UNIQUE KEY `idx_company_unique` (`company`),
  ADD KEY `fkey_user_link` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `link`
--
ALTER TABLE `link`
  MODIFY `link_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_application_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_link_id` FOREIGN KEY (`link_id`) REFERENCES `link` (`link_id`) ON DELETE SET NULL;

--
-- Constraints for table `link`
--
ALTER TABLE `link`
  ADD CONSTRAINT `fkey_user_link` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
