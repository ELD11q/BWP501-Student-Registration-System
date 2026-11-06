var messages = {
    login: "الرجاء تسجيل الدخول أولاً.",
    wrong: "اسم المستخدم أو البريد أو كلمة المرور غير صحيحة.",
    empty: "الرجاء تعبئة جميع الحقول.",
    email: "الرجاء إدخال بريد إلكتروني صحيح.",
    short: "كلمة المرور يجب أن تكون 6 أحرف على الأقل.",
    match: "كلمتا المرور غير متطابقتين.",
    exists: "اسم المستخدم أو البريد الإلكتروني مسجل مسبقاً.",
    registered: "تم إنشاء الحساب! يمكنك تسجيل الدخول الآن.",
    logout: "تم تسجيل الخروج بنجاح."
};

var params = new URLSearchParams(window.location.search);
var msgBox = document.getElementById("message");

if (msgBox) {
    var error = params.get("error");
    var success = params.get("success");
    if (error) {
        msgBox.textContent = messages[error] || "حدث خطأ ما.";
        msgBox.className = "alert alert-danger";
    } else if (success) {
        msgBox.textContent = messages[success] || "تم.";
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
            alert("كلمة المرور يجب أن تكون 6 أحرف على الأقل.");
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

document.addEventListener("submit", function (e) {
    if (e.target.classList.contains("delete-form")) {
        if (!confirm("هل أنت متأكد من حذف هذا الطالب؟")) {
            e.preventDefault();
        }
    }
});

var searchForm = document.getElementById("searchForm");

function loadStudents(url) {
    var ajaxUrl = url + (url.indexOf("?") == -1 ? "?" : "&") + "ajax=1";

    fetch(ajaxUrl)
        .then(function (response) {
            if (response.url.indexOf("login.html") != -1) {
                window.location.href = "login.html?error=login";
            }
            return response.text();
        })
        .then(function (html) {
            document.getElementById("results").outerHTML = html;
            history.pushState(null, "", url);
        });
}

if (searchForm) {
    var searchTimer;

    function searchUrl() {
        var text = document.getElementById("search").value;
        return "view_students.php?search=" + encodeURIComponent(text) + "&page=1";
    }

    searchForm.addEventListener("submit", function (e) {
        e.preventDefault();
        loadStudents(searchUrl());
    });

    document.getElementById("search").addEventListener("input", function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            loadStudents(searchUrl());
        }, 400);
    });

    document.addEventListener("click", function (e) {
        var link = e.target.closest("#results .page-link, #results th a");
        if (link) {
            e.preventDefault();
            if (!link.closest(".disabled")) {
                loadStudents(link.getAttribute("href"));
            }
        }
    });

    window.addEventListener("popstate", function () {
        location.reload();
    });
}
