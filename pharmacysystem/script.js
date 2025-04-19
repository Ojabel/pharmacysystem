document.addEventListener('DOMContentLoaded', function() {
  const toggleBtn = document.getElementById('toggleSidebar');
  const sidebar  = document.getElementById('adminSidebar');
  
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('active');
    });
  }
});
