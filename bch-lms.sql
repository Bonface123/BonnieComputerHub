-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2024 at 06:23 PM
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
-- Database: `bch-lms`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` int(11) NOT NULL DEFAULT 10,
  `due_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignments`
--

INSERT INTO `assignments` (`id`, `module_id`, `title`, `description`, `weight`, `due_date`, `created_at`) VALUES
(1, 1, 'Create a Personal Portfolio', 'Build a personal portfolio website using HTML and CSS. Include:\r\n- Header with navigation\r\n- About section\r\n- Skills section\r\n- Contact form\r\nUse proper semantic HTML and responsive CSS.', 30, '2024-11-17 00:00:00', '2024-11-10 12:46:43'),
(2, 1, 'Restaurant Menu Layout', 'Design a restaurant menu layout using HTML and CSS with:\r\n- Multiple sections (appetizers, main course, desserts)\r\n- Prices and descriptions\r\n- Responsive design for mobile and desktop\r\n- Proper use of CSS Grid or Flexbox', 20, '2024-11-24 00:00:00', '2024-11-10 12:46:43'),
(3, 2, 'Interactive Form Validation', 'Create a registration form with JavaScript validation:\r\n- Email format validation\r\n- Password strength checker\r\n- Real-time feedback\r\n- Custom error messages\r\n- Submit button enables only when all fields are valid', 25, '2024-12-01 00:00:00', '2024-11-10 12:46:43'),
(4, 2, 'Todo List Application', 'Build a todo list application with JavaScript:\r\n- Add/Remove tasks\r\n- Mark tasks as complete\r\n- Filter tasks (all, active, completed)\r\n- Local storage integration\r\n- Drag and drop functionality', 35, '2024-12-08 00:00:00', '2024-11-10 12:46:43'),
(5, 3, 'User Authentication System', 'Develop a complete user authentication system:\r\n- User registration\r\n- Login/Logout functionality\r\n- Password hashing\r\n- Session management\r\n- Password reset feature\r\n- MySQL database integration', 40, '2024-12-15 00:00:00', '2024-11-10 12:46:43'),
(6, 3, 'Blog Management System', 'Create a blog management system with:\r\n- Post creation/editing/deletion\r\n- Comment system\r\n- User roles (admin/author/reader)\r\n- Category management\r\n- Search functionality\r\n- Database design and implementation', 40, '2024-12-22 00:00:00', '2024-11-10 12:46:43');

-- --------------------------------------------------------

--
-- Table structure for table `coding_exercises`
--

CREATE TABLE `coding_exercises` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `instructions` text NOT NULL,
  `starter_code` text DEFAULT NULL,
  `solution_code` text DEFAULT NULL,
  `test_cases` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coding_exercises`
--

