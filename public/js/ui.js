function toggleEyePassword(inputId, btn) {
  const inputs = document.querySelectorAll(
    'input[type="password"], input[type="text"][name="password"], input[type="text"][name="confirm_password"]',
  );
  const clickedInput = document.getElementById(inputId);
  const isText = clickedInput.type === "password";

  document.querySelectorAll("#password, #confirm_password").forEach((input) => {
    input.type = isText ? "text" : "password";
  });

  document.querySelectorAll(".eye_password svg").forEach((svg) => {
    svg.innerHTML = isText
      ? '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  });
}

// notification
let toastTimeout = null;
function showToast(message, type = "info", duration = 3000) {
  let toast = document.getElementById("toast");

  if (!toast) {
    toast = document.createElement("div");
    toast.id = "toast";
    toast.className = "toast";
    document.body.appendChild(toast);
  }

  toast.className = `toast toast-${type}`;
  toast.textContent = message;

  void toast.offsetWidth;
  toast.classList.add("show");

  clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => toast.classList.remove("show"), duration);
}

function hidePageLoader() {
  const loader = document.getElementById("page-loader");
  if (loader) {
    loader.classList.add("hidden");
    setTimeout(() => loader.remove(), 400);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  hidePageLoader();
  initScrollAnimations();
});
