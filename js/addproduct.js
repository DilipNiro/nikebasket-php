function updateStatut() {
	const quantiteInput = document.getElementById('quantite');
	const statutSelect = document.getElementById('statut');

	// Vérifier si le statut est "archive"
	if (statutSelect.value === 'archive') {
		return; // Ne rien changer si le statut est "archive"
	}

	// Vérifier si la quantité est supérieure à 0
	if (parseInt(quantiteInput.value, 10) > 0) {
		statutSelect.value = 'actif'; // Mettre le statut à actif
	} else {
		statutSelect.value = 'en_rupture'; // Mettre le statut à en rupture
	}
}

function handleStatutChange() {
	const statutSelect = document.getElementById('statut');
	const quantiteInput = document.getElementById('quantite');

	// Si le statut est "en_rupture", mettre la quantité à 0
	if (statutSelect.value === 'en_rupture') {
		quantiteInput.value = 0; // Mettre la quantité à 0
	}
}

// Initialiser le statut et la quantité au chargement de la page
window.onload = function () {
	// Écouter les changements dans le champ de quantité
	document.getElementById('quantite').addEventListener('input', function () {
		updateStatut(); // Mettre à jour le statut en fonction de la quantité
	});

	// Écouter les changements dans le champ de statut
	document.getElementById('statut').addEventListener('change', function () {
		handleStatutChange(); // Appeler la fonction de changement de statut
	});

	// Initialisation
	updateStatut(); // Mettre à jour le statut initialement
};
