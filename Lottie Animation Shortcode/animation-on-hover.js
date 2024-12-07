document.addEventListener("DOMContentLoaded", function() {
    const lottieContainers = document.querySelectorAll('.lottie-animation-container');

    lottieContainers.forEach(function(container) {
        const jsonPath = container.getAttribute('data-json');
        const hoverSelector = container.getAttribute('data-hover-selector');
        
        const animation = lottie.loadAnimation({
            container: container,
            renderer: 'svg',
            loop: true,
            autoplay: false,
            path: jsonPath
        });

        const hoverElement = document.querySelector(hoverSelector);

        if (hoverElement) {
            hoverElement.addEventListener("mouseenter", function() {
                animation.play();
            });

            hoverElement.addEventListener("mouseleave", function() {
                animation.stop();
            });
        }
    });
});
