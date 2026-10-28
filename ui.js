// Show or hide the menu
document.getElementById("menuButton").onclick = function () {
const menu = document.getElementById("menu");
menu.style.display = menu.style.display === "none" ? "block" : "none";
};
// Change light and dark themes
document.getElementById("themeButton").onclick = function () {
document.body.classList.toggle("dark");
};
// Close the notice
document.getElementById("closeNotice").onclick = function () {
document.getElementById("notice").style.display = "none";
};
// FAQ questions
document.querySelectorAll(".question").forEach(function (button) {
button.onclick = function () {
const answer = button.nextElementSibling;
answer.style.display = answer.style.display === "block" ? "none" : "block";
};
});
// Simple text slider
const slides = [
"Welcome to our campus!",
"Visit the library and find a quiet place to study.",
"Join a student club and meet new friends."
];
let slideNumber = 0;
function showSlide() {
document.getElementById("slideText").textContent = slides[slideNumber];
}
document.getElementById("next").onclick = function () {
slideNumber = (slideNumber + 1) % slides.length;
showSlide();
};
document.getElementById("previous").onclick = function () {
slideNumber = (slideNumber - 1 + slides.length) % slides.length;
showSlide();
};
// Open and close the popup
document.getElementById("openModal").onclick = function () {
document.getElementById("modal").style.display = "block";
};
document.getElementById("closeModal").onclick = function () {
document.getElementById("modal").style.display = "none";
};