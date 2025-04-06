document.addEventListener("DOMContentLoaded", function () {
  // Create the scroll-to-top button
  const scrollToTopBtn = document.createElement("button");
  scrollToTopBtn.id = "scrollToTopBtn";
  scrollToTopBtn.className = "bg-green-lier rounded-xl"; // Add Tailwind classes
  scrollToTopBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    `;

  // Add styles to the button
  scrollToTopBtn.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        color: black;
        border: none;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s, transform 0.3s;
        transform: translateY(20px);
        z-index: 1000;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    `;

  // Add the button to the body
  document.body.appendChild(scrollToTopBtn);

  // Function to handle scroll event
  function handleScroll() {
    if (window.scrollY > 300) {
      scrollToTopBtn.style.opacity = "1";
      scrollToTopBtn.style.transform = "translateY(0)";
    } else {
      scrollToTopBtn.style.opacity = "0";
      scrollToTopBtn.style.transform = "translateY(20px)";
    }
  }

  // Add scroll event listener
  window.addEventListener("scroll", handleScroll);

  // Add click event to scroll to top
  scrollToTopBtn.addEventListener("click", function () {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  });

  // Add hover effect
  scrollToTopBtn.addEventListener("mouseover", function () {
    this.style.transform = "translateY(-5px)";
  });

  scrollToTopBtn.addEventListener("mouseout", function () {
    if (window.scrollY > 300) {
      this.style.transform = "translateY(0)";
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    const answer = item.querySelector(".faq-answer");
    const arrow = item.querySelector(".arrow");

    // Set initial arrow rotation
    arrow.style.transform = "rotate(45deg)";

    question.addEventListener("click", function () {
      // Toggle the answer visibility with a class that has transitions
      answer.classList.toggle("faq-open");

      // Rotate the arrow when open
      if (answer.classList.contains("faq-open")) {
        arrow.style.transform = "rotate(90deg)";
      } else {
        arrow.style.transform = "rotate(45deg)";
      }
    });
  });
});
