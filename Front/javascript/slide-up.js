document.addEventListener('DOMContentLoaded', () => {
    // Select only the specific divs
    const slideUpDivs = document.querySelectorAll('.cont-burial,.otc-cont, .schedule-cont, .content-detail-serv');

    // Add the 'slide-up' class to each selected div
    slideUpDivs.forEach(div => {
        div.classList.add('slide-up');
    });

    // Use an IntersectionObserver to trigger the animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                // Add the 'show' class with a delay based on the index
                setTimeout(() => {
                    entry.target.classList.add('show');
                }, index * 200); // Adjust delay as needed (200ms between each animation)
            }
        });
    });

    // Observe each selected div for visibility
    slideUpDivs.forEach(div => observer.observe(div));
});
