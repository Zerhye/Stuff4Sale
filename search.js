document.querySelector("#searchButton").addEventListener("click", function() {
    const searchQuery = document.querySelector("#searchInput").value;
    fetch(`search_items.php?query=${searchQuery}`)
        .then(response => response.json())
        .then(data => {
            document.querySelector("#itemsList").innerHTML = data.map(item => `
                <div class="item">
                    <h2>${item.name}</h2>
                    <p>Price: $${item.price}</p>
                    <img src="${item.image_path}" alt="${item.name}" />
                </div>
            `).join('');
        });
});
