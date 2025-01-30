document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.getElementById('logout-link');
    const confirmationDialog = document.getElementById('confirmation-dialog');
    const confirmLogout = document.getElementById('confirm-logout');
    const cancelLogout = document.getElementById('cancel-logout');

    // Show the confirmation dialog when the logout link is clicked
    logoutLink.addEventListener('click', (event) => {
        event.preventDefault(); // Prevent the default link action
        confirmationDialog.classList.add('show'); // Show the dialog with transition
    });

    // Redirect to the logout.php file to perform the logout and update status
    confirmLogout.addEventListener('click', () => {
        window.location.href = 'logout.php'; // Redirect to the logout URL to handle the logout process
    });

    // Close the confirmation dialog if "Cancel" is clicked
    cancelLogout.addEventListener('click', () => {
        confirmationDialog.classList.remove('show'); // Hide the dialog with transition
    });

    // Optionally, close dialog if the overlay area is clicked
    confirmationDialog.addEventListener('click', (event) => {
        if (event.target === confirmationDialog) {
            confirmationDialog.classList.remove('show'); // Hide the dialog with transition
        }
    });
});
