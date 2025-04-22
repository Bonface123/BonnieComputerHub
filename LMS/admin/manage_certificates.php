<?php
// Admin management for certificates
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all certificates with user and course info
$certificates = $pdo->query('
    SELECT c.*, u.name AS student_name, cr.course_name
    FROM certificates c
    JOIN users u ON c.user_id = u.id
    JOIN courses cr ON c.course_id = cr.id
    ORDER BY c.issued_at DESC
')->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates - BCH Admin</title>
    <link rel="stylesheet" href="../../assets/css/bch-global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <a href="admin_dashboard.php" class="text-2xl font-bold text-secondary">Bonnie Computer Hub</a>
            <a href="admin_dashboard.php" class="text-white hover:text-secondary transition"><i class="fas fa-arrow-left mr-2"></i>Dashboard</a>
        </div>
    </header>
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-10 border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center justify-center bg-primary text-secondary rounded-full w-12 h-12"><i class="fas fa-certificate text-2xl"></i></span>
                    <div>
                        <h1 class="text-3xl font-bold text-primary leading-tight">Certificate Management</h1>
                        <p class="text-gray-500 text-sm mt-1">Issue, view, edit, or revoke certificates for all students and courses.</p>
                    </div>
                </div>
                <input id="searchBar" type="text" placeholder="Search by student, course, code..." class="ml-auto px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-secondary bg-white" style="max-width:250px;">
            </div>
            <div class="mb-6 p-4 rounded-lg bg-blue-50 border-l-4 border-blue-400 flex items-center gap-3">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                <div>
                    <span class="font-semibold text-blue-800">Tip:</span> Use the form below to manually issue a certificate. Use the table to manage existing certificates.
                </div>
            </div>
            <form action="generate_certificate.php" method="POST" class="mb-8 grid md:grid-cols-3 gap-4 items-end bg-gray-50 p-4 rounded-xl shadow-sm border border-gray-100">
                <div>
                    <label for="user_id" class="block text-gray-700 font-semibold mb-1">Select Student</label>
                    <select name="user_id" id="user_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent bg-white">
                        <option value="">Select a student...</option>
                        <?php
                        $students = $pdo->query("SELECT id, name, email FROM users WHERE role = 'student' ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($students as $student): ?>
                            <option value="<?= $student['id'] ?>">
                                <?= htmlspecialchars($student['name']) ?> (<?= htmlspecialchars($student['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="course_id" class="block text-gray-700 font-semibold mb-1">Select Course</label>
                    <select name="course_id" id="course_id" required class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent bg-white">
                        <option value="">Select a course...</option>
                        <?php
                        $courses = $pdo->query("SELECT id, course_name FROM courses ORDER BY course_name")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($courses as $course): ?>
                            <option value="<?= $course['id'] ?>">
                                <?= htmlspecialchars($course['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-secondary hover:text-primary focus:outline-none focus:ring-2 focus:ring-secondary transition duration-300 shadow" aria-label="Issue Certificate">
                        <i class="fas fa-certificate mr-2"></i>Issue Certificate
                    </button>
                </div>
            </form>
            <hr class="my-8 border-t-2 border-gray-100">
            <div id="feedbackMsg" class="mb-4"></div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm rounded-xl shadow-sm bg-white">
                    <thead class="bg-gradient-to-r from-primary to-secondary text-white">
                        <tr>
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">Student</th>
                            <th class="px-4 py-3 font-semibold">Course</th>
                            <th class="px-4 py-3 font-semibold">Grade</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Issued At</th>
                            <th class="px-4 py-3 font-semibold">Certificate Code</th>
                            <th class="px-4 py-3 font-semibold">Issued By</th>
                            <th class="px-4 py-3 font-semibold">PDF</th>
                            <th class="px-4 py-3 font-semibold">Verify</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="certTableBody">
        <?php foreach ($certificates as $cert): ?>
            <tr class="border-b hover:bg-gray-50 transition">
                <td class="px-4 py-2 font-mono text-xs text-gray-500"><?= $cert['id'] ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($cert['student_name']) ?></td>
                <td class="px-4 py-2"><?= htmlspecialchars($cert['course_name']) ?></td>
                <td class="px-4 py-2">
                    <span class="inline-block bg-blue-100 text-blue-800 px-2 rounded">
                        <?= $cert['grade'] !== null ? number_format($cert['grade'],2).'%' : 'N/A' ?>
                    </span>
                </td>
                <td class="px-4 py-2 capitalize">
                    <span class="inline-block px-2 py-1 rounded <?= $cert['status']==='issued' ? 'bg-green-100 text-green-700' : ($cert['status']==='revoked' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                        <?= htmlspecialchars($cert['status']) ?>
                    </span>
                </td>
                <td class="px-4 py-2 text-xs">
                    <?= htmlspecialchars($cert['issued_at']) ?>
                </td>
                <td class="px-4 py-2 font-mono text-xs text-gray-500">
                    <?= htmlspecialchars($cert['certificate_code']) ?>
                </td>
                <td class="px-4 py-2 text-xs">
                    <?= $cert['issued_by'] ? htmlspecialchars($cert['issued_by']) : 'Auto' ?>
                </td>
                <td class="px-4 py-2">
                        <?php if ($cert['pdf_path']): ?>
                            <a href="<?= $cert['pdf_path'] ?>" target="_blank" class="text-primary underline">PDF</a>
                            <br>
                            <a href="../pages/download_certificate.php?course_id=<?= $cert['course_id'] ?>" target="_blank" class="text-green-700 underline">Download Link</a>
                        <?php else: ?>
                            <span class="text-gray-400">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2">
                        <a href="../pages/verify_certificate.php?code=<?= urlencode($cert['certificate_code']) ?>" target="_blank" class="text-blue-600 underline">Verify</a>
                    </td>
                <td class="px-4 py-2 flex gap-2">
                    <button class="editBtn bg-secondary text-primary px-2 py-1 rounded text-xs font-semibold hover:bg-yellow-400" data-id="<?= $cert['id'] ?>" data-grade="<?= $cert['grade'] ?>" data-status="<?= $cert['status'] ?>">Edit</button>
                    <?php if ($cert['status'] === 'issued'): ?>
                        <button class="revokeBtn bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold hover:bg-red-200" data-id="<?= $cert['id'] ?>">Revoke</button>
                    <?php endif; ?>
                    <button class="deleteBtn bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-semibold hover:bg-gray-300" data-id="<?= $cert['id'] ?>">Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Edit Certificate</h2>
        <form id="editForm">
            <input type="hidden" name="id" id="editId">
            <div class="mb-4">
                <label class="block font-semibold mb-1">Grade (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="grade" id="editGrade" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1">Status</label>
                <select name="status" id="editStatus" class="w-full border rounded px-3 py-2">
                    <option value="issued">Issued</option>
                    <option value="revoked">Revoked</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="flex gap-2 justify-end">
                <button type="button" id="closeModal" class="bg-gray-200 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
<script>
// Search/Filter
const searchBar = document.getElementById('searchBar');
const certTableBody = document.getElementById('certTableBody');
searchBar.addEventListener('input', function() {
    const val = this.value.toLowerCase();
    Array.from(certTableBody.children).forEach(row => {
        row.style.display = Array.from(row.children).some(td => td.textContent.toLowerCase().includes(val)) ? '' : 'none';
    });
});
// Feedback
function showMsg(msg, success=true) {
    const el = document.getElementById('feedbackMsg');
    el.textContent = msg;
    el.className = success ? 'mb-4 bg-green-100 text-green-800 px-4 py-2 rounded' : 'mb-4 bg-red-100 text-red-800 px-4 py-2 rounded';
    setTimeout(()=>{el.textContent='';el.className='mb-4';}, 4000);
}
// CRUD AJAX
certTableBody.addEventListener('click', function(e) {
    if (e.target.classList.contains('revokeBtn')) {
        const id = e.target.dataset.id;
        if (confirm('Revoke this certificate?')) {
            fetch('certificate_crud.php', {method:'POST',body:new URLSearchParams({action:'revoke',id})})
            .then(r=>r.json()).then(res=>{if(res.success){showMsg('Certificate revoked.');location.reload();}else{showMsg(res.message,false);}});
        }
    }
    if (e.target.classList.contains('deleteBtn')) {
        const id = e.target.dataset.id;
        if (confirm('Delete this certificate? This cannot be undone.')) {
            fetch('certificate_crud.php', {method:'POST',body:new URLSearchParams({action:'delete',id})})
            .then(r=>r.json()).then(res=>{if(res.success){showMsg('Certificate deleted.');location.reload();}else{showMsg(res.message,false);}});
        }
    }
    if (e.target.classList.contains('editBtn')) {
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editId').value = e.target.dataset.id;
        document.getElementById('editGrade').value = e.target.dataset.grade;
        document.getElementById('editStatus').value = e.target.dataset.status;
    }
});
document.getElementById('closeModal').onclick = ()=>document.getElementById('editModal').classList.add('hidden');
document.getElementById('editForm').onsubmit = function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    fd.append('action','edit');
    fetch('certificate_crud.php', {method:'POST',body:fd})
    .then(r=>r.json()).then(res=>{if(res.success){showMsg('Certificate updated.');location.reload();}else{showMsg(res.message,false);}});
};
</script>
    </main>
    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6 text-center">
            &copy; <?= date('Y') ?> Bonnie Computer Hub. All rights reserved.
        </div>
    </footer>
</body>
</html>
