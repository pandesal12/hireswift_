-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 09, 2025 at 10:16 PM
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
(1, 1, 'John Smith', 'john.smith@example.com', '+1 (555) 123-4567', 'uploads/resumes/john_smith_resume.pdf', 'uploads/parsed/john_smith_parsed.json', 'Shortlisted', 87.50, '2025-06-09 18:46:10', '2025-06-09 18:46:10');

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
  `status` enum('Active','Closed','Draft') DEFAULT 'Active',
  `created_by` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `employment_type`, `skills`, `education`, `description`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Senior Software Engineer', 'Full Time', '[\"PHP\", \"JavaScript\", \"MySQL\", \"Laravel\", \"Vue.js\"]', '[\"Bachelor\'s Degree in Computer Science\", \"Master\'s Degree preferred\"]', 'We are seeking a highly skilled Senior Software Engineer to join our dynamic development team. The ideal candidate will have extensive experience in web development, database design, and modern frameworks. You will be responsible for designing, developing, and maintaining complex web applications while mentoring junior developers.', 'Active', 'john@gmail.com', '2025-06-09 18:18:40', '2025-06-09 18:22:20'),
(2, 'Data Analyst', 'Part Time', '[\"Python\", \"SQL\", \"Excel\", \"Tableau\", \"Power BI\", \"Statistics\"]', '[\"Bachelor\'s Degree in Statistics\", \"Mathematics\", \"Computer Science\"]', 'Join our analytics team as a Data Analyst where you will transform raw data into actionable insights. You will work with various stakeholders to understand business requirements, create reports, and develop dashboards that drive strategic decision-making.', 'Active', '1', '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(3, 'UI/UX Designer', 'Full Time', '[\"Figma\", \"Adobe XD\", \"Photoshop\", \"Illustrator\", \"Sketch\", \"Prototyping\"]', '[\"Bachelor\'s Degree in Design\", \"HCI\", \"Related field\"]', 'We are looking for a creative UI/UX Designer to enhance user experience across our digital platforms. You will conduct user research, create wireframes and prototypes, and collaborate with development teams to implement intuitive and visually appealing interfaces.', 'Active', '1', '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(4, 'Project Manager', 'Full Time', '[\"Agile\", \"Scrum\", \"JIRA\", \"Project Planning\", \"Risk Management\", \"Leadership\"]', '[\"Bachelor\'s Degree\", \"PMP Certification preferred\"]', 'Lead cross-functional teams and drive project success as our Project Manager. You will be responsible for planning, executing, and closing projects while ensuring they are delivered on time, within scope, and budget. Strong communication and leadership skills are essential.', 'Closed', '1', '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(5, 'DevOps Engineer', 'Contract', '[\"Docker\", \"Kubernetes\", \"AWS\", \"Jenkins\", \"Git\", \"Linux\", \"Python\"]', '[\"Bachelor\'s Degree in Engineering\", \"Computer Science\"]', 'Join our infrastructure team as a DevOps Engineer to streamline our development and deployment processes. You will work with containerization, cloud platforms, and automation tools to ensure reliable and scalable application delivery.', 'Active', '1', '2025-06-09 18:18:40', '2025-06-09 18:18:40'),
(6, 'Marketing Intern', 'Internship', '[\"Social Media\", \"Content Creation\", \"Analytics\", \"Adobe Creative Suite\"]', '[\"Currently pursuing Bachelor\'s Degree\", \"Marketing\", \"Communications\"]', 'Gain hands-on experience in digital marketing with our internship program. You will assist in creating marketing campaigns, managing social media accounts, and analyzing marketing metrics while learning from experienced professionals.', 'Active', '1', '2025-06-09 18:18:40', '2025-06-09 18:18:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `email` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL,
  `phone` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`email`, `password`, `name`, `phone`) VALUES
('john@gmail.com', '527bd5b5d689e2c32ae974c6229ff785', 'John Aldrine Lim', '9957269133');

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
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `fk_application_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
