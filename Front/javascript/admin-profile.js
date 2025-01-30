    document.addEventListener('DOMContentLoaded', function() {
        const profileLink = document.getElementById('profile-link');
        const profileOverlay = document.getElementById('MyProfile');
        const closeProfileButton = document.getElementById('close-profile');
        const dialog = document.querySelector('.dialog1');

        // Show the overlay and dialog when the profile is clicked
        profileLink.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default action
            profileOverlay.style.display = 'flex'; // Show the overlay
            dialog.style.display = 'block'; // Show the dialog
            // Trigger reflow to enable animation
            void dialog.offsetWidth; // This line forces a reflow
            dialog.classList.remove('fade-out'); // Ensure fade-out class is removed
            dialog.classList.add('fade-in'); // Add fade-in class
        });

        // Close the overlay when the close button is clicked
        closeProfileButton.addEventListener('click', function() {
            dialog.classList.add('fade-out'); // Add fade-out class
            setTimeout(function() {
                dialog.style.display = 'none'; // Hide the dialog after the animation
                profileOverlay.style.display = 'none'; // Hide the overlay
                dialog.classList.remove('fade-out'); // Remove fade-out class for next time
            }, 600); // Match the duration of the animation
        });

        // Optional: Close the overlay when clicking outside of the dialog
        window.addEventListener('click', function(event) {
            if (event.target === profileOverlay) {
                dialog.classList.add('fade-out'); // Add fade-out class
                setTimeout(function() {
                    dialog.style.display = 'none'; // Hide the dialog after the animation
                    profileOverlay.style.display = 'none'; // Hide the overlay
                    dialog.classList.remove('fade-out'); // Remove fade-out class for next time
                }, 600); // Match the duration of the animation
            }
        });
    });
