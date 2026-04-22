const cartModal = document.getElementById('cart-modal');
const imagePanier = document.querySelector('.cart');
const closePopUp = document.querySelector('#cart-modal .close-popup');

function showPopupPanier() {
	cartModal.style.display = 'block';
}

imagePanier.addEventListener('click', showPopupPanier);

closePopUp.addEventListener('click', () => {


	cartModal.style.display = 'none';

});

window.addEventListener('click', (event) => {

	if (event.target === cartModal) {
		cartModal.style.display = 'none';
	}


});

