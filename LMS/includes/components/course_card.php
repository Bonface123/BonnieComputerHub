<?php
// Reusable course card component for Bonnie Computer Hub LMS
require_once __DIR__ . '/../helpers/course_functions.php';
/**
 * Render a course card
 * @param array $course
 * @param string $view (visitor|instructor|admin)
 * @return string
 */
function render_course_card($course, $view = 'visitor') {
    // Design system & accessibility classes
    $card = "<div class='bch-card bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col h-full'>";
    $card .= "<div class='p-6 flex-1 flex flex-col'>";
    $card .= "<h3 class='text-2xl font-bold mb-2 text-bch-blue' aria-label='Course Title'>{$course['course_name']}</h3>";
    $card .= "<div class='flex items-center gap-2 mb-2'>";
    $card .= render_course_status_badge($course['status']);
    if (!empty($course['certification'])) {
        $card .= "<span class='ml-2 inline-block px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-semibold' aria-label='Certification'>{$course['certification']}</span>";
    }
    $card .= "</div>";
    $card .= "<p class='text-gray-700 mb-3'>{$course['description']}</p>";
    $card .= "<div class='mb-4'>";
    $card .= "<span class='font-semibold text-gray-600'>Skill Level:</span> <span class='ml-1 text-sm'>{$course['skill_level']}</span>";
    $card .= "</div>";
    $card .= "<div class='flex flex-wrap gap-2 mb-4'>";
    if (!empty($course['modules'])) {
        foreach ($course['modules'] as $module) {
            $card .= "<span class='bg-bch-blue-light text-bch-blue px-2 py-1 rounded text-xs' aria-label='Module'>{$module['module_name']}</span>";
        }
    }
    $card .= "</div>";
    $card .= "<div class='flex items-center gap-4 mb-4'>";
    $card .= "<span class='font-semibold text-lg text-bch-blue'>KES {$course['price']}</span>";
    if (!empty($course['discount_price'])) {
        $card .= "<span class='line-through text-gray-400'>KES {$course['discount_price']}</span>";
    }
    $card .= "</div>";
    $intakeDisplay = ($course['mode'] === 'self-paced' || empty($course['next_intake_date'])) ? 'Self-paced' : date('M j, Y', strtotime($course['next_intake_date']));
    $card .= "<div class='mb-2 text-sm text-gray-500'>Next Intake: <span class='font-semibold'>{$intakeDisplay}</span></div>";
    $card .= "<div class='mb-2 text-sm text-gray-500'>Instructor: <span class='font-semibold'>{$course['instructor_name']}</span></div>";
    // CTA buttons based on view
    $card .= "<div class='mt-auto pt-4 flex gap-3'>";
    // NOTE: To enable the application modal, make sure to include ../assets/js/apply-modal.js on any page using this component.
    if ($view === 'visitor') {
        $card .= "<a href='register.php?course_id={$course['id']}' class='bch-btn bch-btn-primary bch-btn-sm' aria-label='Enroll'>Enroll</a>";
    } elseif ($view === 'instructor') {
        $card .= "<a href='../instructor/edit_course.php?id={$course['id']}' class='bch-btn bch-btn-secondary bch-btn-sm' aria-label='Edit'>Edit</a>";
    } elseif ($view === 'admin') {
        $card .= "<a href='../admin/edit_course.php?id={$course['id']}' class='bch-btn bch-btn-secondary bch-btn-sm' aria-label='Edit'>Edit</a>";
        if ($course['status'] === 'pending') {
            $card .= "<a href='../admin/approve_course.php?id={$course['id']}' class='bch-btn bch-btn-primary bch-btn-sm' aria-label='Approve'>Approve</a>";
        }
    }
    $card .= "</div>";
    $card .= "</div></div>";
    return $card;
}
