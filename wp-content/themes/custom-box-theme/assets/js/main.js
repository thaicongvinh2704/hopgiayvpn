document.addEventListener("DOMContentLoaded", function () {

    const categoriesSection = document.querySelector(".categories-section");
    const categoriesMore = document.querySelector(".categories-more");

    if (categoriesSection && categoriesMore) {
        categoriesMore.addEventListener("click", function () {
            categoriesSection.classList.add("is-expanded");
            categoriesMore.style.display = "none";
        });
    }

    /* =========================
       PRODUCTS SLIDER
    ========================= */
    const track = document.querySelector(".slider-track");
    const cards = document.querySelectorAll(".slide");
    const dotsContainer = document.querySelector(".slider-dots");

    if (track && cards.length > 0 && dotsContainer) {

        let index = 0;
        const getVisibleCards = () => {
            if (window.matchMedia("(max-width: 560px)").matches) return 1;
            if (window.matchMedia("(max-width: 900px)").matches) return 3;
            return 5;
        };

        let visibleCards = getVisibleCards();
        let totalSlides = Math.max(cards.length - visibleCards, 0);
        let dots = [];

        function renderDots() {
            dotsContainer.innerHTML = "";

            for (let i = 0; i <= totalSlides; i++) {
                const dot = document.createElement("span");
                if (i === index) dot.classList.add("active");

                dot.addEventListener("click", () => {
                    index = i;
                    updateSlider();
                });

                dotsContainer.appendChild(dot);
            }

            dots = Array.from(dotsContainer.querySelectorAll("span"));
        }

        function updateSlider() {
            const previousTotalSlides = totalSlides;
            visibleCards = getVisibleCards();
            totalSlides = Math.max(cards.length - visibleCards, 0);
            if (index > totalSlides) index = totalSlides;
            if (previousTotalSlides !== totalSlides) renderDots();

            track.style.transform = `translateX(-${index * cards[0].offsetWidth}px)`;

            dots.forEach(dot => dot.classList.remove("active"));
            if (dots[index]) dots[index].classList.add("active");
        }

        setInterval(() => {
            index++;
            if (index > totalSlides) index = 0;
            updateSlider();
        }, 4000);

        window.addEventListener("resize", updateSlider);
        renderDots();
        updateSlider();
    }


    /* =========================
       TESTIMONIALS SLIDER
    ========================= */
    const testiTrack = document.getElementById("testiTrack");
    const testiSlides = document.querySelectorAll(".testi-slide");
    const testiDotsContainer = document.getElementById("testiDots");

    if (testiTrack && testiSlides.length > 0 && testiDotsContainer) {

        let testiIndex = 0;
        let touchStartX = 0;
        let touchEndX = 0;

        // CREATE DOTS AUTO
        testiSlides.forEach((_, i) => {
            const dot = document.createElement("span");
            if (i === 0) dot.classList.add("active");

            dot.addEventListener("click", () => {
                testiIndex = i;
                updateTestiSlider();
            });

            testiDotsContainer.appendChild(dot);
        });

        const testiDots = testiDotsContainer.querySelectorAll("span");

        function updateTestiSlider() {
            testiTrack.style.transform = `translateX(-${testiIndex * 100}%)`;

            testiDots.forEach(dot => dot.classList.remove("active"));
            if (testiDots[testiIndex]) {
                testiDots[testiIndex].classList.add("active");
            }
        }

        function goToTestiSlide(nextIndex) {
            testiIndex = nextIndex;
            if (testiIndex < 0) testiIndex = testiSlides.length - 1;
            if (testiIndex >= testiSlides.length) testiIndex = 0;
            updateTestiSlider();
        }

        testiTrack.addEventListener("touchstart", (event) => {
            touchStartX = event.changedTouches[0].screenX;
        }, { passive: true });

        testiTrack.addEventListener("touchend", (event) => {
            touchEndX = event.changedTouches[0].screenX;
            const swipeDistance = touchEndX - touchStartX;

            if (Math.abs(swipeDistance) < 50) return;
            goToTestiSlide(testiIndex + (swipeDistance < 0 ? 1 : -1));
        }, { passive: true });

        // AUTO SLIDE
        setInterval(() => {
            goToTestiSlide(testiIndex + 1);
        }, 4000);
    }

});
/* =========================
   FAQ ACCORDION
========================= */
/* =========================
   FAQ ADVANCED ACCORDION
========================= */
document.querySelectorAll(".faq-question").forEach(item => {

    item.addEventListener("click", () => {

        const parent = item.parentElement;

        // toggle
        if (parent.classList.contains("active")) {
            parent.classList.remove("active");
        } else {
            document.querySelectorAll(".faq-item").forEach(i => i.classList.remove("active"));
            parent.classList.add("active");
        }

    });

});
document.addEventListener("DOMContentLoaded", function () {

    const toggle = document.querySelector(".menu-toggle");
    const menu = document.querySelector(".nav-menu");

    if (toggle && menu) {
        toggle.addEventListener("click", function () {
            menu.classList.toggle("active");
        });
    }

});
