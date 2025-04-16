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

document.addEventListener("DOMContentLoaded", function () {
  // Create the page transition overlay element
  const overlay = document.createElement("div");
  overlay.className = "page-transition-overlay";
  overlay.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: #B7C784;
    z-index: 9999;
    transform: translateY(-100%);
    transition: transform 0.8s cubic-bezier(0.65, 0, 0.35, 1);
  `;
  document.body.appendChild(overlay);

  // Track if we're navigating internally
  let isInternalNavigation = false;

  // Function to trigger page transition
  function triggerPageTransition(url) {
    // Set flag to indicate internal navigation
    isInternalNavigation = true;

    // Store this in sessionStorage so the next page knows we came from internal navigation
    sessionStorage.setItem("internalNavigation", "true");

    // Disable any ongoing transitions to ensure smooth animation
    overlay.style.transition = "none";
    // Force reflow
    overlay.offsetHeight;
    // Re-enable transition with a smoother curve
    overlay.style.transition = "transform 1.2s cubic-bezier(0.16, 1, 0.3, 1)";
    // Show overlay sliding from top to bottom
    overlay.style.transform = "translateY(0)";

    // After animation completes, navigate to the new page
    // Wait for animation to fully complete before navigating
    setTimeout(() => {
      window.location.href = url;
    }, 1200); // Increased to match the new transition duration
  }

  // Handle all link clicks for internal navigation
  document.addEventListener("click", function (e) {
    // Find closest anchor tag if the click was on a child element
    const link = e.target.closest("a");

    // Check if it's a link and it's internal (same origin)
    if (
      link &&
      link.href &&
      link.hostname === window.location.hostname &&
      !link.hasAttribute("download") &&
      link.getAttribute("target") !== "_blank"
    ) {
      // Prevent default navigation
      e.preventDefault();

      // Get the URL to navigate to
      const url = link.href;

      // Trigger the transition
      triggerPageTransition(url);
    }
  });

  // Handle the incoming page (page reveal)
  const cameFromInternalNavigation =
    sessionStorage.getItem("internalNavigation") === "true";

  // Clear the flag immediately
  sessionStorage.removeItem("internalNavigation");

  // Only show the reveal animation if we came from internal navigation
  if (cameFromInternalNavigation) {
    // Ensure no transition initially
    overlay.style.transition = "none";
    overlay.style.transform = "translateY(0)";

    // Force reflow to ensure the initial state is applied
    overlay.offsetHeight;

    // Reveal the page by sliding overlay back up with a smoother curve
    setTimeout(() => {
      overlay.style.transition = "transform 1.2s cubic-bezier(0.16, 1, 0.3, 1)";
      overlay.style.transform = "translateY(-100%)";
    }, 300); // Slight delay before starting the reveal animation
  } else {
    // Make sure overlay is hidden for direct navigation or back/forward
    overlay.style.transition = "none";
    overlay.style.transform = "translateY(-100%)";
  }

  // Fix for browser back/forward navigation
  window.addEventListener("pageshow", function (event) {
    // If the page is loaded from the cache (back/forward navigation)
    if (event.persisted) {
      // Immediately hide the overlay without animation
      overlay.style.transition = "none";
      overlay.style.transform = "translateY(-100%)";

      // Force reflow
      overlay.offsetHeight;
    }
  });
});
