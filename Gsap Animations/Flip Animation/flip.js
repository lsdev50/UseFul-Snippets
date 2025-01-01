document.addEventListener('DOMContentLoaded', function () {
    gsap.registerPlugin(Flip);
    
    let dogs = gsap.utils.toArray("figure");
    let bigDog = dogs[0];

    dogs.forEach((dog) => {
    dog.addEventListener("click", (e) => changeGrid(dog));
    });

    function changeGrid(dog) {
        if (dog === bigDog) return;

        let state = Flip.getState(dogs);

        bigDog.dataset.grid = dog.dataset.grid;
        dog.dataset.grid = "img-1";
        bigDog = dog;

        Flip.from(state, {
            absolute: true,
            ease: "Power1.inOut"
        });
    }
});