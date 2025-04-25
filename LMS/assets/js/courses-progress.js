// BCH LMS - Custom Progress Bar Animation & Feedback Modal
$(function() {
  // Animate progress bars
  $('.progress-bar').each(function() {
    var $bar = $(this);
    var percent = $bar.data('percent');
    $bar.css('width', percent + '%');
    if (percent === 100) $bar.attr('data-complete', '100');
    $bar.addClass('progress-bar-anim');
  });

  // Feedback Modal
  $('.open-feedback-modal').on('click', function(e) {
    e.preventDefault();
    var courseId = $(this).data('course');
    $('#feedback-modal').addClass('active');
    $('#feedback-modal [name="course_id"]').val(courseId);
  });
  $('.close-btn, #feedback-modal .modal-bg').on('click', function() {
    $('#feedback-modal').removeClass('active');
  });
  $('#feedback-modal form').on('submit', function() {
    $('#feedback-modal').removeClass('active');
  });
  // Trap focus in modal
  $('#feedback-modal').on('keydown', function(e) {
    if (e.key === 'Tab') {
      var focusable = $('#feedback-modal').find('button, [href], input, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
      var first = focusable.first()[0];
      var last = focusable.last()[0];
      if (e.shiftKey ? document.activeElement === first : document.activeElement === last) {
        e.preventDefault();
        (e.shiftKey ? last : first).focus();
      }
    }
    if (e.key === 'Escape') $('#feedback-modal').removeClass('active');
  });
});
