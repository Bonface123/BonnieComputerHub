# Bonnie Computer Hub LMS – Instructor Module Documentation

## Table of Contents
1. [Overview](#overview)
2. [Folder Structure](#folder-structure)
3. [Key Features & Workflows](#key-features--workflows)
4. [Setup & Usage](#setup--usage)
5. [Design System & Accessibility](#design-system--accessibility)
6. [Security Practices](#security-practices)
7. [Example Workflow](#example-workflow)
8. [Contribution & Further Development](#contribution--further-development)
9. [Contact](#contact)

---

## Overview
Bonnie Computer Hub's Learning Management System (LMS) Instructor Module empowers instructors to manage courses, modules, content, assignments, student progress, and notifications. The UI is fully responsive, accessible, and adheres to the BCH design system for consistency and usability.

---

## Folder Structure
**Files in `LMS/instructor/`:**
- **Course Management:**
  - `manage_courses.php`, `edit_course.php`, `create_course.php`, `delete_course.php`
- **Module Management:**
  - `edit_module.php`, `delete_module.php`, `view_module.php`
- **Content Management:**
  - `edit_content.php`, `delete_content.php`, `view_content.php`
- **Assignments:**
  - `add_assignment.php`, `edit_assignment.php`, `delete_assignment.php`, `manage_assignments.php`, `view_assignment.php`, `view_assignments.php`, `view_submissions.php`, `grade_submissions.php`, `update_grade.php`
- **Progress Tracking:**
  - `student_progress.php`, `track_progress.php`, `view_progress.php`, `view_report.php`
- **Notifications:**
  - `send_notification.php`
- **Resource Management:**
  - `manage_resources.php`
- **Dashboard:**
  - `instructor_dashboard.php`

---

## Key Features & Workflows
- **Course CRUD:** Instructors can create, edit, and delete any course.
- **Module & Content CRUD:** Instructors can add, edit, and delete modules and content for any course.
- **Assignment Management:** Instructors can assign, edit, grade, and delete assignments.
- **Student Progress:** Instructors can view and track student progress, view reports, and manage grades.
- **Notifications:** Instructors can send notifications to students.
- **Resource Sharing:** Instructors can upload and manage course resources.
- **Design System:** All pages use a unified BCH design system for colors, typography, and layout.
- **Accessibility:** ARIA labels, keyboard navigation, and visible focus states are enforced.
- **Security:** Session-based authentication, prepared SQL statements, and role checks.

---

## Setup & Usage
- **Requirements:** PHP 7+, MySQL, XAMPP (or similar stack), Tailwind CSS, Font Awesome.
- **Installation:**
  1. Clone or copy the project to your XAMPP `htdocs` directory.
  2. Import the provided SQL schema (`bch-lms.sql`) into MySQL.
  3. Update database credentials in `includes/db_connect.php`.
  4. Start XAMPP and access the LMS via `http://localhost/BonnieComputerHub/LMS/instructor/manage_courses.php`.
- **User Roles:**
  - Admin, Instructor, Student (this folder is for instructors).
  - Instructors can manage all courses, modules, and content.

---

## Design System & Accessibility
- **Colors:**
  - Primary: #1E40AF (BCH Blue)
  - Secondary: #FFD700 (BCH Gold)
  - Accent: #fd7e14, #dc3545, etc.
- **Typography:** Inter font, consistent sizing, and headings.
- **Components:** Reusable classes for cards, buttons, forms, navigation.
- **Accessibility:**
  - Skip links, ARIA attributes, keyboard navigation, visible focus outlines.
- **Responsive:**
  - Mobile-first layouts, flex/grid, responsive spacing.

---

## Security Practices
- **Session-based authentication for all pages.**
- **Role checks (instructor only).**
- **Prepared statements for all database queries.**
- **File upload validation for thumbnails and resources.**

---

## Example Workflow
- **Add a Course:**
  Instructor clicks "Add Course", fills out details, and submits. Course appears in the course list.
- **Edit Course:**
  Instructor clicks "Edit" (dedicated page), updates details, and saves.
- **Delete Course/Module/Content:**
  Instructor clicks "Delete" on any item; confirmation prompt appears; item is deleted.
- **Add Module/Content:**
  Instructor clicks "Add Module" or "Add Content", fills out the form, and submits.

---

## Contribution & Further Development
- **Follow the BCH design system for all new features.**
- **Ensure all new code is accessible and responsive.**
- **Document new features in this guide.**
- **Report issues or suggest improvements via the project repository.**

---

## Contact
- **Bonnie Computer Hub**
- Nairobi, Kenya
- +254 729 820 689
- Bonniecomputerhub24@gmail.com
