// Gestionnaire global pour tous les éléments interactifs
class UIManager {
    constructor() {
        this.initializeAccountDropdown();
        this.initializeCarousel();
    }

    initializeAccountDropdown() {
        const accountIcon = document.getElementById('account-icon');
        const accountDropdown = document.getElementById('account-dropdown');
        
        if (accountIcon && accountDropdown) {
            accountIcon.addEventListener('click', (event) => {
                event.stopPropagation();
                accountDropdown.classList.toggle('show');
            });

            accountDropdown.addEventListener('click', (event) => {
                event.stopPropagation();
            });

            document.addEventListener('click', (event) => {
                if (!accountIcon.contains(event.target) && !accountDropdown.contains(event.target)) {
                    accountDropdown.classList.remove('show');
                }
            });
        }
    }

    initializeCarousel() {
        const mainImage = document.getElementById('main-product-image');
        const thumbnailsContainer = document.querySelector('.thumbnails');
        const prevButton = document.querySelector('.thumbnail-nav.prev');
        const nextButton = document.querySelector('.thumbnail-nav.next');
        const thumbnails = Array.from(document.querySelectorAll('.thumbnail'));

        if (!mainImage || !thumbnailsContainer) {
            // Si pas de carousel, on s'arrête ici
            return;
        }

        let currentIndex = 0;
        let autoSlideInterval;

        const autoSlide = () => {
            currentIndex = (currentIndex + 1) % thumbnails.length;
            const nextThumbnail = thumbnails[currentIndex];
            this.changeMainImage(nextThumbnail.src, nextThumbnail);
        };

        const startAutoSlide = () => {
            this.stopAutoSlide();
            autoSlideInterval = setInterval(autoSlide, 5000);
        };

        this.stopAutoSlide = () => {
            if (autoSlideInterval) {
                clearInterval(autoSlideInterval);
            }
        };

        if (prevButton && nextButton) {
            prevButton.addEventListener('click', () => {
                this.stopAutoSlide();
                currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                this.changeMainImage(thumbnails[currentIndex].src, thumbnails[currentIndex]);
                startAutoSlide();
            });

            nextButton.addEventListener('click', () => {
                this.stopAutoSlide();
                currentIndex = (currentIndex + 1) % thumbnails.length;
                this.changeMainImage(thumbnails[currentIndex].src, thumbnails[currentIndex]);
                startAutoSlide();
            });
        }

        if (thumbnailsContainer) {
            thumbnailsContainer.addEventListener('mouseenter', this.stopAutoSlide);
            thumbnailsContainer.addEventListener('mouseleave', startAutoSlide);
            startAutoSlide();
        }
    }

    changeMainImage(src, thumbnail) {
        const mainImage = document.getElementById('main-product-image');
        if (mainImage) {
            mainImage.src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => 
                thumb.classList.remove('active'));
            thumbnail.classList.add('active');
        }
    }
}

// Initialiser l'UI quand le DOM est chargé
document.addEventListener('DOMContentLoaded', () => {
    const ui = new UIManager();
    
    // Rendre changeMainImage disponible globalement si nécessaire
    window.changeMainImage = (src, thumbnail) => ui.changeMainImage(src, thumbnail);
});