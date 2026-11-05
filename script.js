var messages = {
    login: "Please login first.",
    wrong: "Wrong username/email or password.",
    empty: "Please fill all the fields.",
    email: "Please enter a valid email.",
    short: "Password must be at least 6 characters.",
    match: "Passwords do not match.",
    exists: "Username or email is already registered.",
    registered: "Account created! You can login now.",
    logout: "You logged out successfully."
};

var params = new URLSearchParams(window.location.search);
var msgBox = document.getElementById("message");

if (msgBox) {
    var error = params.get("error");
    var success = params.get("success");
    if (error) {
        msgBox.textContent = messages[error] || "Something went wrong.";
        msgBox.className = "alert alert-danger";
    } else if (success) {
        msgBox.textContent = messages[success] || "Done.";
        msgBox.className = "alert alert-success";
    }
}

var registerForm = document.getElementById("registerForm");

if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
        var password = document.getElementById("password").value;
        var confirm = document.getElementById("confirm_password");

        if (password.length < 6) {
            e.preventDefault();
            alert("Password must be at least 6 characters.");
        } else if (password != confirm.value) {
            e.preventDefault();
            confirm.classList.add("is-invalid");
        }
    });
}

var studentForm = document.getElementById("studentForm");

function showErrors(errors) {
    var fields = ["name", "email", "phone", "address"];
    fields.forEach(function (field) {
        var input = studentForm.querySelector("[name=" + field + "]");
        var errorDiv = document.getElementById(field + "-error");
        if (errors[field]) {
            input.classList.add("is-invalid");
            input.classList.remove("is-valid");
            errorDiv.textContent = errors[field];
        } else {
            input.classList.remove("is-invalid");
            input.classList.add("is-valid");
        }
    });
}

function checkStudent(callback) {
    var data = new FormData(studentForm);
    data.delete("picture");

    fetch("validate_student.php", { method: "POST", body: data })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            showErrors(result.errors);
            if (callback) {
                callback(result.valid);
            }
        })
        .catch(function () {
            if (callback) {
                studentForm.submit();
            }
        });
}

if (studentForm) {
    var inputs = studentForm.querySelectorAll("input[type=text], input[type=email], textarea");
    inputs.forEach(function (input) {
        input.addEventListener("blur", function () {
            checkStudent();
        });
    });

    studentForm.addEventListener("submit", function (e) {
        e.preventDefault();
        checkStudent(function (valid) {
            if (valid) {
                studentForm.submit();
            }
        });
    });

    document.getElementById("picture").addEventListener("change", function () {
        var preview = document.getElementById("preview");
        if (this.files[0]) {
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove("d-none");
        }
    });
}

document.querySelectorAll(".delete-form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
        if (!confirm("Are you sure you want to delete this student?")) {
            e.preventDefault();
        }
    });
});
