document.addEventListener("DOMContentLoaded", function () {
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  gsap.registerPlugin(ScrollTrigger);

  document.querySelectorAll(".card-wrapper").forEach(wrapper => {
    const overlay = wrapper.querySelector(".card-overlay");
    if (!overlay) return;

    gsap.to(overlay, {
      opacity: 0.6,
      ease: "none",
      scrollTrigger: {
        trigger: wrapper,
        start: "top 0%",
        end: "bottom 30%",
        scrub: true
      }
    });
  });
});