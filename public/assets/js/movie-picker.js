// for the movie search box on the admin schedule form. Typing shows
// matching titles; clicking one fills in the hidden movie id the form submits.

const searchInput = document.getElementById('movieSearch');
const resultsBox = document.getElementById('movieResults');
const movieIdInput = document.getElementById('movieId');
const movies = JSON.parse(searchInput.dataset.movies);

searchInput.addEventListener('input', function () {
    movieIdInput.value = '';
    const term = this.value.trim().toLowerCase();

    if (term === '') {
        resultsBox.classList.add('is-hidden');
        // Remove all old results when the search field is empty.
        resultsBox.replaceChildren();
        return;
    }

    const matches = movies.filter(m => m.title.toLowerCase().includes(term)).slice(0, 8);

    // Clear the previous results before adding the new matches.
    resultsBox.replaceChildren();
    // Create one search-result button for each matching movie.
    for (const movie of matches) {
        // Create a real button instead of building an HTML string from the movie title.
        const button = document.createElement('button');
        // Clicking a result should select a movie, not submit the form.
        button.type = 'button';
        // Store the movie ID and title on the button so the click handler below can read them.
        button.dataset.id = movie.id;
        button.dataset.title = movie.title;
        // Use the existing CSS class to keep the same appearance.
        button.className = 'schedule-picker__result';
        // Display the title as plain text. Even a title containing HTML will not run as code.
        button.textContent = movie.title;
        // Add the finished button to the visible list of results.
        resultsBox.append(button);
    }

    // If nothing matched, show a message instead of an empty results box.
    if (matches.length === 0) {
        const emptyMessage = document.createElement('p');
        emptyMessage.className = 'schedule-picker__empty';
        emptyMessage.textContent = 'No matches';
        resultsBox.append(emptyMessage);
    }

    resultsBox.classList.remove('is-hidden');
});

resultsBox.addEventListener('click', function (event) {
    const button = event.target.closest('button[data-id]');
    if (!button) {
        return;
    }

    movieIdInput.value = button.dataset.id;
    searchInput.value = button.dataset.title;
    resultsBox.classList.add('is-hidden');
    // The movie has been selected, so remove the search-result buttons.
    resultsBox.replaceChildren();
});
