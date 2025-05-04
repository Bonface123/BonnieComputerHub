// BCH Apply Modal Logic
// Provides robust, accessible, and design-system-compliant modal functionality for course applications

// Make openApplyModal available globally IMMEDIATELY
window.openApplyModal = function(courseId) {
    const modal = document.getElementById('bch-apply-modal');
    if (!modal) {
        alert('Application modal is not available. Please try again later.');
        return;
    }
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    document.getElementById('bch-modal-course-id').value = courseId || '';
    document.getElementById('bch-apply-form').reset();
    document.getElementById('bch-apply-feedback').innerHTML = '';
    const courseNameInput = document.getElementById('bch-modal-course-name');
    if (courseId) {
        let courseName = '';
        if (window.event && window.event.target && window.event.target.dataset && window.event.target.dataset.courseName) {
            courseName = window.event.target.dataset.courseName;
        }
        if (courseName && courseName.length > 0 && courseName !== 'undefined') {
            courseNameInput.value = courseName;
            courseNameInput.classList.remove('border-red-500');
        } else {
            fetch('/bonniecomputerhub/LMS/pages/enroll_apply.php?get_course_name=1&course_id=' + encodeURIComponent(courseId))
                .then(res => res.json())
                .then(data => {
                    if (data && data.course_name) {
                        courseNameInput.value = data.course_name;
                        courseNameInput.classList.remove('border-red-500');
                    } else {
                        courseNameInput.value = 'Course not found';
                        courseNameInput.classList.add('border-red-500');
                    }
                })
                .catch(() => {
                    courseNameInput.value = 'Error fetching course name';
                    courseNameInput.classList.add('border-red-500');
                });
        }
    } else {
        courseNameInput.value = 'General Application';
        courseNameInput.classList.remove('border-red-500');
    }
    setTimeout(() => {
        document.getElementById('bch-modal-name').focus();
    }, 100);
    if (typeof trapFocus === 'function') {
        trapFocus(modal);
    }
};

