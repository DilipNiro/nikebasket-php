
//CAROUSEL 

let currentSlide = 0;
const slides = document.querySelectorAll('.carousel-slide');

function showSlide(n) {
	slides.forEach(slide => slide.classList.remove('active'));

	currentSlide = n;
	if (currentSlide >= slides.length) currentSlide = 0;
	if (currentSlide < 0) currentSlide = slides.length - 1;

	slides[currentSlide].classList.add('active');
}

function moveSlide(direction) {
	showSlide(currentSlide + direction);
}

// Navigation au clavier
document.addEventListener('keydown', (e) => {
	if (e.key === 'ArrowLeft') moveSlide(-1);
	if (e.key === 'ArrowRight') moveSlide(1);
});

// Défilement automatique toutes les 5 secondes
let autoSlideInterval = setInterval(() => moveSlide(1), 3000);

// Pause le défilement automatique quand la souris est sur le carousel
document.querySelector('.carousel').addEventListener('mouseenter', () => {
	clearInterval(autoSlideInterval);
});

// Reprend le défilement automatique quand la souris quitte le carousel
document.querySelector('.carousel').addEventListener('mouseleave', () => {
	autoSlideInterval = setInterval(() => moveSlide(1), 3000);
});

// Swipe sur mobile
let touchStartX = 0;
let touchEndX = 0;

document.querySelector('.carousel').addEventListener('touchstart', (e) => {
	touchStartX = e.touches[0].clientX;
});

document.querySelector('.carousel').addEventListener('touchend', (e) => {
	touchEndX = e.changedTouches[0].clientX;
	handleSwipe();
});

function handleSwipe() {
	const swipeThreshold = 50;
	const swipeDistance = touchEndX - touchStartX;

	if (Math.abs(swipeDistance) > swipeThreshold) {
		if (swipeDistance > 0) {
			moveSlide(-1); // Swipe droite
		} else {
			moveSlide(1); // Swipe gauche
		}
	}
}


// PARTIE AJOUT 

// Prévisualisation de l'image
document.getElementById('image').addEventListener('change', function (e) {
	const preview = document.getElementById('imagePreview');
	const file = e.target.files[0];

	if (file) {
		const reader = new FileReader();
		reader.onload = function (e) {
			preview.src = e.target.result;
			preview.style.display = 'block';
		}
		reader.readAsDataURL(file);
	} else {
		preview.style.display = 'none';
	}
});

// Désactiver le formulaire après soumission
document.getElementById('uploadForm').addEventListener('submit', function (e) {
	const submitButton = this.querySelector('button[type="submit"]');
	submitButton.disabled = true;
	submitButton.textContent = 'Envoi en cours...';
});