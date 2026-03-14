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
