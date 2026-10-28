
const eventList = document.getElementById("eventList");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortSelect = document.getElementById("sortSelect");
const status = document.getElementById("status");
const prevBtn = document.getElementById("prevBtn");
const nextBtn = document.getElementById("nextBtn");
const pageInfo = document.getElementById("pageInfo");

let events = [];
let currentPage = 1;
const pageSize = 6;

// Fetch external JSON data
async function loadEvents() {
    status.textContent = "Loading events...";

    try {
        const response = await fetch("p6-events.json");

        if (!response.ok) {
            throw new Error("Unable to load event data.");
        }

        events = await response.json();
        renderEvents();
    } catch (error) {
        status.textContent =
            "Error loading events. Please run this project using Live Server.";
        eventList.replaceChildren();
        pageInfo.textContent = "Page 0";
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        console.error(error);
    }
}

// Search, filter, sort and paginate
function renderEvents() {
    const searchText = searchInput.value.toLowerCase().trim();
    const category = categoryFilter.value;

    let filteredEvents = events.filter(function (event) {
        const matchesSearch =
            event.name.toLowerCase().includes(searchText) ||
            event.category.toLowerCase().includes(searchText) ||
            event.venue.toLowerCase().includes(searchText);

        const matchesCategory =
            category === "All" || event.category === category;

        return matchesSearch && matchesCategory;
    });

    filteredEvents.sort(function (a, b) {
        if (sortSelect.value === "name") {
            return a.name.localeCompare(b.name);
        }

        return new Date(a.date) - new Date(b.date);
    });

    const totalPages = Math.ceil(filteredEvents.length / pageSize);
    currentPage = Math.min(currentPage, Math.max(totalPages, 1));

    const start = (currentPage - 1) * pageSize;
    const pageEvents = filteredEvents.slice(start, start + pageSize);

    eventList.replaceChildren();

    if (pageEvents.length === 0) {
        status.textContent = "No events found.";
    } else {
        status.textContent =
            "Showing " + pageEvents.length + " of " +
            filteredEvents.length + " events";
    }

    // Create event cards dynamically
    pageEvents.forEach(function (event) {
        const card = document.createElement("article");
        card.className = "event-card";

        const title = document.createElement("h2");
        title.textContent = event.name;

        const categoryLabel = document.createElement("span");
        categoryLabel.className = "category";
        categoryLabel.textContent = event.category;

        const date = document.createElement("p");
        date.textContent = "Date: " + event.date;

        const time = document.createElement("p");
        time.textContent = "Time: " + event.time;

        const venue = document.createElement("p");
        venue.textContent = "Venue: " + event.venue;

        const description = document.createElement("p");
        description.textContent = event.description;

        card.append(title, categoryLabel, date, time, venue, description);
        eventList.appendChild(card);
    });

    pageInfo.textContent =
        "Page " + (filteredEvents.length ? currentPage : 0) +
        " of " + totalPages;

    prevBtn.disabled = currentPage <= 1 || totalPages === 0;
    nextBtn.disabled = currentPage >= totalPages;
}

// Search and filter events
searchInput.addEventListener("input", function () {
    currentPage = 1;
    renderEvents();
});

categoryFilter.addEventListener("change", function () {
    currentPage = 1;
    renderEvents();
});

// Sort events
sortSelect.addEventListener("change", function () {
    currentPage = 1;
    renderEvents();
});

// Pagination
prevBtn.addEventListener("click", function () {
    if (currentPage > 1) {
        currentPage--;
        renderEvents();
    }
});

nextBtn.addEventListener("click", function () {
    currentPage++;
    renderEvents();
});

// Start the application
loadEvents();
