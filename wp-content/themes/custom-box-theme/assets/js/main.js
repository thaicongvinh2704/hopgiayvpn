document.addEventListener("DOMContentLoaded", function () {
    const searchForms = document.querySelectorAll("[data-search-suggestions]");

    searchForms.forEach((form) => {
        const input = form.querySelector("[data-search-input]");
        const results = form.querySelector("[data-search-results]");

        if (!input || !results || typeof customBoxSearch === "undefined" || !customBoxSearch.endpoint) {
            return;
        }

        const cache = new Map();
        const minLength = Number(customBoxSearch.minLength || 2);
        const debounceDelay = Number(customBoxSearch.debounce || 150);
        let controller = null;
        let timer = null;
        let previousQuery = "";

        const hideResults = () => {
            results.hidden = true;
            results.innerHTML = "";
            form.classList.remove("has-suggestions");
        };

        const escapeHtml = (value) => String(value || "").replace(/[&<>"']/g, (char) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
        }[char]));

        const renderResults = (items, query) => {
            const safeQuery = encodeURIComponent(query);
            const searchUrl = `${customBoxSearch.searchUrl}?s=${safeQuery}`;

            if (!items.length) {
                results.innerHTML = `<div class="header-search-empty">No matching results</div><a class="header-search-all" href="${searchUrl}">Search for "${escapeHtml(query)}"</a>`;
                results.hidden = false;
                form.classList.add("has-suggestions");
                return;
            }

            const itemsHtml = items.map((item) => {
                const title = escapeHtml(item.title);
                const type = escapeHtml(item.type);
                const url = escapeHtml(item.url || "#");

                return `
                    <a class="header-search-suggestion" href="${url}">
                        <span>${title}</span>
                        <small>${type}</small>
                    </a>
                `;
            }).join("");

            results.innerHTML = `${itemsHtml}<a class="header-search-all" href="${searchUrl}">View all results for "${escapeHtml(query)}"</a>`;
            results.hidden = false;
            form.classList.add("has-suggestions");
        };

        const requestResults = (query) => {
            if (cache.has(query)) {
                renderResults(cache.get(query), query);
                return;
            }

            if (controller) {
                controller.abort();
            }

            controller = new AbortController();

            fetch(`${customBoxSearch.endpoint}?q=${encodeURIComponent(query)}`, {
                signal: controller.signal,
                credentials: "same-origin"
            })
                .then((response) => response.ok ? response.json() : [])
                .then((items) => {
                    const limitedItems = Array.isArray(items) ? items.slice(0, 6) : [];
                    cache.set(query, limitedItems);
                    renderResults(limitedItems, query);
                })
                .catch((error) => {
                    if (error.name !== "AbortError") {
                        hideResults();
                    }
                });
        };

        input.addEventListener("input", () => {
            const query = input.value.trim();
            const shouldRequestImmediately = query.length <= 3 || previousQuery.length < minLength;
            previousQuery = query;

            window.clearTimeout(timer);

            if (query.length < minLength) {
                hideResults();
                return;
            }

            if (shouldRequestImmediately) {
                requestResults(query);
                return;
            }

            timer = window.setTimeout(() => requestResults(query), debounceDelay);
        });

        input.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                hideResults();
                input.blur();
            }
        });

        document.addEventListener("click", (event) => {
            if (!form.contains(event.target)) {
                hideResults();
            }
        });
    });

    const productGallery = document.querySelector("[data-product-gallery]");

    if (productGallery) {
        const slides = Array.from(productGallery.querySelectorAll(".product-gallery-slide"));
        const thumbs = Array.from(productGallery.querySelectorAll(".product-gallery-thumb"));
        const prevButton = productGallery.querySelector(".product-gallery-prev");
        const nextButton = productGallery.querySelector(".product-gallery-next");
        let galleryIndex = 0;
        let galleryTimer = null;

        const showGallerySlide = (nextIndex) => {
            if (!slides.length) return;

            galleryIndex = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
                slide.classList.toggle("is-active", index === galleryIndex);
            });

            thumbs.forEach((thumb, index) => {
                thumb.classList.toggle("is-active", index === galleryIndex);
            });
        };

        const startGalleryTimer = () => {
            if (galleryTimer || slides.length < 2) return;
            galleryTimer = setInterval(() => {
                showGallerySlide(galleryIndex + 1);
            }, 3500);
        };

        const restartGalleryTimer = () => {
            if (galleryTimer) {
                clearInterval(galleryTimer);
                galleryTimer = null;
            }
            startGalleryTimer();
        };

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener("click", () => {
                showGallerySlide(index);
                restartGalleryTimer();
            });
        });

        if (prevButton) {
            prevButton.addEventListener("click", () => {
                showGallerySlide(galleryIndex - 1);
                restartGalleryTimer();
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", () => {
                showGallerySlide(galleryIndex + 1);
                restartGalleryTimer();
            });
        }

        showGallerySlide(0);
        startGalleryTimer();
    }

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
            if (window.matchMedia("(max-width: 560px)").matches) return 3;
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
    const mobileToggle = document.querySelector(".mobile-menu-icon");
    const menu = document.querySelector(".nav-menu");
    const navInner = document.querySelector(".nav-inner");

    const setMenuState = (isOpen) => {
        menu.classList.toggle("active", isOpen);
        if (navInner) {
            navInner.classList.toggle("is-open", isOpen);
        }
        if (toggle) {
            toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        }
        if (mobileToggle) {
            mobileToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        }
    };

    const handleMenuToggle = () => {
        const isOpen = !menu.classList.contains("active");
        setMenuState(isOpen);
    };

    if (menu && (toggle || mobileToggle)) {
        if (toggle) {
            toggle.addEventListener("click", handleMenuToggle);
        }

        if (mobileToggle) {
            mobileToggle.addEventListener("click", handleMenuToggle);
        }

        if (false) {
            const isOpen = menu.classList.toggle("active");
            if (navInner) {
                navInner.classList.toggle("is-open", isOpen);
            }
            toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        }

        menu.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", function () {
                if (!window.matchMedia("(max-width: 768px)").matches) return;
                setMenuState(false);
            });
        });

        window.addEventListener("resize", function () {
            if (window.matchMedia("(min-width: 769px)").matches) {
                setMenuState(false);
            }
        });
    }

});

