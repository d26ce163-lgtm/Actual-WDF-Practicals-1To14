const form = document.getElementById("registrationForm");

 

function showError(id, message) {

    document.getElementById(id).textContent = message;

    const field = document.getElementById(id.replace("Error", ""));

    if (field) {

        field.classList.toggle("invalid", message !== "");

        field.setAttribute("aria-invalid", message !== "" ? "true" : "false");

    }

}

function clearError(id) {

    document.getElementById(id).textContent = "";

    const field = document.getElementById(id.replace("Error", ""));

    if (field) {

        field.classList.remove("invalid");

        field.setAttribute("aria-invalid", "false");

    }

}

function validateName() {

    const name = document.getElementById("name").value.trim();

    const regex = /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/;

    if (!regex.test(name)) { showError("nameError", "Enter a valid name using letters and spaces only."); return false; }

    clearError("nameError"); return true;

}

function validateEmail() {

    const email = document.getElementById("email").value.trim();

    const regex = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/;

    if (!regex.test(email)) { showError("emailError", "Enter a valid email address."); return false; }

    clearError("emailError"); return true;

}

function validateMobile() {

    const mobile = document.getElementById("mobile").value.trim();

    const regex = /^[6-9][0-9]{9}$/;

    if (!regex.test(mobile)) { showError("mobileError", "Enter a valid 10-digit Indian mobile number."); return false; }

    clearError("mobileError"); return true;

}

function validatePassword() {

    const password = document.getElementById("password").value;

    const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9\s])\S{8,}$/;

    if (!regex.test(password)) { showError("passwordError", "Use 8+ characters with uppercase, lowercase, number and special character."); return false; }

    clearError("passwordError"); return true;

}

function validateConfirmPassword() {

    const password = document.getElementById("password").value;

    const confirm = document.getElementById("confirmPassword").value;

    if (confirm === "" || password !== confirm) { showError("confirmPasswordError", "Passwords do not match."); return false; }

    clearError("confirmPasswordError"); return true;

}

function validateCourse() {

    if (document.getElementById("course").value === "") { showError("courseError", "Please select a course."); return false; }

    clearError("courseError"); return true;

}

function validateYear() {

    if (document.getElementById("year").value === "") { showError("yearError", "Please select your academic year."); return false; }

    clearError("yearError"); return true;

}

function validateGender() {

    const gender = document.querySelector('input[name="gender"]:checked');

    if (!gender) { document.getElementById("genderError").textContent = "Please select your gender."; return false; }

    document.getElementById("genderError").textContent = ""; return true;

}

function validateTerms() {

    const terms = document.getElementById("terms");

    if (!terms.checked) { document.getElementById("termsError").textContent = "You must accept the terms and conditions."; return false; }

    document.getElementById("termsError").textContent = ""; return true;

}

 

// Password strength meter

document.getElementById("password").addEventListener("input", function () {

    const password = this.value;

    const bar = document.getElementById("strengthBar");

    const text = document.getElementById("strengthText");

    let score = 0;

    if (password.length >= 8) score++;

    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;

    if (/[0-9]/.test(password)) score++;

    if (/[^A-Za-z0-9\s]/.test(password)) score++;

    const levels = [

        {label: "Very Weak", color: "#d32f2f"},

        {label: "Weak", color: "#f57c00"},

        {label: "Medium", color: "#fbc02d"},

        {label: "Strong", color: "#43a047"},

        {label: "Very Strong", color: "#16803c"}

    ];

    bar.style.width = (score * 25) + "%";

    bar.style.background = password ? levels[score].color : "transparent";

    text.textContent = password ? "Password strength: " + levels[score].label : "Password strength: Not entered";

    if (password.length > 0) validatePassword();

    if (document.getElementById("confirmPassword").value !== "") validateConfirmPassword();

});

 

// Validate fields when the user leaves them

document.getElementById("name").addEventListener("blur", validateName);

document.getElementById("email").addEventListener("blur", validateEmail);

document.getElementById("mobile").addEventListener("blur", validateMobile);

document.getElementById("password").addEventListener("blur", validatePassword);

document.getElementById("confirmPassword").addEventListener("blur", validateConfirmPassword);

document.getElementById("course").addEventListener("change", validateCourse);

document.getElementById("year").addEventListener("change", validateYear);

document.querySelectorAll('input[name="gender"]').forEach(function (radio) {

    radio.addEventListener("change", validateGender);

});

document.getElementById("terms").addEventListener("change", validateTerms);

 

// Form submission

form.addEventListener("submit", function (event) {

    event.preventDefault();

    const valid = [

        validateName(), validateEmail(), validateMobile(), validatePassword(),

        validateConfirmPassword(), validateCourse(), validateYear(),

        validateGender(), validateTerms()

    ];

    if (valid.includes(false)) {

        document.getElementById("successMessage").textContent = "";

        const firstInvalid = form.querySelector(".invalid");

        if (firstInvalid) firstInvalid.focus();

        else if (!document.querySelector('input[name="gender"]:checked')) document.querySelector('input[name="gender"]').focus();

        else if (!document.getElementById("terms").checked) document.getElementById("terms").focus();

        return;

    }

    document.getElementById("successMessage").textContent = "Registration successful!";

    // Demo only: no data is stored or sent to a server.

});

 

// Reset form and messages

form.addEventListener("reset", function () {

    setTimeout(function () {

        document.querySelectorAll(".error").forEach(function (error) { error.textContent = ""; });

        document.querySelectorAll(".invalid").forEach(function (field) {

            field.classList.remove("invalid");

            field.setAttribute("aria-invalid", "false");

        });

        document.getElementById("strengthBar").style.width = "0";

        document.getElementById("strengthText").textContent = "Password strength: Not entered";

        document.getElementById("successMessage").textContent = "";

    }, 0);

});