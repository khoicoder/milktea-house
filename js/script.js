function addCart(id) {
  fetch("/milktea-house/ajax/add_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "id=" + id,
  })
    .then((res) => res.json())
    .then((data) => {
      console.log(data);
      showToast(data.message);

      if (data.status === "success") {
        updateCartCount(data.cart_count);
      }
    });
}
function toggleUserMenu() {
  let menu = document.getElementById("userDropdown");
  if (menu.style.display === "block") {
    menu.style.display = "none";
  } else {
    menu.style.display = "block";
  }
}

function updateCartCount(count) {
  console.log("update cart:", count);
  document.getElementById("cart-count").innerText = count;
}

function showToast(message) {
  const toast = document.getElementById("toast");

  toast.innerText = message;
  toast.style.display = "block";

  setTimeout(() => {
    toast.style.display = "none";
  }, 2000);
}

document.addEventListener("click", function (e) {
  const dropdown = document.getElementById("userDropdown");
  const avatar = document.querySelector(".avatar");
  if (!dropdown) return;
  if (avatar && avatar.contains(e.target)) return;
  if (dropdown.contains(e.target)) return;
  dropdown.style.display = "none";
});
// nhắc lỗi form đăng kí
function validateForm() {
  let username = document.getElementById("username").value;
  let email = document.getElementById("email").value;
  let password = document.getElementById("password").value;
  let confirm = document.getElementById("confirm_password").value;
  let valid = true;
  // reset lỗi
  document.getElementById("usernameError").innerText = "";
  document.getElementById("emailError").innerText = "";
  document.getElementById("passwordError").innerText = "";
  document.getElementById("confirmError").innerText = "";
  if (username === "") {
    document.getElementById("usernameError").innerText = "Vui lòng nhập tên";
    valid = false;
  }
  else if (username.length < 3) {
    document.getElementById("usernameError").innerText = "Username phải ít nhất 3 ký tự";
    valid = false;
  }
  if (email === "") {
    document.getElementById("emailError").innerText = "Vui lòng nhập email";
    valid = false;
  }
  if (password === "") {
    document.getElementById("passwordError").innerText = "Vui lòng nhập mật khẩu";
    valid = false;
  }
  if (confirm === "") {
    document.getElementById("confirmError").innerText = "Vui lòng nhập lại mật khẩu";
    valid = false;
  }
  if (password !== confirm && confirm !== "") {
    document.getElementById("confirmError").innerText = "Mật khẩu không khớp";
    valid = false;
  }
  //nhắc pass yếu 
  if (document.getElementById("passwordError").innerText !== "") {
    valid = false;
  }
  return valid;
}
//check mật khẩu đủ điều kiện không
document.getElementById("password").addEventListener("keyup", function () {

  let password = this.value;
  let error = "";

  if (password.length < 6) {
    error = "Mật khẩu phải ít nhất 6 ký tự";
  }
  else if (!/[A-Z]/.test(password)) {
    error = "Phải có ít nhất 1 chữ in hoa";
  }
  else if (!/[0-9]/.test(password)) {
    error = "Phải có ít nhất 1 chữ số";
  }
  else if (!/\W/.test(password)) {
    error = "Phải có ký tự đặc biệt";
  }

  document.getElementById("passwordError").innerText = error;

});