document.addEventListener("DOMContentLoaded", function () {
    const tocBlocks = Array.from(document.querySelectorAll(".blog-toc"));

    tocBlocks.forEach(toc => {
        const tocToggle = toc.querySelector(".blog-toc-toggle");
        const tocLinks = Array.from(toc.querySelectorAll(".blog-toc-link"));
        const headings = tocLinks
            .map(link => document.querySelector(link.getAttribute("href")))
            .filter(Boolean);

        if (tocToggle) {
            tocToggle.addEventListener("click", function () {
                const isOpen = toc.classList.toggle("is-open");
                tocToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
                const icon = tocToggle.querySelector("i");

                if (icon) {
                    icon.classList.toggle("fa-chevron-up", isOpen);
                    icon.classList.toggle("fa-chevron-down", !isOpen);
                }
            });
        }

        tocLinks.forEach(link => {
            link.addEventListener("click", function () {
                if (window.matchMedia("(max-width: 900px)").matches && tocToggle) {
                    toc.classList.remove("is-open");
                    tocToggle.setAttribute("aria-expanded", "false");
                }
            });
        });

        if ("IntersectionObserver" in window && headings.length) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    tocLinks.forEach(link => link.classList.remove("is-active"));
                    const activeLink = toc.querySelector(`.blog-toc-link[href="#${entry.target.id}"]`);
                    if (activeLink) activeLink.classList.add("is-active");
                });
            }, {
                rootMargin: "-20% 0px -70% 0px",
                threshold: 0
            });

            headings.forEach(heading => observer.observe(heading));
        }
    });

    document.querySelectorAll(".blog-faq-question").forEach(button => {
        button.addEventListener("click", function () {
            const item = button.closest(".blog-faq-item");
            if (!item) return;

            const isOpen = item.classList.toggle("is-open");
            button.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
    });
});
