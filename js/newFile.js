//check mật khẩu đủ điều kiện không
document.getElementById("password").addEventListener("keyup", function () {
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

  document.getElementById("passwordError").innerText = error;
});
