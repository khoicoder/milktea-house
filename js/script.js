/* ==============================
============================== */
// script.js (dùng biến global được set bởi PHP)
const BASE = window.BASE_URL ? window.BASE_URL : "/milktea-house/";
// NOTE: dùng BASE thay vì BASE_URL để tránh tên trùng

/* ==============================
   ADD TO CART
============================== */

function addCart(id) {
  if (!isLoggedIn) {
    alert("Bạn cần đăng nhập để thêm sản phẩm");
    window.location.href = BASE + "pages/login.php";
    return;
  }

  fetch(BASE + "ajax/add_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "id=" + id,
  })
    .then((res) => res.text()) // lấy text trước để debug nếu JSON sai
    .then((text) => {
      // debug: nếu JSON lỗi, log toàn bộ text để kiểm tra
      try {
        const data = JSON.parse(text);
        console.log("Add cart response:", data);

        if (data.message) showToast(data.message);
        if (
          data.status === "success" &&
          typeof data.cart_count !== "undefined"
        ) {
          updateCartCount(data.cart_count);
        }
      } catch (err) {
        console.error("Invalid JSON from add_cart.php:", err, text);
        // hiển thị 1 thông báo thân thiện
        showToast("Lỗi server: response không hợp lệ. Kiểm tra console.");
      }
    })
    .catch((err) => {
      console.error("Fetch error:", err);
      showToast("Có lỗi mạng. Vui lòng thử lại.");
    });
}

/* ==============================
   UPDATE CART COUNT
============================== */

function updateCartCount(count) {
  const cart = document.getElementById("cart-count");

  if (cart) {
    cart.textContent = count;
  }
}

/* ==============================
   USER DROPDOWN MENU
============================== */

function toggleUserMenu() {
  const userMenu = document.querySelector(".user-menu");

  if (!userMenu) return;

  userMenu.classList.toggle("open");

  // Close notification menu if open
  const notiDropdown = document.getElementById("notiDropdown");
  if (notiDropdown) notiDropdown.classList.remove("active");
}

/* ==============================
   NOTIFICATION MENU
============================== */

function toggleNotiMenu() {
  const notiDropdown = document.getElementById("notiDropdown");
  if (!notiDropdown) return;

  notiDropdown.classList.toggle("active");

  // Close user menu if open
  const userMenu = document.querySelector(".user-menu");
  if (userMenu) userMenu.classList.remove("open");
}

/* ==============================
   MOBILE MENU TOGGLE
============================== */

function toggleMobileMenu() {
  const mainMenu = document.getElementById("mainMenu");
  if (mainMenu) {
    mainMenu.classList.toggle("mobile-active");
  }
}

/* close dropdown when click outside */

document.addEventListener("click", function (e) {
  const userMenu = document.querySelector(".user-menu");
  const notiWrapper = document.querySelector(".noti-wrapper");
  const notiDropdown = document.getElementById("notiDropdown");
  const mainMenu = document.getElementById("mainMenu");
  const mobileToggle = document.querySelector(".mobile-toggle");

  // Handle User Menu
  if (userMenu && !userMenu.contains(e.target)) {
    userMenu.classList.remove("open");
  } else if (userMenu && userMenu.contains(e.target) && e.target.tagName === 'A') {
    userMenu.classList.remove("open");
  }

  // Handle Notification Menu
  if (notiWrapper && !notiWrapper.contains(e.target)) {
    if (notiDropdown && notiDropdown.classList.contains("active")) {
      notiDropdown.classList.remove("active");
    }
  }

  // Handle Mobile Menu
  if (mainMenu && !mainMenu.contains(e.target) && mobileToggle && !mobileToggle.contains(e.target)) {
    mainMenu.classList.remove("mobile-active");
  }
});

/* ==============================
   TOAST NOTIFICATION
============================== */

function showToast(message) {
  const toast = document.getElementById("toast");

  if (!toast) return;

  toast.textContent = message;
  toast.classList.add("show");

  setTimeout(() => {
    toast.classList.remove("show");
  }, 2000);
}

/* ==============================
   REGISTER FORM VALIDATION
============================== */

function validateForm() {
  let username = document.getElementById("username")?.value.trim();
  let email = document.getElementById("email")?.value.trim();
  let password = document.getElementById("password")?.value;
  let confirm = document.getElementById("confirm_password")?.value;

  let valid = true;

  setError("usernameError", "");
  setError("emailError", "");
  setError("passwordError", "");
  setError("confirmError", "");

  if (!username) {
    setError("usernameError", "Vui lòng nhập tên");
    valid = false;
  } else if (username.length < 3) {
    setError("usernameError", "Username phải ít nhất 3 ký tự");
    valid = false;
  }

  if (!email) {
    setError("emailError", "Vui lòng nhập email");
    valid = false;
  }

  if (!password) {
    setError("passwordError", "Vui lòng nhập mật khẩu");
    valid = false;
  }

  if (!confirm) {
    setError("confirmError", "Vui lòng nhập lại mật khẩu");
    valid = false;
  }

  if (password !== confirm) {
    setError("confirmError", "Mật khẩu không khớp");
    valid = false;
  }

  return valid;
}

function setError(id, message) {
  const el = document.getElementById(id);

  if (el) {
    el.textContent = message;
  }
}

/* ==============================
   PASSWORD STRENGTH CHECK
============================== */

document.addEventListener("DOMContentLoaded", () => {
  const passwordInput = document.getElementById("password");

  if (!passwordInput) return;

  passwordInput.addEventListener("keyup", function () {
    let password = this.value;
    let error = "";

    if (password.length < 6) {
      error = "Mật khẩu phải ít nhất 6 ký tự";
    } else if (!/[A-Z]/.test(password)) {
      error = "Phải có ít nhất 1 chữ in hoa";
    } else if (!/[0-9]/.test(password)) {
      error = "Phải có ít nhất 1 chữ số";
    } else if (!/\W/.test(password)) {
      error = "Phải có ký tự đặc biệt";
    }

    setError("passwordError", error);
  });
});
