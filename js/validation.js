document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signup-form');
    
    // Function to validate email
    function validateEmail(email) {
        if (!email) return { isValid: true, message: '' };
        const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
        return {
            isValid: emailRegex.test(email),
            message: emailRegex.test(email) ? '' : 'Please enter a valid email address'
        };
    }

    // Function to validate phone
    function validatePhone(phone) {
        if (!phone) return { isValid: true, message: '' };
        const phoneRegex = /^[6-9]\d{9}$/;
        return {
            isValid: phoneRegex.test(phone),
            message: phoneRegex.test(phone) ? '' : 'Please enter a valid 10-digit mobile number'
        };
    }

    // Function to validate GST
    function validateGST(gst) {
        if (!gst) return { isValid: true, message: '' };
        
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        
        if (!gstRegex.test(gst)) {
            return { 
                isValid: false, 
                message: 'Invalid GST format. Format should be like: 29ABCDE1234F1Z5'
            };
        }

        const validStateCodes = [
            '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
            '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
            '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
            '31', '32', '33', '34', '35', '36', '37'
        ];

        if (!validStateCodes.includes(gst.substring(0, 2))) {
            return {
                isValid: false,
                message: 'Invalid state code in GST number'
            };
        }

        return { isValid: true, message: '' };
    }

    // Function to validate password
    function validatePassword(password) {
        if (!password) return { isValid: true, message: '', requirements: [] };
        
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        const requirements = [];
        if (password.length < minLength) requirements.push('at least 8 characters');
        if (!hasUpperCase) requirements.push('one uppercase letter');
        if (!hasLowerCase) requirements.push('one lowercase letter');
        if (!hasNumbers) requirements.push('one number');
        if (!hasSpecialChar) requirements.push('one special character');
        
        return {
            isValid: requirements.length === 0,
            requirements: requirements,
            message: requirements.length > 0 ? 'Password must contain ' + requirements.join(', ') : 'Password strength: Good'
        };
    }

    if (signupForm) {
        const emailInput = document.getElementById('email');
        const phoneInput = document.getElementById('phone-number');
        const passwordInput = document.getElementById('password');
        const gstInput = document.querySelector('input[name="user_gst"]');
        
        // Email validation
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                const emailError = document.getElementById('email-error-message');
                const validation = validateEmail(this.value);
                
                emailError.textContent = validation.message;
                emailError.style.color = validation.isValid ? 'green' : 'red';
                this.style.borderColor = this.value ? (validation.isValid ? 'green' : 'red') : '#ccc';
            });
        }

        // Phone validation
        if (phoneInput) {
            phoneInput.addEventListener('input', function() {
                const phoneError = document.getElementById('phone-error-message');
                const validation = validatePhone(this.value);
                
                phoneError.textContent = validation.message;
                phoneError.style.color = validation.isValid ? 'green' : 'red';
                this.style.borderColor = this.value ? (validation.isValid ? 'green' : 'red') : '#ccc';
            });
        }

        // GST validation
        if (gstInput) {
            // Add error message span if it doesn't exist
            if (!document.getElementById('gst-error-message')) {
                const errorSpan = document.createElement('span');
                errorSpan.id = 'gst-error-message';
                errorSpan.style.fontSize = '12px';
                errorSpan.style.marginTop = '4px';
                errorSpan.style.display = 'block';
                gstInput.parentNode.appendChild(errorSpan);
            }

            gstInput.addEventListener('input', function() {
                const gstError = document.getElementById('gst-error-message');
                const validation = validateGST(this.value);
                
                gstError.textContent = validation.message;
                gstError.style.color = validation.isValid ? 'green' : 'red';
                this.style.borderColor = this.value ? (validation.isValid ? 'green' : 'red') : '#ccc';
            });
        }

        // Password validation
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const passwordError = document.getElementById('password-error');
                const validation = validatePassword(this.value);
                
                passwordError.textContent = validation.message;
                passwordError.style.color = validation.isValid ? 'green' : 'red';
                this.style.borderColor = this.value ? (validation.isValid ? 'green' : 'red') : '#ccc';
            });
        }

        // Form submission
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let isValid = true;
            const email = emailInput.value;
            const phone = phoneInput.value;
            const password = passwordInput.value;
            const gst = gstInput ? gstInput.value : '';

            // Only validate non-empty fields
            if (email && !validateEmail(email).isValid) {
                isValid = false;
            }

            if (phone && !validatePhone(phone).isValid) {
                isValid = false;
            }

            if (password && !validatePassword(password).isValid) {
                isValid = false;
            }

            if (gst && !validateGST(gst).isValid) {
                isValid = false;
            }

            // Submit form only if all filled fields are valid
            if (isValid) {
                // Check if required fields are filled
                if (!email || !phone || !password) {
                    alert('Please fill in all required fields');
                    return;
                }
                this.submit();
            }
        });
    }
});