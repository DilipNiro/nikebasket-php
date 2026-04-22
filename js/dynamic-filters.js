// Fonction pour récupérer les filtres actuels
function getCurrentFilters() {
    const filters = {
        categorie: document.getElementById('categorie').value,
        tailles: [...document.querySelectorAll('input[name="tailles[]"]:checked')].map(input => input.value),
        couleurs: [...document.querySelectorAll('input[name="couleurs[]"]:checked')].map(input => input.value),
        price_min: document.getElementById('price_min').value,
        price_max: document.getElementById('price_max').value,
        search: document.getElementById('hidden-search').value
    };
    return filters;
}

// Fonction pour mettre à jour l'URL avec les filtres
function updateURL(filters) {
    const params = new URLSearchParams();
    
    if (filters.categorie) params.append('categorie', filters.categorie);
    if (filters.search) params.append('search', filters.search);
    if (filters.price_min) params.append('price_min', filters.price_min);
    if (filters.price_max) params.append('price_max', filters.price_max);
    
    filters.tailles.forEach(taille => params.append('tailles[]', taille));
    filters.couleurs.forEach(couleur => params.append('couleurs[]', couleur));
    
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
}

// Fonction pour mettre à jour les produits
async function updateProducts(filters) {
    try {
        const params = new URLSearchParams();
        Object.entries(filters).forEach(([key, value]) => {
            if (Array.isArray(value)) {
                value.forEach(v => params.append(`${key}[]`, v));
            } else if (value) {
                params.append(key, value);
            }
        });

        const response = await fetch(`ajax-filter-products.php?${params.toString()}`);
        if (!response.ok) throw new Error('Erreur réseau');
        
        const html = await response.text();
        document.querySelector('.product-grid').innerHTML = html;
        
        // Mettre à jour l'URL sans recharger la page
        updateURL(filters);

    } catch (error) {
        console.error('Erreur lors de la mise à jour des produits:', error);
    }
}

// Fonction pour initialiser tous les écouteurs d'événements
function initializeFilters() {
    // Filtre de catégorie
    document.getElementById('categorie').addEventListener('change', function() {
        const filters = getCurrentFilters();
        updateProducts(filters);
    });

    // Filtres de taille
    document.querySelectorAll('input[name="tailles[]"]').forEach(input => {
        input.addEventListener('change', function() {
            const filters = getCurrentFilters();
            updateProducts(filters);
        });
    });

    // Filtres de couleur
    document.querySelectorAll('input[name="couleurs[]"]').forEach(input => {
        input.addEventListener('change', function() {
            const filters = getCurrentFilters();
            updateProducts(filters);
        });
    });

    // Filtres de prix
    let priceTimeout;
    const priceInputs = ['price_min', 'price_max'].map(id => document.getElementById(id));
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(priceTimeout);
            priceTimeout = setTimeout(() => {
                const filters = getCurrentFilters();
                updateProducts(filters);
            }, 500); // Délai de 500ms pour éviter trop de requêtes
        });
    });

    // Recherche
    let searchTimeout;
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            document.getElementById('hidden-search').value = this.value;
            searchTimeout = setTimeout(() => {
                const filters = getCurrentFilters();
                updateProducts(filters);
            }, 500);
        });
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', initializeFilters);