document.addEventListener('DOMContentLoaded', function () {
    // 1. Dynamic Modal Creation
    if (!document.getElementById('bch-apply-modal')) {
        const modal = document.createElement('div');
        modal.id = 'bch-apply-modal';
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden';
        modal.innerHTML = `
            <div class="bch-modal-card bg-white rounded-2xl shadow-2xl max-w-lg w-full p-0 relative animate-fadeIn font-inter">
                <div class="bch-modal-header flex items-center gap-3 px-8 py-5 rounded-t-2xl" style="background: linear-gradient(90deg, #002147 80%, #E6B800 100%);">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-primary text-2xl">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-white tracking-tight mb-0 flex-1">Apply for Course</h2>
                    <button id="bch-close-modal" class="ml-auto text-white hover:text-yellow-400 text-3xl focus:outline-none focus:ring-2 focus:ring-yellow-600" aria-label="Close">&times;</button>
                </div>
                <form id="bch-apply-form" class="space-y-4 px-8 py-6" novalidate autocomplete="off">
                    <input type="hidden" name="course_id" id="bch-modal-course-id" />
                    <div class="mb-3">
                        <label class="block font-semibold mb-1 text-primary" for="bch-modal-course-name">Course Name</label>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-book-open text-blue-600 text-lg"></i>
                            <input type="text" id="bch-modal-course-name" name="course_name" class="w-full border border-blue-200 rounded px-3 py-2 bg-gray-100 text-blue-700 font-semibold focus:border-yellow-600 focus:ring-2 focus:ring-yellow-300 transition" readonly tabindex="-1" aria-readonly="true" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-primary" for="bch-modal-name">Full Name</label>
                        <input type="text" id="bch-modal-name" name="name" class="w-full border rounded px-3 py-2" required />
                        <span id="bch-modal-name-error" class="text-xs text-red-600 mt-1 block"></span>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-primary" for="bch-modal-email">Email</label>
                        <input type="email" id="bch-modal-email" name="email" class="w-full border rounded px-3 py-2" required />
                        <span id="bch-modal-email-error" class="text-xs text-red-600 mt-1 block"></span>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-primary" for="bch-modal-phone">Phone</label>
                        <input type="text" id="bch-modal-phone" name="phone" class="w-full border rounded px-3 py-2" />
                        <span id="bch-modal-phone-error" class="text-xs text-red-600 mt-1 block"></span>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1 text-primary" for="bch-modal-message">Message (optional)</label>
                        <textarea id="bch-modal-message" name="message" class="w-full border rounded px-3 py-2"></textarea>
                    </div>
                    <button type="submit" id="bch-modal-submit" class="bg-[#002147] text-white px-6 py-3 rounded font-bold hover:bg-blue-800 transition w-full flex items-center justify-center gap-2 focus:outline-none focus:ring-4 focus:ring-blue-300" aria-live="polite">
                        <span id="bch-modal-submit-text">Apply</span>
                        <svg id="bch-modal-spinner" class="hidden animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    </button>
                </form>
                <div id="bch-apply-feedback" class="mt-4 text-center text-sm"></div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    // 2. Modal Open Function (global)
    window.openApplyModal = function(courseId) {
        const modal = document.getElementById('bch-apply-modal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        document.getElementById('bch-modal-course-id').value = courseId || '';
        document.getElementById('bch-apply-form').reset();
        document.getElementById('bch-apply-feedback').innerHTML = '';
        const courseNameInput = document.getElementById('bch-modal-course-name');
        if (courseId) {
            let courseName = '';
            if (window.event && window.event.target && window.event.target.dataset && window.event.target.dataset.courseName) {
                courseName = window.event.target.dataset.courseName;
            }
            if (courseName && courseName.length > 0 && courseName !== 'undefined') {
                courseNameInput.value = courseName;
                courseNameInput.classList.remove('border-red-500');
            } else {
                fetch('/bonniecomputerhub/LMS/pages/enroll_apply.php?get_course_name=1&course_id=' + encodeURIComponent(courseId))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.course_name) {
                            courseNameInput.value = data.course_name;
                            courseNameInput.classList.remove('border-red-500');
                        } else {
                            courseNameInput.value = 'Course not found';
                            courseNameInput.classList.add('border-red-500');
                        }
                    })
                    .catch(() => {
                        courseNameInput.value = 'Error fetching course name';
                        courseNameInput.classList.add('border-red-500');
                    });
            }
        } else {
            courseNameInput.value = 'General Application';
            courseNameInput.classList.remove('border-red-500');
        }
        setTimeout(() => {
            document.getElementById('bch-modal-name').focus();
        }, 100);
        trapFocus(modal);
    };

    // 3. Accessibility: Focus trap and ESC close
    function trapFocus(modal) {
        const focusable = modal.querySelectorAll('button, [href], input, textarea, select, [tabindex]:not([tabindex="-1"])');
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    }
                } else {
                    if (document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            }
        });
    }

    // 4. Close modal on background or X click
    document.body.addEventListener('click', function(e) {
        if (e.target.id === 'bch-close-modal' || e.target.id === 'bch-apply-modal') {
            document.getElementById('bch-apply-modal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            document.getElementById('bch-apply-feedback').innerHTML = '';
            const submitBtn = document.getElementById('bch-modal-submit');
            const submitText = document.getElementById('bch-modal-submit-text');
            const spinner = document.getElementById('bch-modal-spinner');
            submitBtn.disabled = false;
            spinner.classList.add('hidden');
            submitText.textContent = 'Apply';
        }
    });

    // 5. Validation (live and on submit)
    const nameInput = document.getElementById('bch-modal-name');
    const emailInput = document.getElementById('bch-modal-email');
    const phoneInput = document.getElementById('bch-modal-phone');
    nameInput.addEventListener('blur', function() {
        if (nameInput.value.trim().length < 3) {
            nameInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-name-error').textContent = 'Please enter your full name (min 3 characters).';
        } else {
            nameInput.classList.remove('bch-input-error');
            document.getElementById('bch-modal-name-error').textContent = '';
        }
    });
    emailInput.addEventListener('blur', function() {
        const re = /^\S+@\S+\.\S+$/;
        if (!re.test(emailInput.value.trim())) {
            emailInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-email-error').textContent = 'Please enter a valid email address.';
        } else {
            emailInput.classList.remove('bch-input-error');
            document.getElementById('bch-modal-email-error').textContent = '';
        }
    });
    phoneInput.addEventListener('blur', function() {
        if (phoneInput.value && phoneInput.value.replace(/\D/g,'').length < 8) {
            phoneInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-phone-error').textContent = 'Phone number must be at least 8 digits.';
        } else {
            phoneInput.classList.remove('bch-input-error');
            document.getElementById('bch-modal-phone-error').textContent = '';
        }
    });

    // 6. AJAX Submission
    document.getElementById('bch-apply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        let valid = true;
        if (nameInput.value.trim().length < 3) {
            nameInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-name-error').textContent = 'Please enter your full name (min 3 characters).';
            valid = false;
        }
        const re = /^\S+@\S+\.\S+$/;
        if (!re.test(emailInput.value.trim())) {
            emailInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-email-error').textContent = 'Please enter a valid email address.';
            valid = false;
        }
        if (phoneInput.value && phoneInput.value.replace(/\D/g,'').length < 8) {
            phoneInput.classList.add('bch-input-error');
            document.getElementById('bch-modal-phone-error').textContent = 'Phone number must be at least 8 digits.';
            valid = false;
        }
        if (!valid) {
            setTimeout(() => {
                nameInput.classList.remove('bch-input-error');
                emailInput.classList.remove('bch-input-error');
                phoneInput.classList.remove('bch-input-error');
            }, 700);
            return;
        }
        // Prevent submission if courseId is present but course name is missing or error
        const courseId = document.getElementById('bch-modal-course-id').value;
        const courseNameInput = document.getElementById('bch-modal-course-name');
        if (courseId && (!courseNameInput.value || courseNameInput.value === 'Course not found' || courseNameInput.value === 'Error fetching course name')) {
            const feedback = document.getElementById('bch-apply-feedback');
            feedback.innerHTML = '<div class="bch-bg-red-50 bch-border-l-4 bch-border-red-500 bch-text-red-700 bch-p-4 bch-mb-2 bch-rounded animate-fadeIn"><div class="flex items-center justify-center"><i class="fas fa-times-circle mr-2 text-red-600"></i> Unable to fetch course name. Please try again or refresh the page.</div></div>';
            courseNameInput.classList.add('border-red-500');
            return;
        }
        // Loading state
        const submitBtn = document.getElementById('bch-modal-submit');
        const submitText = document.getElementById('bch-modal-submit-text');
        const spinner = document.getElementById('bch-modal-spinner');
        const feedback = document.getElementById('bch-apply-feedback');
        submitBtn.disabled = true;
        spinner.classList.remove('hidden');
        submitText.textContent = 'Submitting...';
        feedback.innerHTML = '';
        // Ensure name is included correctly in FormData
        const formData = new FormData(this);
        formData.set('name', nameInput.value.trim());
        fetch('/bonniecomputerhub/LMS/pages/enroll_apply.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(resp => {
            spinner.classList.add('hidden');
            submitBtn.disabled = false;
            submitText.textContent = 'Apply';
            if (resp.success) {
                // Confetti celebration if available
                if (typeof confetti === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js';
                    script.onload = function() { confetti(); };
                    document.body.appendChild(script);
                } else {
                    confetti();
                }
                // Vibrant, animated feedback
                feedback.innerHTML = `<div class='bch-bg-green-50 bch-border-l-4 bch-border-green-500 bch-text-green-700 bch-p-4 bch-mb-2 bch-rounded animate-fadeIn bch-shadow-lg' style='animation: bounce-in 0.7s;'>
                  <div class='flex items-center justify-center gap-2'>
                    <svg class='w-8 h-8 animate-bounce text-green-500' fill='none' stroke='currentColor' stroke-width='2' viewBox='0 0 24 24'><circle cx='12' cy='12' r='10' stroke='currentColor' stroke-width='4' class='opacity-25'></circle><path class='opacity-75' fill='currentColor' d='M9 12l2 2l4-4'></path></svg>
                    <span class='font-bold text-lg'>${resp.message || 'Application submitted successfully!'}</span>
                  </div>
                </div>`;
                // Optionally show persistent message on main page
                setTimeout(() => {
                  document.getElementById('bch-apply-modal').classList.add('hidden');
                  document.body.classList.remove('overflow-hidden');
                  feedback.innerHTML = '';
                  // Persistent message
                  let mainFeedback = document.getElementById('bch-main-feedback');
                  if (!mainFeedback) {
                    mainFeedback = document.createElement('div');
                    mainFeedback.id = 'bch-main-feedback';
                    mainFeedback.className = 'fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[9999] bch-bg-green-50 bch-border-l-4 bch-border-green-500 bch-text-green-700 bch-p-4 bch-rounded-xl bch-shadow-xl animate-fadeIn';
                    document.body.appendChild(mainFeedback);
                  }
                  mainFeedback.innerHTML = `<div class='flex items-center gap-2'><i class='fas fa-check-circle text-2xl text-green-500 animate-bounce'></i> <span class='font-semibold'>${resp.message || 'Application submitted successfully!'}</span></div>`;
                  setTimeout(() => {
                    mainFeedback.remove();
                  }, 5000);
                }, 2500);
            } else {
                feedback.innerHTML = `<div class='bch-bg-red-50 bch-border-l-4 bch-border-red-500 bch-text-red-700 bch-p-4 bch-mb-2 bch-rounded animate-fadeIn'><div class='flex items-center justify-center'><i class='fas fa-exclamation-circle mr-2 text-red-500'></i> ${resp.message || 'Submission failed. Try again.'}</div></div>`;
            }
        })
        .catch(() => {
            spinner.classList.add('hidden');
            submitBtn.disabled = false;
            submitText.textContent = 'Apply';
            feedback.innerHTML = `<div class='bch-bg-red-50 bch-border-l-4 bch-border-red-500 bch-text-red-700 bch-p-4 bch-mb-2 bch-rounded animate-fadeIn'><div class='flex items-center justify-center'><i class='fas fa-exclamation-circle mr-2 text-red-500'></i> Submission failed. Try again.</div></div>`;
        });
    });
});
