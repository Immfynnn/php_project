document.addEventListener('DOMContentLoaded', () => {
    const profileLink = document.getElementById('profile-link'); // The profile icon
    const profileOverlay = document.getElementById('MyProfile');
    const profileDialog = document.querySelector('.dialog1');
    const closeButton = document.getElementById('close-profile');

    // Function to open the profile with slide-down animation
    const openProfile = () => {
        profileOverlay.style.display = 'flex'; // Make the overlay visible
        setTimeout(() => {
            profileDialog.classList.add('slide-down'); // Trigger slide-down animation
            profileDialog.classList.remove('slide-up'); // Ensure slide-up is removed
        }, 10); // Small delay to allow display change to take effect
    };

    // Function to close the profile with slide-up animation
    const closeProfile = () => {
        profileDialog.classList.add('slide-up'); // Trigger slide-up animation
        profileDialog.classList.remove('slide-down'); // Ensure slide-down is removed
        setTimeout(() => {
            profileOverlay.style.display = 'none'; // Hide the overlay after animation
        }, 300); // Matches the CSS transition duration
    };

    // Event listener for profile icon click
    profileLink.addEventListener('click', (e) => {
        e.preventDefault(); // Prevent default link behavior
        openProfile(); // Trigger slide-down animation
    });

    // Event listener for close button
    closeButton.addEventListener('click', closeProfile);

    // Close profile when clicking outside the dialog
    profileOverlay.addEventListener('click', (e) => {
        if (e.target === profileOverlay) closeProfile();
    });
});