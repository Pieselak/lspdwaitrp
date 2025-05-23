document.addEventListener("DOMContentLoaded", function () {
  const main = document.querySelector("main");
  console.log("DOM loaded with JavaScript");

  // Popup functionality

  function createAlert(parameters) {
    const popup = document.createElement("div");
    const popupColor = parameters.color || "-";
    const popupButtons = parameters.buttons || [];
    const popupClosable = parameters.closable !== undefined ? parameters.closable : true;
    const popupObject = {
      isClosed: false,
      close: () => {
        if (popup) {
          popup.classList.add("popup-out");
          setTimeout(() => {
            popup.remove()
            popupObject.isClosed = true;
          }, 250);
        }
      },
      setColor: (color) => {
        popup.classList.remove(popupColor);
        popup.classList.add(color);
      },
      setTitle: (title) => {
        const titleElement = popup.querySelector(".popup-header h2");
        if (titleElement) {
          titleElement.textContent = title;
        }
      },
      setContent: (content) => {
        const contentElement = popup.querySelector(".popup-content");
        if (contentElement) {
          contentElement.innerHTML = `<p>${content}</p>`;
        }
      },
      setButtons: (buttons) => {
        const buttonsContainer = popup.querySelector(".popup-buttons");
        if (buttonsContainer) {
          buttonsContainer.innerHTML = "";
          buttons.forEach((button) => {
            const buttonElement = document.createElement("button");
            const buttonText = button.text || "?";
            const buttonClass = button.class || "";

            buttonElement.classList.add(buttonClass);
            buttonElement.textContent = buttonText;
            buttonElement.addEventListener("click", function () {
              if (button.callback) {
                button.callback();
              }
              if (popupClosable) {
                closeAlert(popup);
              }
            });
            buttonsContainer.appendChild(buttonElement);
          });
        }
      },
    };

    popup.classList.add("popup", popupColor);
    if (parameters.title) {
      popup.innerHTML += `
      <div class="popup-header">
        <h2>${parameters.title}</h2>
       </div>`;
    }

    if (parameters.content) {
      popup.innerHTML += `
        <div class="popup-content">
            <p>${parameters.content}</p>
        </div>`;
    }

    if (popupButtons.length > 0 || popupClosable) {
      const buttonsContainer = document.createElement("div");
      buttonsContainer.classList.add("popup-buttons");

      if (popupButtons.length === 0 && popupClosable) {
        const buttonElement = document.createElement("button");
        buttonElement.classList.add(popupColor);
        buttonElement.textContent = "Zamknij"

        buttonElement.addEventListener("click", function () {
          popupObject.close();
        })
        buttonsContainer.appendChild(buttonElement);
      } else {
        popupButtons.forEach((button) => {
          const buttonElement = document.createElement("button");
          const buttonText = button.text || "?";
          const buttonClass = button.class || "";

          buttonElement.classList.add(buttonClass);
          buttonElement.textContent = buttonText;
          buttonElement.addEventListener("click", function () {
            if (button.callback) {
              button.callback();
            }
            if (popupClosable) {
              popupObject.close();
            }
          });
          buttonsContainer.appendChild(buttonElement);
        });
      }

      popup.appendChild(buttonsContainer);
    }

    main.appendChild(popup);
    return popupObject;
  }
  window.createAlert = createAlert;

  // Textarea functionality

  function autoResizeTextarea(textarea) {
    // Reset height to allow shrinking
    textarea.style.height = "auto";
    // Set the height to match the scroll height (content height)
    textarea.style.height = textarea.scrollHeight + "px";
  }

  const textareas = document.querySelectorAll("textarea[data-autoresize]");

  textareas.forEach(function (textarea) {
    // Initial resize
    autoResizeTextarea(textarea);

    // Resize on input
    textarea.addEventListener("input", function () {
      autoResizeTextarea(textarea);
    });
  });

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