INSERT INTO `coding_exercises` (`id`, `section_id`, `instructions`, `starter_code`, `solution_code`, `test_cases`) VALUES
(1, 2, 'Create a basic HTML page with a main heading that says \"Welcome\" and a paragraph with some text.', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>My First Page</title>\r\n</head>\r\n<body>\r\n    <!-- Write your code here -->\r\n</body>\r\n</html>', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>My First Page</title>\r\n</head>\r\n<body>\r\n    <h1>Welcome</h1>\r\n    <p>This is my first HTML page!</p>\r\n</body>\r\n</html>', NULL),
(2, 5, 'Create a simple calculator that adds two numbers.', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Calculator</title>\r\n</head>\r\n<body>\r\n    <input type=\"number\" id=\"num1\">\r\n    <input type=\"number\" id=\"num2\">\r\n    <button onclick=\"calculate()\">Add</button>\r\n    <p id=\"result\"></p>\r\n    <script>\r\n        // Write your code here\r\n    </script>\r\n</body>\r\n</html>', '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Calculator</title>\r\n</head>\r\n<body>\r\n    <input type=\"number\" id=\"num1\">\r\n    <input type=\"number\" id=\"num2\">\r\n    <button onclick=\"calculate()\">Add</button>\r\n    <p id=\"result\"></p>\r\n    <script>\r\n        function calculate() {\r\n            const num1 = parseFloat(document.getElementById(\"num1\").value);\r\n            const num2 = parseFloat(document.getElementById(\"num2\").value);\r\n            document.getElementById(\"result\").textContent = num1 + num2;\r\n        }\r\n    </script>\r\n</body>\r\n</html>', NULL),
(3, 8, 'Create a PHP script that connects to MySQL and displays data from a table.', '<?php\r\n$host = \"localhost\";\r\n$user = \"your_username\";\r\n$pass = \"your_password\";\r\n$db = \"your_database\";\r\n\r\n// Write your code here\r\n?>', '<?php\r\n$host = \"localhost\";\r\n$user = \"your_username\";\r\n$pass = \"your_password\";\r\n$db = \"your_database\";\r\n\r\n$conn = new mysqli($host, $user, $pass, $db);\r\n$result = $conn->query(\"SELECT * FROM users\");\r\nwhile($row = $result->fetch_assoc()) {\r\n    echo $row[\"name\"] . \"<br>\";\r\n}\r\n?>', NULL);

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
(12, 20, '', NULL, '672dadd0b4353_MIME.pdf', '2024-11-08 06:21:04', '', 'Introduction to Client_Server Side computing'),
(13, 18, '', NULL, '6730bca0359db_ASSIGNMENT 1.docx', '2024-11-10 14:01:04', '', 'introduction to html');

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
(20, 19, '2024-11-08 06:07:19', ''),
(20, 20, '2024-11-08 06:07:26', ''),
(25, 18, '2024-11-08 06:07:11', 'PHP');

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
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `order_number` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `title`, `description`, `order_number`, `status`, `created_at`) VALUES
(1, 'Introduction to HTML', 'Learn the fundamentals of HTML and web page structure', 1, 'active', '2024-11-10 11:01:21'),
(2, 'JavaScript Essentials', 'Master JavaScript programming and DOM manipulation', 2, 'active', '2024-11-10 11:01:21'),
(3, 'Backend Development', 'Learn PHP and MySQL for server-side development', 3, 'active', '2024-11-10 11:01:21');

-- --------------------------------------------------------

--
-- Table structure for table `module_content`
--

