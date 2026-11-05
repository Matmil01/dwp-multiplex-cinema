 // JavaScript for "Filter catalog..." on the admin Movies page.
document.getElementById('catalogFilter').addEventListener('input', function () {
    var term = this.value.toLowerCase();
    document.querySelectorAll('#catalogList [data-title]').forEach(function (row) {
        row.style.display = row.dataset.title.includes(term) ? '' : 'none';
    });
});
