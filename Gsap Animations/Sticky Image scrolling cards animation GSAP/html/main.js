// Clean GSAP Sticky Scroll Animation
// Simpler approach without complex padding calculations

// Clean GSAP Sticky Scroll Animation
// Simpler approach without complex padding calculations

document.addEventListener("DOMContentLoaded", function () {
  // Check if GSAP and ScrollTrigger are available
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") {
    console.warn("GSAP or ScrollTrigger not loaded");
    return;
  }
  
  gsap.registerPlugin(ScrollTrigger);

  // Main elements
  const container = document.querySelector(".card_container");
  const imageContainer = document.querySelector(".card_wrap_img");
  const cardsContainer = document.querySelector(".card_parent");
  const cards = gsap.utils.toArray(".ls_card");
  const images = gsap.utils.toArray(".card_img");

  // Exit if required elements not found
  if (!container || !cards.length || !images.length) {
    console.warn("Required elements not found");
    return;
  }

  const isMobile = window.innerWidth <= 768;
  let scrollTriggerInstance = null;

  // Mobile behavior: move images into cards and skip animations
  if (isMobile) {
    // Hide the separate image container
    if (imageContainer) imageContainer.style.display = "none";

    // Place each image above the card title
    cards.forEach((card, i) => {
      const title = card.querySelector(".ls_card_title");
      const sourceImg = images[i];
      if (!title || !sourceImg) return;
      const cloned = sourceImg.cloneNode(true);
      cloned.classList.add("inline_card_img");
      cloned.removeAttribute("style");
      card.insertBefore(cloned, title);
    });

    // Kill any existing ScrollTriggers just in case
    ScrollTrigger.getAll().forEach(t => t.kill());
    return;
  }

  function initAnimation() {
    // Kill existing ScrollTrigger
    if (scrollTriggerInstance) {
      scrollTriggerInstance.kill();
    }

    // Reset all states
    resetStates();

    // Ensure first and last cards align to viewport center at start/end
    adjustCardParentPadding();

    // Create main ScrollTrigger
    scrollTriggerInstance = ScrollTrigger.create({
      trigger: container,
      start: "top top",
      end: () => `+=${cardsContainer.scrollHeight - window.innerHeight}`,
      pin: imageContainer,
      pinSpacing: true,
      scrub: 1,
      onUpdate: handleScroll,
      onRefresh: () => {
        adjustCardParentPadding();
        resetStates();
      }
    });

    // Set up individual card triggers
    setupCardTriggers();
  }

  function resetStates() {
    // Reset image states - first image visible, others hidden
    gsap.set(images, { opacity: 0 });
    if (images[0]) gsap.set(images[0], { opacity: 1 });

    // Reset card states - first card active, others inactive
    cards.forEach((card, i) => {
      card.classList.toggle("active", i === 0);
    });
  }

  function handleScroll(self) {
    // Find the card whose center is closest to the viewport center
    const viewportCenter = window.innerHeight / 2;
    let closestIndex = 0;
    let smallestDistance = Infinity;

    cards.forEach((card, index) => {
      const rect = card.getBoundingClientRect();
      const cardCenter = rect.top + rect.height / 2;
      const distance = Math.abs(cardCenter - viewportCenter);
      if (distance < smallestDistance) {
        smallestDistance = distance;
        closestIndex = index;
      }
    });

    updateActiveCard(closestIndex);
  }

  function setupCardTriggers() {
    // Keep lightweight triggers to ensure refresh behavior; selection handled in handleScroll
    cards.forEach((card, index) => {
      ScrollTrigger.create({
        trigger: card,
        start: "top bottom",
        end: "bottom top",
        onEnter: () => {},
        onEnterBack: () => {}
      });
    });
  }

  function adjustCardParentPadding() {
    if (!cards.length) return;
    const viewportCenter = window.innerHeight / 2;
    const firstHeight = cards[0].offsetHeight || 0;
    const lastHeight = cards[cards.length - 1].offsetHeight || 0;

    const topPad = Math.max(0, viewportCenter - firstHeight / 2);
    const bottomPad = Math.max(0, viewportCenter - lastHeight / 2);

    cardsContainer.style.paddingTop = `${topPad}px`;
    cardsContainer.style.paddingBottom = `${bottomPad}px`;
  }

  function updateActiveCard(index) {
    // Update card active states
    cards.forEach((card, i) => {
      card.classList.toggle("active", i === index);
    });

    // Update image visibility with smooth transition
    images.forEach((image, i) => {
      if (i === index) {
        gsap.to(image, {
          opacity: 1,
          duration: 0.6,
          ease: "power2.out"
        });
      } else {
        gsap.to(image, {
          opacity: 0,
          duration: 0.6,
          ease: "power2.out"
        });
      }
    });
  }

  // Initialize
  initAnimation();

  // Handle resize with debouncing
  let resizeTimer;
  window.addEventListener("resize", () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
      initAnimation();
      ScrollTrigger.refresh();
    }, 200);
  });

  // Optional: Add refresh method for external use
  window.refreshStickyScroll = () => {
    initAnimation();
    ScrollTrigger.refresh();
  };
});