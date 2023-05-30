//doesn't do anything right now
$(document).ready(function() {
  $('#fill-now-btn').click(function() {
    window.location.href = '<?php echo admin_url('user-edit.php'); ?>';
  });
});