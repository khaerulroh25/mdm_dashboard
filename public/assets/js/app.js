// File: assets/js/googleplay.js

document.addEventListener("DOMContentLoaded", function () {
  const selectAll = document.getElementById("selectAll");
  if (!selectAll) return;

  const checkboxes = document.querySelectorAll(".app-checkbox");

  selectAll.addEventListener("change", function () {
    checkboxes.forEach((cb) => (cb.checked = selectAll.checked));
  });
});
