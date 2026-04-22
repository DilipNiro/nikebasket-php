// Gestion de l'animation initiale des filtres
function initializeFilterAnimation() {
	const filterWrapper = document.querySelector('.filter-wrapper');

	// Ajouter la classe active après un court délai pour déclencher l'animation
	setTimeout(() => {
		if (filterWrapper) {
			filterWrapper.classList.add('active');
		}
	}, 300); // Délai réduit pour une animation plus rapide
}

// Gestion du compte dropdown
function initializeAccountDropdown() {
	const accountIcon = document.getElementById('account-icon');
	const accountDropdown = document.getElementById('account-dropdown');

	if (accountIcon && accountDropdown) {
		accountIcon.addEventListener('click', function () {
			accountDropdown.classList.toggle('show');
		});

		window.addEventListener('click', function (event) {
			if (!accountIcon.contains(event.target) && !accountDropdown.contains(event.target)) {
				accountDropdown.classList.remove('show');
			}
		});
	}
}

// Gestion de la recherche
function initializeSearch() {
	const searchInput = document.getElementById('search-input');
	const hiddenSearch = document.getElementById('hidden-search');
	const filterForm = document.getElementById('filter-form');
	const searchButton = document.querySelector('.search-button');

	if (searchInput && hiddenSearch && filterForm && searchButton) {
		// Synchroniser la recherche avec le champ caché
		searchInput.addEventListener('input', function () {
			hiddenSearch.value = this.value;
		});

		// Soumettre sur Enter
		searchInput.addEventListener('keypress', function (e) {
			if (e.key === 'Enter') {
				e.preventDefault();
				filterForm.submit();
			}
		});

		// Soumettre sur clic du bouton
		searchButton.addEventListener('click', function () {
			hiddenSearch.value = searchInput.value;
			filterForm.submit();
		});
	}
}

// Gestion de la persistance de l'état du filtre après soumission du formulaire
function handleFilterPersistence() {
	const filterWrapper = document.querySelector('.filter-wrapper');
	if (filterWrapper) {
		// Si le formulaire a déjà été soumis (vérifié via l'URL)
		if (window.location.search) {
			filterWrapper.classList.add('active');
		}
	}
}

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function () {
	handleFilterPersistence(); // Vérifier d'abord si on revient d'une recherche
	initializeFilterAnimation(); // Puis initialiser l'animation si nécessaire
	initializeAccountDropdown();
	initializeSearch();
});	