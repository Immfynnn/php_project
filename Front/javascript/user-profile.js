document.addEventListener('DOMContentLoaded', function() {
    const profileLink = document.getElementById('profile-link'); // Profile icon
    const profileOverlay = document.getElementById('MyProfile'); // Overlay container
    const closeProfileButton = document.getElementById('close-profile'); // Close button
    const dialog = document.querySelector('.dialog1'); // Dialog container

    // Show the overlay and dialog when the profile icon is clicked
    profileLink.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default link behavior
        profileOverlay.style.display = 'flex'; // Show the overlay
        dialog.style.display = 'block'; // Show the dialog
        // Trigger reflow to enable animation
        void dialog.offsetWidth; // Forces reflow to restart animation
        dialog.classList.remove('fade-out'); // Ensure fade-out is removed
        dialog.classList.add('fade-in'); // Add fade-in class
    });

    // Close the overlay when the close button is clicked
    closeProfileButton.addEventListener('click', function() {
        dialog.classList.add('fade-out'); // Add fade-out class
        setTimeout(function() {
            dialog.style.display = 'none'; // Hide the dialog after animation
            profileOverlay.style.display = 'none'; // Hide the overlay
            dialog.classList.remove('fade-out'); // Remove fade-out class for next time
        }, 300); // Match the duration of the fade-out effect
    });

    // Optional: Close the overlay when clicking outside of the dialog
    window.addEventListener('click', function(event) {
        if (event.target === profileOverlay) {
            dialog.classList.add('fade-out'); // Add fade-out class
            setTimeout(function() {
                dialog.style.display = 'none'; // Hide the dialog after animation
                profileOverlay.style.display = 'none'; // Hide the overlay
                dialog.classList.remove('fade-out'); // Remove fade-out class for next time
            }, 300); // Match the duration of the fade-out effect
        }
    });
});
