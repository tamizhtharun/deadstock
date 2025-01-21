form.addEventListener('submit', function(event) {
    if (!termsCheckbox.checked) {
        event.preventDefault();
        alert('Please agree to the Terms and Conditions before registering.');
        return;
    }
    // Your existing form validation continues here...
});