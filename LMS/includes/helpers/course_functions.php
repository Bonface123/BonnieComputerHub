<?php
// Shared course functions for Bonnie Computer Hub LMS
require_once __DIR__ . '/../db_connect.php';

/**
 * Fetch courses with optional filters and user view
 * @param PDO $pdo
 * @param array $filters
 * @param string $view (visitor|instructor|admin)
 * @param int|null $user_id
 * @return array
 */
function get_courses($pdo, $filters = [], $view = 'visitor', $user_id = null) {
    $sql = "SELECT c.*, u.name as instructor_name, c.mode, c.next_intake_date FROM courses c LEFT JOIN users u ON c.instructor_id = u.id WHERE 1=1";
    $params = [];
    // Filter by status for visitors
    if ($view === 'visitor') {
        $sql .= " AND c.status = 'active' AND (c.approved_by IS NOT NULL)";
    } elseif ($view === 'instructor') {
        $sql .= " AND c.instructor_id = ?";
        $params[] = $user_id;
    }
    // Additional filters (e.g., skill_level, search)
    if (!empty($filters['skill_level'])) {
        $sql .= " AND c.skill_level = ?";
        $params[] = $filters['skill_level'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (c.name LIKE ? OR c.description LIKE ?)";
        $params[] = "%" . $filters['search'] . "%";
        $params[] = "%" . $filters['search'] . "%";
    }
    $sql .= " ORDER BY c.next_intake_date DESC, c.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Fetch modules for each course
    foreach ($courses as &$course) {
        $course['modules'] = get_course_modules($pdo, $course['id']);
    }
    return $courses;
}

/**
 * Fetch modules for a course
 */
function get_course_modules($pdo, $course_id) {
    $stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY position ASC");
    $stmt->execute([$course_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Render course status badge
 */
function render_course_status_badge($status) {
    $colors = [
        'active' => 'bg-green-100 text-green-800',
        'draft' => 'bg-yellow-100 text-yellow-800',
        'pending' => 'bg-blue-100 text-blue-800',
        'archived' => 'bg-gray-200 text-gray-600',
    ];
    $label = ucfirst($status);
    $color = $colors[$status] ?? 'bg-gray-100 text-gray-600';
    return "<span class='inline-block px-3 py-1 rounded-full text-xs font-semibold $color'>$label</span>";
}
