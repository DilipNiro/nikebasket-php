function updateTaille() {
	const color = document.querySelector('input[name="couleur"]:checked');
	const taille = document.querySelector('.size-selector .size-option');

	//hide 
	taille.forEach(options => {
		options.classList.add("indisponible");
		options.querySelector('input[name="taille"]').disabled = true;

	})

	//affichage

	if (color && stocks[color.value]) {


		Object.keys(stocks[color.value]).forEach(sizeId => {
			const taille = querySelector('input[name="taille"][value="$sizeId"])');

			if (taille) {
				taille.disabled = false;
				taille.closest('.size-option').classList.remove("indisponible");

			}
		})

	}


}

document.querySelector('input[name="couleur"]').forEach(radio => {

	radio.addEventListener('change', updateTaille);

	updateTaille();

});