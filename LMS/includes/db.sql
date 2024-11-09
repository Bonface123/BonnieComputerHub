-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2024 at 09:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bch_lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assignment_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `max_score` int(11) NOT NULL,
  `instructions` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_name`, `description`, `created_by`, `created_at`, `updated_at`, `name`) VALUES
(18, 'HTML and CSS', 'The HTML and CSS course at Bonnie Computer Hub (BCH) is designed to introduce students to the foundational technologies of web development. The course covers HTML (HyperText Markup Language) for creating and structuring web pages, and CSS (Cascading Style Sheets) for styling and layout. Students will learn how to build simple yet functional websites, with a focus on responsive design to ensure compatibility across various devices. The course is aimed at high school graduates, university students, and tech enthusiasts with little or no prior experience, and combines theory with practical assignments to reinforce learning.', 24, '2024-11-08 06:04:30', '2024-11-08 06:04:30', ''),
(19, 'JavaScript ', 'The JavaScript (JS) course at Bonnie Computer Hub (BCH) teaches students how to add interactivity to websites. It covers basic JavaScript concepts like variables, functions, loops, and DOM manipulation, enabling students to create dynamic and responsive web pages.', 24, '2024-11-08 06:05:46', '2024-11-08 06:05:46', ''),
(20, 'PHP', 'The PHP course at Bonnie Computer Hub (BCH) introduces students to server-side scripting for web development. It covers the basics of PHP, including syntax, variables, functions, and working with databases (MySQL) to create dynamic, data-driven websites. Students will learn to build interactive applications with PHP, enhancing their web development skills.', 24, '2024-11-08 06:06:26', '2024-11-08 06:06:26', '');

-- --------------------------------------------------------

--
-- Table structure for table `course_assignments`
--

CREATE TABLE `course_assignments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `due_date` date NOT NULL,
  `marks` int(11) NOT NULL,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_assignments`
--

INSERT INTO `course_assignments` (`id`, `course_id`, `title`, `description`, `due_date`, `marks`, `instructions`) VALUES
(11, 18, 'Assignment 1', 'what is the importance of using websites in our modern world?', '2024-11-09', 12, 'Answer faithfully'),
(12, 19, 'Javascript Assignment 1', 'why should we use JavaScript ', '2024-11-09', 23, 'answer diligently '),
(13, 20, 'PHP Assignement 1', 'why should we use php?', '2024-11-09', 12, 'Answer according to what you know');

-- --------------------------------------------------------

--
-- Table structure for table `course_materials`
--

CREATE TABLE `course_materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `material_name` varchar(255) NOT NULL,
  `material_description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_materials`
--

INSERT INTO `course_materials` (`id`, `course_id`, `material_name`, `material_description`, `file_path`, `uploaded_at`, `file_name`, `description`) VALUES
(10, 18, '', NULL, '672dad7097528_ASSIGNMENT 1.docx', '2024-11-08 06:19:28', '', 'The Basics of HTML'),
(11, 19, '', NULL, '672dad9d4c82d_messaging systems introduction (1).docx', '2024-11-08 06:20:13', '', 'Why JavaScript '),
(12, 20, '', NULL, '672dadd0b4353_MIME.pdf', '2024-11-08 06:21:04', '', 'Introduction to Client_Server Side computing');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`user_id`, `course_id`, `enrollment_date`, `course_name`) VALUES
(20, 18, '2024-11-08 06:07:11', ''),
(20, 19, '2024-11-08 06:07:19', ''),
(20, 20, '2024-11-08 06:07:26', '');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `comments` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `student_id`, `course_id`, `comments`, `created_at`) VALUES
(4, 20, 18, 'Please enroll yourself', '2024-11-08 06:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_comments`
--

CREATE TABLE `feedback_comments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `submission_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submitted_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `grade` float DEFAULT NULL,
  `submission_status` varchar(20) DEFAULT 'pending',
  `submission_date` datetime DEFAULT current_timestamp(),
  `course_id` int(11) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(50) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `assignment_id`, `student_id`, `submitted_on`, `grade`, `submission_status`, `submission_date`, `course_id`, `file_path`, `feedback`, `last_updated`, `status`) VALUES