CREATE TABLE `module_content` (
  `id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `section_order` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `content_type` enum('lesson','exercise','quiz') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `module_content`
--

INSERT INTO `module_content` (`id`, `module_id`, `section_order`, `title`, `content`, `content_type`, `created_at`) VALUES
(1, 1, 1, 'Introduction to HTML', '<h2>What is HTML?</h2><p>HTML is the standard markup language for creating web pages...</p>', 'lesson', '2024-11-10 11:01:21'),
(2, 1, 2, 'Your First HTML Page', 'Create your first HTML page', 'exercise', '2024-11-10 11:01:21'),
(3, 1, 3, 'HTML Elements Quiz', 'Test your HTML knowledge', 'quiz', '2024-11-10 11:01:21'),
(4, 2, 1, 'JavaScript Basics', 'Introduction to JavaScript programming', 'lesson', '2024-11-10 11:01:21'),
(5, 2, 2, 'DOM Manipulation Exercise', 'Practice DOM manipulation', 'exercise', '2024-11-10 11:01:21'),
(6, 2, 3, 'JavaScript Quiz', 'Test your JavaScript knowledge', 'quiz', '2024-11-10 11:01:21'),
(7, 3, 1, 'PHP Basics', 'Introduction to PHP programming', 'lesson', '2024-11-10 11:01:21'),
(8, 3, 2, 'Database Operations', 'Practice MySQL operations', 'exercise', '2024-11-10 11:01:21'),
(9, 3, 3, 'PHP & MySQL Quiz', 'Test your backend development knowledge', 'quiz', '2024-11-10 11:01:21');

-- --------------------------------------------------------

--
-- Table structure for table `module_progress`
--

CREATE TABLE `module_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `completion_percentage` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `module_progress`
--

INSERT INTO `module_progress` (`id`, `user_id`, `module_id`, `status`, `completion_percentage`, `completed_at`) VALUES
(1, 25, 1, 'completed', 100, NULL),
(2, 25, 2, 'completed', 100, NULL),
(3, 25, 3, 'in_progress', 0, NULL),
(4, 25, 3, 'in_progress', 33, NULL),
(5, 25, 3, 'in_progress', 67, NULL),
(6, 25, 3, 'completed', 100, NULL),
(7, 26, 1, 'in_progress', 0, NULL),
(8, 20, 1, 'in_progress', 0, NULL),
(9, 20, 1, 'in_progress', 33, NULL),
(10, 20, 1, 'in_progress', 67, NULL),
(11, 20, 1, 'completed', 100, NULL),
(12, 20, 1, 'completed', 133, NULL),
(13, 20, 2, 'in_progress', 0, NULL),
(14, 28, 1, 'in_progress', 0, NULL),
(15, 28, 1, 'in_progress', 33, NULL),
(16, 28, 1, 'in_progress', 67, NULL),
(17, 28, 1, 'completed', 100, NULL),
(18, 28, 1, 'completed', 133, NULL),
(19, 28, 1, 'completed', 167, NULL),
(20, 28, 1, 'completed', 200, NULL);

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
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `correct_answer` varchar(255) NOT NULL,
  `explanation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`id`, `section_id`, `question`, `options`, `correct_answer`, `explanation`) VALUES
(1, 3, 'Which tag is used for the largest heading in HTML?', '[\"<h1>\", \"<h6>\", \"<heading>\", \"<head>\"]', '<h1>', 'The <h1> tag represents the main heading of a page.'),
(2, 6, 'Which method is used to add an element at the end of an array?', '[\"push()\", \"pop()\", \"shift()\", \"unshift()\"]', 'push()', 'The push() method adds elements to the end of an array.'),
(3, 9, 'Which PHP function is used to connect to MySQL database?', '[\"mysqli_connect()\", \"mysql_db()\", \"db_connect()\", \"connect_db()\"]', 'mysqli_connect()', 'mysqli_connect() is the modern way to connect to MySQL in PHP.');

-- --------------------------------------------------------

--
-- Table structure for table `student_assignments`
--

CREATE TABLE `student_assignments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `submission_text` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('pending','submitted','graded') DEFAULT 'pending',
  `score` int(11) DEFAULT 0,
  `feedback` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `graded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_assignments`
--

INSERT INTO `student_assignments` (`id`, `user_id`, `assignment_id`, `submission_text`, `file_path`, `status`, `score`, `feedback`, `submitted_at`, `graded_at`) VALUES
(1, 20, 1, '', NULL, 'submitted', 0, NULL, '2024-11-10 14:09:57', NULL),
(2, 28, 1, '', NULL, 'submitted', 0, NULL, '2024-11-10 14:37:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_progress`
--

CREATE TABLE `student_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `status` enum('not_started','in_progress','completed') DEFAULT 'not_started',
  `score` int(11) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_progress`
--

INSERT INTO `student_progress` (`id`, `user_id`, `section_id`, `status`, `score`, `completed_at`) VALUES
(1, 25, 1, 'completed', 100, NULL),
(2, 25, 2, 'completed', 100, NULL),
(3, 25, 3, 'completed', 100, NULL),
(4, 25, 4, 'completed', 100, NULL),
(5, 25, 5, 'completed', 100, NULL),
(6, 25, 6, 'completed', 100, NULL),
(7, 25, 7, 'completed', 100, NULL),
(8, 25, 8, 'completed', 100, NULL),
(9, 25, 9, 'completed', 100, NULL),
(10, 20, 1, 'completed', 100, NULL),
(11, 20, 2, 'completed', 100, NULL),
(12, 20, 3, 'completed', 100, NULL),
(13, 20, 3, 'completed', 100, NULL),
(14, 28, 1, 'completed', 100, NULL),
(15, 28, 2, 'completed', 100, NULL),
(16, 28, 3, 'completed', 100, NULL),
(17, 28, 3, 'completed', 100, NULL),
(18, 28, 3, 'completed', 0, NULL),
(19, 28, 3, 'completed', 0, NULL);

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
  `submission_status` varchar(20) DEFAULT 'pending',
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`, `role_id`, `submission_status`, `profile_image`, `bio`, `phone_number`, `date_of_birth`, `address`) VALUES
(20, 'Faith', 'faith@gmail.com', '$2y$10$k6msUNk9vjl5eHAOuNNyT.LI9L.dwIVCJJj1XQGoiBhRAhwQNLKfK', 'student', '2024-11-08 05:54:25', '2024-11-08 05:54:25', 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(21, 'Paul', 'paul@gmail.com', '$2y$10$PidxplaSe5yB5Lf/M4T2y.31neCQCNElYw6zvJz4ZJmO27GZ2q1MC', 'instructor', '2024-11-08 05:55:18', '2024-11-08 05:55:18', 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(24, 'Bonface', 'ondusobonface9@gmail.com', '$2y$10$ivxTW86AbriWjt8wtOd/5Oe8ClCXeTTqY1FoAujsPAvz6IvfLz7zO', 'admin', '2024-11-08 06:00:41', '2024-11-08 06:00:41', 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(25, 'Cally Sloan', 'qilolopek@mailinator.com', '$2y$10$XsgEQmpyS6NM9ifgg/8bpuJhZoig4XbOSlrWwDZCKDPH8TlHWQveG', 'student', '2024-11-10 08:45:18', '2024-11-10 13:41:24', 0, 'pending', NULL, 'Ullamco voluptates q', '+1 (924) 709-6757', '2005-06-06', 'Nulla aliquid molest'),
(26, 'Shadrack Onyango', 'shadrack.dev@gmail.com', '$2y$10$YAEqaVV9JK3Bp7viSaU8Gujv2XPzUzFNc/jqB4DcuUY4t1CLNKXRu', 'student', '2024-11-10 13:46:00', '2024-11-10 13:46:00', 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(28, 'James Okiya', 'okiya@gmail.com', '$2y$10$W3YhyPxuG2u40aiscFqejOXiDin.mu9XlZcu9frHPAN4/bEIjPq.W', 'student', '2024-11-10 14:28:52', '2024-11-10 14:28:52', 0, 'pending', NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `coding_exercises`
--
ALTER TABLE `coding_exercises`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

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
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `module_content`
--
ALTER TABLE `module_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `module_progress`
--
ALTER TABLE `module_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `module_id` (`module_id`);

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
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_submission` (`user_id`,`assignment_id`),
  ADD KEY `assignment_id` (`assignment_id`);

--
-- Indexes for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `section_id` (`section_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coding_exercises`
--
ALTER TABLE `coding_exercises`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

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
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `module_content`
--
ALTER TABLE `module_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `module_progress`
--
ALTER TABLE `module_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `student_assignments`
--
ALTER TABLE `student_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_progress`
--
ALTER TABLE `student_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

--
-- Constraints for table `coding_exercises`
--
ALTER TABLE `coding_exercises`
  ADD CONSTRAINT `coding_exercises_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `module_content` (`id`);

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
-- Constraints for table `module_content`
--
ALTER TABLE `module_content`
  ADD CONSTRAINT `module_content_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

--
-- Constraints for table `module_progress`
--
ALTER TABLE `module_progress`
  ADD CONSTRAINT `module_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `module_progress_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`);

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

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `module_content` (`id`);

--
-- Constraints for table `student_assignments`
--
ALTER TABLE `student_assignments`
  ADD CONSTRAINT `student_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_assignments_ibfk_2` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`);

--
-- Constraints for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD CONSTRAINT `student_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_progress_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `module_content` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
