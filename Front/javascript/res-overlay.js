 const cancelButton = document.getElementById("button-delete"); // Cancel reservation button
    const cancelDialog = document.getElementById("cancel-confirmation-dialog");
    const confirmCancelButton = document.getElementById("confirm-cancel"); // Confirm cancel button
    const cancelCancelButton = document.getElementById("cancel-cancel"); // Cancel button

    // Show the cancel confirmation dialog when the user clicks the cancel reservation button
    cancelButton.addEventListener("click", function(event) {
        event.preventDefault(); // Prevent the form from submitting immediately
        cancelDialog.style.display = "block"; // Show the confirmation dialog
    });

    // If user confirms the cancellation
    confirmCancelButton.addEventListener("click", function() {
        // Proceed with the cancellation (e.g., submit the form)
        document.querySelector("form[action='reservation-cancel.php']").submit();
    });

    // If user cancels the cancellation
    cancelCancelButton.addEventListener("click", function() {
        cancelDialog.style.display = "none"; // Hide the confirmation dialog
    });