(0, 11, 20, '2024-11-08 07:43:03', NULL, 'pending', '2024-11-08 10:43:03', 18, '../uploads/assignments/ASSIGNMENT 1.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 11, 20, '2024-11-08 07:43:19', NULL, 'pending', '2024-11-08 10:43:19', 18, '../uploads/assignments/ASSIGNMENT 1.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:44:14', NULL, 'pending', '2024-11-08 10:44:14', 19, '../uploads/assignments/Email Protocols 2.pdf', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:48:11', NULL, 'pending', '2024-11-08 10:48:11', 19, '../uploads/assignments/Email Protocols 2.pdf', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:49:33', NULL, 'pending', '2024-11-08 10:49:33', 19, '../uploads/assignments/Email Protocols 2.pdf', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:50:21', NULL, 'pending', '2024-11-08 10:50:21', 19, '../uploads/assignments/Email Protocols 2.pdf', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:50:45', NULL, 'pending', '2024-11-08 10:50:45', 19, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:55:37', NULL, 'pending', '2024-11-08 10:55:37', 19, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:56:40', NULL, 'pending', '2024-11-08 10:56:40', 19, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:56:58', NULL, 'pending', '2024-11-08 10:56:58', 19, '../uploads/assignments/APMS_PROJECT_DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:57:12', NULL, 'pending', '2024-11-08 10:57:12', 19, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:57:27', NULL, 'pending', '2024-11-08 10:57:27', 19, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 07:57:37', NULL, 'pending', '2024-11-08 10:57:37', 19, '../uploads/assignments/APMS FULL DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:04:46', NULL, 'pending', '2024-11-08 11:04:46', 19, '../uploads/assignments/APMS FULL DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:05:07', NULL, 'pending', '2024-11-08 11:05:07', 19, '../uploads/assignments/APMS FULL DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:06:33', NULL, 'pending', '2024-11-08 11:06:33', 19, '../uploads/assignments/APMS FULL DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:07:08', NULL, 'pending', '2024-11-08 11:07:08', 19, '../uploads/assignments/Email Protocols 2.pdf', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:13:53', NULL, 'pending', '2024-11-08 11:13:53', 19, '../uploads/assignments/APMS CHAPTER 1,2 & 3 DOCUMENTATION.docx', NULL, '2024-11-08 08:17:37', 'Pending'),
(0, 12, 20, '2024-11-08 08:25:14', NULL, 'pending', '2024-11-08 11:25:14', 19, '../uploads/assignments/APMS CHAPTER 1,2 & 3 DOCUMENTATION.docx', NULL, '2024-11-08 08:25:14', 'Pending'),
(0, 11, 20, '2024-11-08 08:28:50', NULL, 'pending', '2024-11-08 11:28:50', 18, '../uploads/assignments/APMS_PROJECT_DOCUMENTATION.docx', NULL, '2024-11-08 08:28:50', 'Pending'),
(0, 11, 20, '2024-11-08 08:29:49', NULL, 'pending', '2024-11-08 11:29:49', 18, '../uploads/assignments/APMS_PROJECT_DOCUMENTATION.docx', NULL, '2024-11-08 08:29:49', 'Pending'),
(0, 11, 20, '2024-11-08 08:31:05', NULL, 'pending', '2024-11-08 11:31:05', 18, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:31:05', 'Pending'),
(0, 11, 20, '2024-11-08 08:31:35', NULL, 'pending', '2024-11-08 11:31:35', 18, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:31:35', 'Pending'),
(0, 11, 20, '2024-11-08 08:31:45', NULL, 'pending', '2024-11-08 11:31:45', 18, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:31:45', 'Pending'),
(0, 12, 20, '2024-11-08 08:32:04', NULL, 'pending', '2024-11-08 11:32:04', 19, '../uploads/assignments/APMS CHAPTER 1,2 & 3 DOCUMENTATION.docx', NULL, '2024-11-08 08:32:04', 'Pending'),
(0, 12, 20, '2024-11-08 08:33:42', NULL, 'pending', '2024-11-08 11:33:42', 19, '../uploads/assignments/APMS CHAPTER 1,2 & 3 DOCUMENTATION.docx', NULL, '2024-11-08 08:33:42', 'Pending'),
(0, 12, 20, '2024-11-08 08:34:12', NULL, 'pending', '2024-11-08 11:34:12', 19, '../uploads/assignments/APMS CHAPTER 1,2 & 3 DOCUMENTATION.docx', NULL, '2024-11-08 08:34:12', 'Pending'),
(0, 11, 20, '2024-11-08 08:34:53', NULL, 'pending', '2024-11-08 11:34:53', 18, '../uploads/assignments/APMS-4.2.docx', NULL, '2024-11-08 08:34:53', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role_id` int(11) NOT NULL,
  `submission_status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`, `role_id`, `submission_status`) VALUES
(20, 'Faith', 'faith@gmail.com', '$2y$10$k6msUNk9vjl5eHAOuNNyT.LI9L.dwIVCJJj1XQGoiBhRAhwQNLKfK', 'student', '2024-11-08 05:54:25', '2024-11-08 05:54:25', 0, 'pending'),
(21, 'Paul', 'paul@gmail.com', '$2y$10$PidxplaSe5yB5Lf/M4T2y.31neCQCNElYw6zvJz4ZJmO27GZ2q1MC', 'instructor', '2024-11-08 05:55:18', '2024-11-08 05:55:18', 0, 'pending'),
(24, 'Bonface', 'ondusobonface9@gmail.com', '$2y$10$ivxTW86AbriWjt8wtOd/5Oe8ClCXeTTqY1FoAujsPAvz6IvfLz7zO', 'admin', '2024-11-08 06:00:41', '2024-11-08 06:00:41', 0, 'pending');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `feedback_comments`
--
ALTER TABLE `feedback_comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `course_materials`
--
ALTER TABLE `course_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback_comments`
--
ALTER TABLE `feedback_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD CONSTRAINT `course_assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `course_materials`
--
ALTER TABLE `course_materials`
  ADD CONSTRAINT `course_materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
