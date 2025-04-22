# BonnieComputerHub – Full System Documentation

## Table of Contents
1. [Project Overview](#project-overview)
2. [Project Structure](#project-structure)
3. [Key Modules & Features](#key-modules--features)
4. [Design System & Accessibility](#design-system--accessibility)
5. [Database Schema](#database-schema)
6. [Setup & Deployment](#setup--deployment)
7. [Example User Flows](#example-user-flows)
8. [Security & Best Practices](#security--best-practices)
9. [Contribution & Further Development](#contribution--further-development)
10. [Contact](#contact)

---

## Project Overview
BonnieComputerHub is a multi-module web application providing educational, technical, and service-oriented solutions. It features a Learning Management System (LMS) for online courses, specialized sections for cybersecurity, laptops, and software services, and a unified design system for a consistent, accessible, and responsive user experience.

---

## Project Structure
- **index.html**: Main landing page and navigation portal.
- **BCH_CYBER/**: Cybersecurity services (info, requests, contact).
- **BCH_LAPTOPS/**: Laptop sales/support, product listings, contact.
- **BCH_SOFTWARE/**: Software solutions, requests, contact.
- **LMS/**: Learning Management System with:
  - `admin/`: Admin portal (user/course management, reporting)
  - `instructor/`: Instructor portal (course/module/content/assignment management, student progress, notifications)
  - `student/`: Student portal (enrollment, content access, assignment submission, progress)
  - `includes/`: Shared PHP includes (DB connection, header, footer, auth)
  - `css/`, `images/`, `uploads/`: Styles, images, and file uploads
- **assets/**: Shared CSS (design-system, components, utilities), images
- **bch-lms.sql**: Database schema and seed data

---

## Key Modules & Features
### Main Website
- Landing page with navigation to all services and LMS
- Contact and about sections

### Specialized Sections
- **BCH_CYBER, BCH_LAPTOPS, BCH_SOFTWARE**: Each with dedicated info, service request/contact forms, and styling

### LMS
- **Admin Portal**: User/course management, reporting
- **Instructor Portal**: Course/module/content/assignment CRUD, student progress, notifications
- **Student Portal**: Course enrollment, content access, assignment submission, progress tracking
- **Course Catalog**: Filtering, curriculum outlines, pricing, schedules, skill/certification badges
- **Notifications**: Email and in-app for assignments and updates
- **Resource Management**: File uploads for course materials/assignments

---

## Design System & Accessibility
- **Colors**: Primary (#1E40AF), Secondary (#FFD700), Accent (#fd7e14, #dc3545, etc.)
- **Typography**: Inter font, consistent sizing and headings
- **Components**: Reusable cards, buttons, forms, navigation
- **Accessibility**: Skip links, ARIA attributes, keyboard navigation, visible focus outlines
- **Responsive**: Mobile-first layouts, Tailwind CSS utilities
- **Animations**: Subtle transitions for enhanced UX

---

## Database Schema
- **Tables**: users, courses, course_modules, module_content, assignments, enrollments, submissions, notifications, etc.
- **Relationships**: Foreign keys enforce data integrity (e.g., enrollments reference courses)
- **Constraints**: ON DELETE CASCADE for most, but some (like enrollments) require manual deletion before parent deletion
- **Note**: When deleting a course, related enrollments, modules, module content, and assignments must be deleted first to satisfy foreign key constraints (see `delete_course.php` logic)

---

## Setup & Deployment
1. Install XAMPP, PHP 7+, MySQL
2. Clone/copy project into `htdocs`
3. Import `bch-lms.sql` into MySQL
4. Update database credentials in `/LMS/includes/db_connect.php`
5. Start Apache/MySQL, access via `http://localhost/BonnieComputerHub/`
6. Use default admin/instructor/student accounts or register new ones

---

## Example User Flows
- **Admin**: Log in → Manage users/courses → View reports
- **Instructor**: Log in → Manage courses/modules/content → Grade assignments → Track student progress
- **Student**: Log in → Enroll in course → Access content → Submit assignments → View progress

---

## Security & Best Practices
- All portals require login (session-based authentication)
- Role checks for all sensitive operations
- Prepared SQL statements and input validation
- File upload validation for thumbnails/resources
- Data integrity via foreign keys, cascading deletes, and manual cleanup
- Accessibility and responsive design enforced throughout

---

## Contribution & Further Development
- Follow the BCH design system for all new features
- Document new features and code changes
- Use prepared statements and validate all user input
- Test for accessibility and responsiveness
- Submit issues/feature requests via the project repository

---

## Contact
- **Bonnie Computer Hub**
- Nairobi, Kenya
- +254 729 820 689
- Bonniecomputerhub24@gmail.com
