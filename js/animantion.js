// Cache for group animations
const groupCache = new Map();
// Cache for timeouts to prevent memory leaks
const timeoutCache = new Map();

// Initialize groups with performance optimization
function initializeGroups() {
  document.querySelectorAll(".animation-group").forEach((group) => {
    const trigger = group.querySelector(".group-trigger");
    if (trigger) {
      // Use spread for better performance with small arrays
      const elements = [...group.querySelectorAll(".animate")];
      groupCache.set(trigger, elements);
    }
  });
}

// Optimized time value parsing
function parseTimeValue(element, prefix) {
  for (const className of element.classList) {
    if (className.startsWith(`${prefix}-[`) && className.endsWith("]")) {
      const value = Number(className.slice(prefix.length + 2, -1));
      return {
        value: isNaN(value) ? null : value,
        className,
      };
    }
  }
  return { value: null, className: null };
}

// Clean up animation classes with performance optimization
function cleanupAnimationClasses(element) {
  const existingTimeout = timeoutCache.get(element);
  if (existingTimeout) {
    clearTimeout(existingTimeout);
    timeoutCache.delete(element);
  }

  requestAnimationFrame(() => {
    const classesToRemove = ["in-viewport", "animate"];
    const { className: durationClass } = parseTimeValue(element, "fast");
    const { className: delayClass } = parseTimeValue(element, "delays");

    if (durationClass) classesToRemove.push(durationClass);
    if (delayClass) classesToRemove.push(delayClass);

    element.classList.remove(...classesToRemove);

    const style = element.style;
    style.removeProperty("--animation-duration");
    style.removeProperty("--animation-delay");
    style.removeProperty("animation");
    style.removeProperty("will-change");
  });
}

// Handle animation of elements with performance optimization
function animateElement(element) {
  const { value: duration } = parseTimeValue(element, "fast");
  const { value: delay } = parseTimeValue(element, "delays");

  // Check if this is a reveal animation
  const isRevealAnimation =
    element.classList.contains("reveal-right") ||
    element.classList.contains("reveal-left") ||
    element.classList.contains("reveal-up") ||
    element.classList.contains("reveal-down");

  element.style.willChange = isRevealAnimation
    ? "clip-path, transform"
    : "opacity, transform";

  requestAnimationFrame(() => {
    const style = element.style;

    // Don't reset animation for reveal animations
    if (!isRevealAnimation) {
      style.animation = "none";
      element.offsetHeight;
      style.animation = null;
    }

    element.classList.add("in-viewport");

    if (duration !== null) {
      style.setProperty("--animation-duration", `${duration}s`);
    }
    if (delay !== null) {
      style.setProperty("--animation-delay", `${delay}s`);
    }

    let totalDuration = (duration || 1) + (delay || 0);

    if (element.classList.contains("text-reveal")) {
      const textDuration = duration || 2;
      const children = [...element.children];

      requestAnimationFrame(() => {
        children.forEach((child, index) => {
          child.style.cssText = `
              animation-duration: ${textDuration}s;
              animation-delay: ${(index * textDuration) / children.length}s;
              will-change: ${
                isRevealAnimation
                  ? "clip-path, transform"
                  : "opacity, transform"
              };
            `;
        });
      });

      const lastChildDelay =
        ((children.length - 1) * textDuration) / children.length;
      const textTotalDuration = lastChildDelay + textDuration;
      if (textTotalDuration > totalDuration) {
        totalDuration = textTotalDuration;
      }
    }

    const timeout = setTimeout(() => {
      style.willChange = "auto";
      cleanupAnimationClasses(element);
      timeoutCache.delete(element);
    }, totalDuration * 1000);

    timeoutCache.set(element, timeout);
  });
}

// Optimized observers
const singleObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animateElement(entry.target);
        singleObserver.unobserve(entry.target);
      }
    });
  },
  {
    threshold: 0.2,
    rootMargin: "50px 0px -100px 0px",
  }
);

const groupObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const elements = groupCache.get(entry.target);
        if (elements) {
          if (entry.target.classList.contains("animate")) {
            animateElement(entry.target);
          }

          elements.forEach((element, index) => {
            if (element !== entry.target) {
              setTimeout(() => animateElement(element), index * 100);
            }
          });

          groupCache.delete(entry.target);
          groupObserver.unobserve(entry.target);
        }
      }
    });
  },
  {
    threshold: 0.2,
    rootMargin: "50px 0px -100px 0px",
  }
);

// Initialize animations immediately
function initializeAnimations() {
  initializeGroups();

  // Batch observations
  const elements = [...document.querySelectorAll(".animate")];
  elements.forEach((element) => {
    const isInGroup = element.closest(".animation-group");
    if (!isInGroup) {
      singleObserver.observe(element);
    }
  });

  groupCache.forEach((_, trigger) => {
    groupObserver.observe(trigger);
  });
}

// Initialize immediately on load
window.addEventListener("load", initializeAnimations);

// Debounced mutation observer
let mutationTimeout;
const mutationObserver = new MutationObserver((mutations) => {
  clearTimeout(mutationTimeout);
  mutationTimeout = setTimeout(() => {
    let needsUpdate = false;

    for (const mutation of mutations) {
      for (const node of mutation.addedNodes) {
        if (
          node.classList &&
          (node.classList.contains("animate") ||
            node.classList.contains("animation-group") ||
            node.querySelector?.(".animate"))
        ) {
          needsUpdate = true;
          break;
        }
      }
      if (needsUpdate) break;
    }

    if (needsUpdate) {
      groupCache.clear();
      initializeAnimations();
    }
  }, 100);
});

mutationObserver.observe(document.body, {
  childList: true,
  subtree: true,
});

// Cleanup
window.addEventListener("unload", () => {
  singleObserver.disconnect();
  groupObserver.disconnect();
  mutationObserver.disconnect();
  clearTimeout(mutationTimeout);

  // Clear all timeouts
  timeoutCache.forEach((timeout) => clearTimeout(timeout));
  timeoutCache.clear();
  groupCache.clear();
});
