document.getElementById("forgot-password-form").addEventListener("submit", function (e) {
  e.preventDefault()

  const submitBtn = this.querySelector('button[type="submit"]')
  const originalBtnText = submitBtn.innerHTML

  // Show loading state
  submitBtn.innerHTML = `
    <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
    <span style="color: white;">Sending...</span>
  `

  submitBtn.style.backgroundColor = "#222"
  submitBtn.disabled = true

  const email = this.querySelector('input[name="email"]').value

  fetch("forgot_password.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "email=" + encodeURIComponent(email),
  })
    .then((response) => response.json())
    .then((data) => {
      // Reset button state
      submitBtn.innerHTML = originalBtnText
      submitBtn.style.backgroundColor = ""
      submitBtn.disabled = false

      // Display message using existing premium-alert system
      const alertHtml = `
            <div class="premium-alert" style="border-left-color: ${data.success ? "#28a745" : "#ff3366"}" id="premium-alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        ${
                          data.success
                            ? `
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M20 6L9 17l-5-5"/>
                            </svg>
                        `
                            : `
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        `
                        }
                    </div>
                    <span class="alert-message">${data.message}</span>
                    <button class="alert-close" onclick="closeAlert()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        `

      document.getElementById("modal-message-container").innerHTML = alertHtml

      if (data.success) {
        setTimeout(() => {
          document.getElementById("forgot-password-form").reset()
          closeAlert()
        }, 5000)
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      submitBtn.innerHTML = originalBtnText
      submitBtn.style.backgroundColor = ""
      submitBtn.disabled = false

      // Display error message
      const alertHtml = `
            <div class="premium-alert" id="premium-alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <span class="alert-message">An error occurred. Please try again.</span>
                    <button class="alert-close" onclick="closeAlert()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
        `

      document.getElementById("modal-message-container").innerHTML = alertHtml
    })
})

// Clear messages when switching forms
document.querySelectorAll("#signin-link, #signup-link, #forgot-password-link, #back-to-login-link").forEach((link) => {
  link.addEventListener("click", () => {
    document.getElementById("modal-message-container").innerHTML = ""
  })
})

function closeAlert() {
  const alert = document.getElementById("premium-alert")
  if (alert) alert.remove()
}

