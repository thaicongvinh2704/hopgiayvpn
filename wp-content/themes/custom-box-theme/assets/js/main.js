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

        const buildSearchUrl = (query) => {
            const searchUrl = new URL(customBoxSearch.searchUrl, window.location.origin);
            searchUrl.searchParams.set("s", query);
            searchUrl.searchParams.set("post_type", "product");
            searchUrl.searchParams.set("vpn_search_scope", "header_product");

            if (searchUrl.origin === window.location.origin) {
                return `${searchUrl.pathname}${searchUrl.search}`;
            }

            return searchUrl.toString();
        };

        const buildEndpointUrl = (query) => {
            const endpointUrl = new URL(customBoxSearch.endpoint, window.location.origin);
            endpointUrl.searchParams.set("q", query);
            endpointUrl.searchParams.set("post_type", "product");
            endpointUrl.searchParams.set("vpn_search_scope", "header_product");

            return endpointUrl.toString();
        };

        const renderResults = (items, query) => {
            const searchUrl = buildSearchUrl(query);

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

            fetch(buildEndpointUrl(query), {
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
            if (event.key === "Enter") {
                const query = input.value.trim();

                hideResults();

                if (!query) {
                    event.preventDefault();
                    return;
                }

                event.preventDefault();

                if (typeof form.requestSubmit === "function") {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }

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

    const mobileSearchToggle = document.querySelector("[data-mobile-search-toggle]");
    const headerSearchForm = document.querySelector(".header-search[data-search-suggestions]");

    if (mobileSearchToggle && headerSearchForm) {
        const headerSearchInput = headerSearchForm.querySelector("[data-search-input]");

        const setMobileSearchState = (isOpen) => {
            headerSearchForm.classList.toggle("is-mobile-open", isOpen);
            mobileSearchToggle.classList.toggle("is-open", isOpen);
            mobileSearchToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
            mobileSearchToggle.setAttribute("aria-label", isOpen ? "Close product search" : "Open product search");

            if (isOpen && headerSearchInput) {
                window.setTimeout(() => headerSearchInput.focus(), 80);
            }
        };

        mobileSearchToggle.addEventListener("click", () => {
            setMobileSearchState(!headerSearchForm.classList.contains("is-mobile-open"));
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && headerSearchForm.classList.contains("is-mobile-open")) {
                setMobileSearchState(false);
                mobileSearchToggle.focus();
            }
        });

        window.addEventListener("resize", () => {
            if (window.matchMedia("(min-width: 768px)").matches) {
                setMobileSearchState(false);
            }
        });
    }

    const productGallery = document.querySelector("[data-product-gallery]");

    if (productGallery) {
        const slides = Array.from(productGallery.querySelectorAll(".product-gallery-slide"));
        const thumbs = Array.from(productGallery.querySelectorAll(".product-gallery-thumb"));
        const prevButton = productGallery.querySelector(".product-gallery-prev");
        const nextButton = productGallery.querySelector(".product-gallery-next");
        let galleryIndex = 0;

        const showGallerySlide = (nextIndex) => {
            if (!slides.length) return;

            galleryIndex = (nextIndex + slides.length) % slides.length;

            slides.forEach((slide, index) => {
                const isActive = index === galleryIndex;
                slide.classList.toggle("is-active", isActive);
                slide.hidden = !isActive;
                slide.setAttribute("aria-hidden", isActive ? "false" : "true");
            });

            thumbs.forEach((thumb, index) => {
                const isActive = index === galleryIndex;
                thumb.classList.toggle("is-active", isActive);
                thumb.setAttribute("aria-selected", isActive ? "true" : "false");
                thumb.setAttribute("tabindex", isActive ? "0" : "-1");
                if (isActive) {
                    thumb.setAttribute("aria-current", "true");
                } else {
                    thumb.removeAttribute("aria-current");
                }
            });
        };

        thumbs.forEach((thumb, index) => {
            thumb.addEventListener("click", () => {
                showGallerySlide(index);
            });
        });

        if (prevButton) {
            prevButton.addEventListener("click", () => {
                showGallerySlide(galleryIndex - 1);
            });
        }

        if (nextButton) {
            nextButton.addEventListener("click", () => {
                showGallerySlide(galleryIndex + 1);
            });
        }

        productGallery.addEventListener("keydown", (event) => {
            if ("ArrowLeft" === event.key) {
                event.preventDefault();
                showGallerySlide(galleryIndex - 1);
                thumbs[galleryIndex]?.focus();
            }

            if ("ArrowRight" === event.key) {
                event.preventDefault();
                showGallerySlide(galleryIndex + 1);
                thumbs[galleryIndex]?.focus();
            }
        });

        showGallerySlide(0);
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
        let totalSlides = Math.max(Math.ceil(cards.length / visibleCards) - 1, 0);
        let dots = [];

        function renderDots() {
            dotsContainer.innerHTML = "";

            for (let i = 0; i <= totalSlides; i++) {
                const dot = document.createElement("button");
                dot.type = "button";
                dot.className = "slider-dot";
                dot.setAttribute("aria-label", `Show product group ${i + 1} of ${totalSlides + 1}`);
                if (i === index) dot.classList.add("active");

                dot.addEventListener("click", () => {
                    index = i;
                    updateSlider();
                });

                dotsContainer.appendChild(dot);
            }

            dots = Array.from(dotsContainer.querySelectorAll("button"));
        }

        function updateSlider() {
            const previousTotalSlides = totalSlides;
            visibleCards = getVisibleCards();
            totalSlides = Math.max(Math.ceil(cards.length / visibleCards) - 1, 0);
            if (index > totalSlides) index = totalSlides;
            if (previousTotalSlides !== totalSlides) renderDots();

            track.style.transform = `translateX(-${index * visibleCards * cards[0].offsetWidth}px)`;

            dots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === index;
                dot.classList.toggle("active", isActive);
                dot.setAttribute("aria-pressed", isActive ? "true" : "false");
            });
        }

        window.addEventListener("resize", updateSlider);
        renderDots();
        updateSlider();
    }


    /* =========================
       FEATURED PAPER BAGS SLIDER
    ========================= */
    const featuredPaperBagsTrack = document.querySelector(".featured-paper-bags-track");
    const featuredPaperBagsSlides = document.querySelectorAll(".featured-paper-bags-slide");
    const featuredPaperBagsDotsContainer = document.querySelector(".featured-paper-bags-dots");

    if (featuredPaperBagsTrack && featuredPaperBagsSlides.length > 0 && featuredPaperBagsDotsContainer) {
        let featuredPaperBagsIndex = 0;
        let featuredPaperBagsTouchStartX = 0;
        let featuredPaperBagsVisibleCards = 6;
        let featuredPaperBagsTotalSlides = 0;
        let featuredPaperBagsDots = [];

        const getFeaturedPaperBagsVisibleCards = () => {
            if (window.matchMedia("(max-width: 640px)").matches) return 2;
            if (window.matchMedia("(max-width: 1024px)").matches) return 3;
            return 6;
        };

        const renderFeaturedPaperBagsDots = () => {
            featuredPaperBagsDotsContainer.innerHTML = "";

            for (let dotIndex = 0; dotIndex <= featuredPaperBagsTotalSlides; dotIndex += 1) {
                const dot = document.createElement("button");
                dot.type = "button";
                dot.className = "featured-paper-bags-dot";
                dot.setAttribute(
                    "aria-label",
                    `Show featured paper bag group ${dotIndex + 1} of ${featuredPaperBagsTotalSlides + 1}`
                );
                dot.addEventListener("click", () => {
                    featuredPaperBagsIndex = dotIndex;
                    updateFeaturedPaperBagsSlider();
                });
                featuredPaperBagsDotsContainer.appendChild(dot);
            }

            featuredPaperBagsDots = Array.from(
                featuredPaperBagsDotsContainer.querySelectorAll("button")
            );
        };

        const updateFeaturedPaperBagsSlider = () => {
            const previousTotalSlides = featuredPaperBagsTotalSlides;
            featuredPaperBagsVisibleCards = getFeaturedPaperBagsVisibleCards();
            featuredPaperBagsTotalSlides = Math.max(
                Math.ceil(featuredPaperBagsSlides.length / featuredPaperBagsVisibleCards) - 1,
                0
            );

            if (featuredPaperBagsIndex > featuredPaperBagsTotalSlides) {
                featuredPaperBagsIndex = featuredPaperBagsTotalSlides;
            }

            if (previousTotalSlides !== featuredPaperBagsTotalSlides) {
                renderFeaturedPaperBagsDots();
            }

            const slideWidth = featuredPaperBagsSlides[0].getBoundingClientRect().width;
            const lastPageStart = Math.max(
                featuredPaperBagsSlides.length - featuredPaperBagsVisibleCards,
                0
            );
            const pageStart = featuredPaperBagsIndex === featuredPaperBagsTotalSlides
                ? lastPageStart
                : featuredPaperBagsIndex * featuredPaperBagsVisibleCards;
            featuredPaperBagsTrack.style.transform =
                `translateX(-${pageStart * slideWidth}px)`;

            featuredPaperBagsDots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === featuredPaperBagsIndex;
                dot.classList.toggle("active", isActive);
                dot.setAttribute("aria-pressed", isActive ? "true" : "false");
            });
        };

        const goToFeaturedPaperBagsSlide = (nextIndex) => {
            if (nextIndex > featuredPaperBagsTotalSlides) {
                featuredPaperBagsIndex = 0;
            } else if (nextIndex < 0) {
                featuredPaperBagsIndex = featuredPaperBagsTotalSlides;
            } else {
                featuredPaperBagsIndex = nextIndex;
            }
            updateFeaturedPaperBagsSlider();
        };

        featuredPaperBagsTrack.addEventListener("touchstart", (event) => {
            featuredPaperBagsTouchStartX = event.changedTouches[0].screenX;
        }, { passive: true });

        featuredPaperBagsTrack.addEventListener("touchend", (event) => {
            const touchEndX = event.changedTouches[0].screenX;
            const swipeDistance = touchEndX - featuredPaperBagsTouchStartX;

            if (Math.abs(swipeDistance) < 50) return;
            goToFeaturedPaperBagsSlide(
                featuredPaperBagsIndex + (swipeDistance < 0 ? 1 : -1)
            );
        }, { passive: true });

        featuredPaperBagsVisibleCards = getFeaturedPaperBagsVisibleCards();
        featuredPaperBagsTotalSlides = Math.max(
            Math.ceil(featuredPaperBagsSlides.length / featuredPaperBagsVisibleCards) - 1,
            0
        );
        renderFeaturedPaperBagsDots();
        updateFeaturedPaperBagsSlider();
        window.addEventListener("resize", updateFeaturedPaperBagsSlider);
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
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "testi-dot";
            dot.setAttribute("aria-label", `Show testimonial ${i + 1} of ${testiSlides.length}`);
            if (i === 0) dot.classList.add("active");

            dot.addEventListener("click", () => {
                testiIndex = i;
                updateTestiSlider();
            });

            testiDotsContainer.appendChild(dot);
        });

        const testiDots = testiDotsContainer.querySelectorAll("button");

        function updateTestiSlider() {
            testiTrack.style.transform = `translateX(-${testiIndex * 100}%)`;

            testiDots.forEach((dot, dotIndex) => {
                const isActive = dotIndex === testiIndex;
                dot.classList.toggle("active", isActive);
                dot.setAttribute("aria-pressed", isActive ? "true" : "false");
            });
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

        updateTestiSlider();
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
        const panel = item.getAttribute("aria-controls")
            ? document.getElementById(item.getAttribute("aria-controls"))
            : parent.querySelector(".faq-answer");

        // toggle
        if (parent.classList.contains("active")) {
            parent.classList.remove("active");
            item.setAttribute("aria-expanded", "false");
            if (panel) panel.hidden = true;
        } else {
            document.querySelectorAll(".faq-item").forEach(i => {
                i.classList.remove("active");
                const button = i.querySelector(".faq-question");
                const answer = i.querySelector(".faq-answer");
                if (button) button.setAttribute("aria-expanded", "false");
                if (answer) answer.hidden = true;
            });
            parent.classList.add("active");
            item.setAttribute("aria-expanded", "true");
            if (panel) panel.hidden = false;
        }

    });

});
document.addEventListener("DOMContentLoaded", function () {

    const mobileToggle = document.querySelector("[data-mobile-menu-toggle]");
    const drawer = document.querySelector("[data-mobile-menu-drawer]");
    const overlay = document.querySelector("[data-mobile-menu-overlay]");
    const closeButton = drawer ? drawer.querySelector("[data-mobile-menu-close]") : null;
    const focusableSelector = [
        "a[href]",
        "button:not([disabled])",
        "input:not([disabled])",
        "select:not([disabled])",
        "textarea:not([disabled])",
        "[tabindex]:not([tabindex='-1'])"
    ].join(",");
    let lastFocusedElement = null;
    let closeTimer = null;

    const setDrawerState = (isOpen, returnFocus = true) => {
        if (!mobileToggle || !drawer || !overlay) return;

        window.clearTimeout(closeTimer);
        mobileToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        mobileToggle.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");
        drawer.setAttribute("aria-hidden", isOpen ? "false" : "true");
        document.body.classList.toggle("mobile-menu-open", isOpen);

        if (isOpen) {
            lastFocusedElement = document.activeElement;
            const openSearchToggle = document.querySelector("[data-mobile-search-toggle][aria-expanded='true']");
            if (openSearchToggle) openSearchToggle.click();
            drawer.hidden = false;
            overlay.hidden = false;
            window.requestAnimationFrame(() => {
                drawer.classList.add("is-open");
                overlay.classList.add("is-open");
                (closeButton || drawer.querySelector(focusableSelector))?.focus();
            });
            return;
        }

        drawer.classList.remove("is-open");
        overlay.classList.remove("is-open");
        closeTimer = window.setTimeout(() => {
            drawer.hidden = true;
            overlay.hidden = true;
        }, 220);

        if (returnFocus && lastFocusedElement && typeof lastFocusedElement.focus === "function") {
            lastFocusedElement.focus();
        }
    };

    if (mobileToggle && drawer && overlay) {
        mobileToggle.addEventListener("click", () => {
            setDrawerState("true" !== mobileToggle.getAttribute("aria-expanded"));
        });

        closeButton?.addEventListener("click", () => setDrawerState(false));
        overlay.addEventListener("click", () => setDrawerState(false));

        drawer.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => setDrawerState(false, false));
        });

        drawer.addEventListener("keydown", event => {
            if ("Escape" === event.key) {
                event.preventDefault();
                setDrawerState(false);
                return;
            }

            if ("Tab" !== event.key) return;

            const focusableElements = Array.from(drawer.querySelectorAll(focusableSelector))
                .filter(element => !element.hidden && null !== element.offsetParent);

            if (!focusableElements.length) {
                event.preventDefault();
                return;
            }

            const first = focusableElements[0];
            const last = focusableElements[focusableElements.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        document.addEventListener("keydown", event => {
            if ("Escape" === event.key && "true" === mobileToggle.getAttribute("aria-expanded")) {
                event.preventDefault();
                setDrawerState(false);
            }
        });

        window.addEventListener("resize", () => {
            if (window.matchMedia("(min-width: 768px)").matches) {
                setDrawerState(false, false);
            }
        });
    }

    document.querySelectorAll("[data-mobile-copy-contact]").forEach(button => {
        button.addEventListener("click", async () => {
            const value = button.getAttribute("data-mobile-copy-contact") || "";
            const status = drawer?.querySelector("[data-mobile-copy-status]");

            try {
                await navigator.clipboard.writeText(value);
                if (status) status.textContent = "Phone number copied.";
            } catch (error) {
                if (status) status.textContent = `Copy this number: ${value}`;
            }
        });
    });

});

