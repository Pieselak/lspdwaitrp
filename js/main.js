document.addEventListener("DOMContentLoaded", function () {
  console.log("DOM loaded with JavaScript");

  // Theme functionality
  const themeButton = document.querySelector(".theme-button");
  const themeIcon = document.querySelector(".theme-button .theme-icon");
  const themeText = document.querySelector(".theme-button .theme-text");

  loadTheme();
  themeButton.addEventListener("click", toggleTheme);

  function applyTheme() {
    const currentTheme = localStorage.getItem("currentTheme") ?? "dark";
    const theme = currentTheme === "light" ? "light" : "dark";

    document.documentElement.setAttribute("data-theme", theme);

    if (theme === "light") {
      themeIcon.classList.remove("bx-moon");
      themeIcon.classList.add("bx-sun");
      themeText.innerText = "Motyw jasny";
    } else {
      themeIcon.classList.remove("bx-sun");
      themeIcon.classList.add("bx-moon");
      themeText.innerText = "Motyw ciemny";
    }
  }

  function toggleTheme() {
    const currentTheme = localStorage.getItem("currentTheme") ?? "dark";

    if (currentTheme === "light") {
      localStorage.setItem("currentTheme", "dark");
    } else {
      localStorage.setItem("currentTheme", "light");
    }

    applyTheme();
  }

  function loadTheme() {
    const currentTheme = localStorage.getItem("currentTheme") ?? "dark";
    localStorage.setItem("currentTheme", currentTheme);
    applyTheme(currentTheme);
  }

  // Background functionality

  const backgrounds = [
    "bg-1.png",
    "bg-2.png",
    "bg-3.png",
    "bg-4.png",
    "bg-5.png",
    "bg-6.png",
    "bg-7.png",
    "bg-8.png",
  ];

  function changeBackground() {
    const backgroundElement = document.querySelector(".background");
    const currentBackground = localStorage.getItem("currentBackground") ?? 0;
    let nextBackground = Number(currentBackground) + 1;

    if (nextBackground >= backgrounds.length) {
      nextBackground = 0;
    }
    localStorage.setItem("currentBackground", nextBackground);

    if (backgroundElement) {
      const currentOpacity = window.getComputedStyle(backgroundElement).opacity;
      let timeout = 0;
      if (currentOpacity === "1" || !backgroundElement.style.backgroundImage) {
        backgroundElement.classList.remove("fade-in");
        backgroundElement.classList.add("fade-out");
        timeout = 1000;
      }
      setTimeout(() => {
        backgroundElement.style.backgroundImage = `url(assets/backgrounds/${backgrounds[nextBackground]})`;
        backgroundElement.classList.remove("fade-out");
        backgroundElement.classList.add("fade-in");
      }, timeout);
    }
  }

  changeBackground();
  setInterval(changeBackground, 30000);

  // Navbar functionality
  const menuButton = document.querySelector(".menu-button");
  const menuIcon = document.querySelector(".menu-button .menu-icon");
  const navbar = document.querySelector(".navbar");

  function toggleNavbar(state) {
    function hideNavbar() {
      navbar.style.display = "none";
      menuIcon.classList.add("bx-menu");
      menuIcon.classList.remove("bx-x");
    }

    function showNavbar() {
      navbar.style.display = "flex";
      menuIcon.classList.remove("bx-menu");
      menuIcon.classList.add("bx-x");
    }

    if (state === "hide") {
      hideNavbar();
    } else if (state === "show") {
      showNavbar();
    } else {
      if (navbar.style.display === "none") {
        showNavbar();
      } else {
        hideNavbar();
      }
    }
  }

  menuButton.addEventListener("click", toggleNavbar);

  function handleResponsiveNavbar() {
    const windowWidth = window.innerWidth;

    if (windowWidth > 1300) {
      toggleNavbar("show");
    } else {
      toggleNavbar("hide");
    }
  }

  handleResponsiveNavbar();
  window.addEventListener("resize", handleResponsiveNavbar);

  const profileUsername = document.querySelector(".profile-username");

  // Dropdown functionality
  const dropdownButtons = document.querySelectorAll(".dropdown-button");

  function closeAllDropdowns() {
    dropdownButtons.forEach((button) => {
      button.setAttribute("expanded", "false");
      const dropdown = button.nextElementSibling;
      if (dropdown) {
        dropdown.style.display = "none";
      }

      const toggleIcon = button.querySelector(".toggle-icon");
      if (toggleIcon) {
        toggleIcon.style.transform = "rotate(0deg)";
      }
    });
  }

  document.addEventListener("click", function (event) {
    if (!event.target.closest(".dropdown")) {
      closeAllDropdowns();
    }
  });

  // Add click event to each dropdown button
  dropdownButtons.forEach((button) => {
    button.addEventListener("click", function (event) {
      event.stopPropagation(); // Prevent document click handler

      const dropdown = this.nextElementSibling;
      const isExpanded = this.getAttribute("expanded") === "true";
      const toggleIcon = this.querySelector(".toggle-icon");

      // Close all other dropdowns first
      dropdownButtons.forEach((otherButton) => {
        if (otherButton !== this) {
          otherButton.setAttribute("expanded", "false");
          const otherDropdown = otherButton.nextElementSibling;
          if (otherDropdown) {
            otherDropdown.style.display = "none";
          }

          const otherToggleIcon = otherButton.querySelector(".toggle-icon");
          if (otherToggleIcon) {
            otherToggleIcon.style.transform = "rotate(0deg)";
          }
        }
      });

      // Toggle current dropdown
      this.setAttribute("expanded", !isExpanded);

      if (isExpanded) {
        dropdown.style.display = "none";
        if (toggleIcon) {
          toggleIcon.style.transform = "rotate(0deg)";
        }
      } else {
        dropdown.style.display = "flex";
        if (toggleIcon) {
          toggleIcon.style.transform = "rotate(180deg)";
        }
      }
    });
  });
});
