document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    // Implement login logic here
    window.location.href = 'dashboard.html';
  });
  
  document.getElementById('signup-form').addEventListener('submit', function(e) {
    e.preventDefault();
    // Implement signup logic here
    window.location.href = 'dashboard.html';
  });
  document.getElementById('logout').addEventListener('click', function() {
    // Implement logout logic here
    window.location.href = 'login.html';
  });
  
  // Populate bookings list
  const bookingsList = document.getElementById('bookings-list');
  // Fetch and display user's bookings
    