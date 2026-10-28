const updates = [
  "Check your timetable for the latest class schedule.",
  "Remember to check assignment due dates.",
  "Visit Notices to read campus updates."
];

let currentUpdate = 0;

document.getElementById("menuButton").addEventListener("click", function () {
  const navigation = document.getElementById("navigation");
  navigation.hidden = !navigation.hidden;
});

document.getElementById("next").addEventListener("click", function () {
  currentUpdate = (currentUpdate + 1) % updates.length;
  document.getElementById("slideText").textContent = updates[currentUpdate];
});

document.getElementById("previous").addEventListener("click", function () {
  currentUpdate = (currentUpdate - 1 + updates.length) % updates.length;
  document.getElementById("slideText").textContent = updates[currentUpdate];
});

document.getElementById("helpButton").addEventListener("click", function () {
  document.getElementById("helpText").hidden = false;
});

document.getElementById("themeButton").addEventListener("click", function () {
  document.body.classList.toggle("dark");

  this.textContent = document.body.classList.contains("dark")
    ? "Switch to Light Mode"
    : "Switch to Dark Mode";
});