document.addEventListener("DOMContentLoaded", function () {
    const tocBlocks = Array.from(document.querySelectorAll(".blog-toc"));

    tocBlocks.forEach(toc => {
        const tocToggle = toc.querySelector(".blog-toc-toggle");
        const tocPanel = toc.querySelector("[data-article-toc-panel], .blog-toc-panel");
        const tocLinks = Array.from(toc.querySelectorAll(".blog-toc-link"));
        const tocMedia = window.matchMedia("(max-width: 767px)");
        const headings = tocLinks
            .map(link => {
                const href = link.getAttribute("href") || "";
                if (!href.startsWith("#")) return null;
                return document.getElementById(decodeURIComponent(href.slice(1)));
            })
            .filter(Boolean);

        const setTocState = isOpen => {
            toc.classList.toggle("is-open", isOpen);

            if (tocToggle) {
                tocToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
                const icon = tocToggle.querySelector("i");

                if (icon) {
                    icon.classList.toggle("fa-chevron-up", isOpen);
                    icon.classList.toggle("fa-chevron-down", !isOpen);
                }
            }

            if (tocPanel) {
                tocPanel.hidden = !isOpen;
            }
        };

        const syncTocDefault = event => {
            const shouldOpen = event.matches
                ? "closed" !== toc.dataset.mobileDefault
                : "closed" !== toc.dataset.desktopDefault;
            setTocState(shouldOpen);
        };

        if (toc.hasAttribute("data-article-toc")) {
            syncTocDefault(tocMedia);
            tocMedia.addEventListener("change", syncTocDefault);
        }

        if (tocToggle) {
            tocToggle.addEventListener("click", function () {
                setTocState(!toc.classList.contains("is-open"));
            });
        }

        tocLinks.forEach(link => {
            link.addEventListener("click", function () {
                if (window.matchMedia("(max-width: 900px)").matches && tocToggle) {
                    setTocState(false);
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
            const panelId = button.getAttribute("aria-controls");
            const panel = panelId ? document.getElementById(panelId) : item.querySelector(".blog-faq-answer");
            if (panel) panel.hidden = !isOpen;
        });
    });

    const responsiveDisclosureQuery = [
        "details[data-responsive-disclosure]",
        "details[data-mobile-collapsed]",
        "details[data-product-overview-disclosure]",
        "details[data-mobile-disclosure]"
    ].join(",");
    const responsiveDisclosures = Array.from(document.querySelectorAll(responsiveDisclosureQuery));
    const disclosureMedia = window.matchMedia("(max-width: 767px)");

    const syncResponsiveDisclosures = event => {
        const isMobile = event.matches;

        responsiveDisclosures.forEach(disclosure => {
            disclosure.open = isMobile
                ? disclosure.hasAttribute("data-mobile-open")
                : !disclosure.hasAttribute("data-desktop-collapsed");
        });
    };

    if (responsiveDisclosures.length) {
        syncResponsiveDisclosures(disclosureMedia);
        disclosureMedia.addEventListener("change", syncResponsiveDisclosures);
    }

    document.querySelectorAll("[data-manual-logo-scroller]").forEach(scroller => {
        scroller.addEventListener("keydown", event => {
            if (!["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)) return;
            event.preventDefault();

            if ("Home" === event.key) {
                scroller.scrollTo({ left: 0, behavior: "smooth" });
                return;
            }

            if ("End" === event.key) {
                scroller.scrollTo({ left: scroller.scrollWidth, behavior: "smooth" });
                return;
            }

            scroller.scrollBy({
                left: ("ArrowLeft" === event.key ? -1 : 1) * Math.max(180, scroller.clientWidth * 0.7),
                behavior: "smooth"
            });
        });
    });

    document.querySelectorAll("[data-youtube-video-id]").forEach(button => {
        button.addEventListener("click", () => {
            const videoId = button.getAttribute("data-youtube-video-id");
            const frame = button.closest(".factory-video-frame");
            if (!videoId || !frame) return;

            const iframe = document.createElement("iframe");
            iframe.src = `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?autoplay=1&rel=0`;
            iframe.title = button.getAttribute("aria-label") || "Factory production video";
            iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
            iframe.allowFullscreen = true;
            frame.replaceChildren(iframe);
            iframe.focus();
        }, { once: true });
    });

    document.querySelectorAll("[data-article-table-scroll]").forEach(region => {
        const cue = region.parentElement?.querySelector("[data-article-table-cue]");
        region.setAttribute("tabindex", "0");
        if (cue) {
            cue.hidden = region.scrollWidth <= region.clientWidth + 1;
        }
    });

    const conversionBar = document.querySelector("[data-mobile-conversion-bar]");
    if (conversionBar) {
        document.body.classList.add("has-mobile-conversion-bar");

        const setConversionBarInputState = event => {
            const control = event.target.closest("input, select, textarea, [contenteditable='true']");
            if (!control) return;
            document.body.classList.toggle("mobile-form-control-active", "focusin" === event.type);
        };

        document.addEventListener("focusin", setConversionBarInputState);
        document.addEventListener("focusout", setConversionBarInputState);
    }
});
