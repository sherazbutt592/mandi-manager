class ThemeLayout {
    constructor() {
        this.html = document.getElementsByTagName("html")[0];
    }

    initComponents() {
        Waves.init();
        lucide.createIcons();

        [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(e => new bootstrap.Popover(e));
        [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(e => new bootstrap.Tooltip(e));
        [...document.querySelectorAll(".offcanvas")].map(e => new bootstrap.Offcanvas(e));

        var e = document.getElementById("toastPlacement");
        if (e) {
            document.getElementById("selectToastPlacement").addEventListener("change", function () {
                if (!e.dataset.originalClass) {
                    e.dataset.originalClass = e.className;
                }
                e.className = e.dataset.originalClass + " " + this.value;
            });
        }

        [].slice.call(document.querySelectorAll(".toast")).map(e => new bootstrap.Toast(e));

        const o = document.getElementById("liveAlertPlaceholder");
        const t = document.getElementById("liveAlertBtn");
        if (t) {
            t.addEventListener("click", () => {
                var e = "Nice, you triggered this alert message!";
                var t = "success";
                const n = document.createElement("div");
                n.innerHTML = [
                    `<div class="alert alert-${t} alert-dismissible" role="alert">`,
                    `   <div>${e}</div>`,
                    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
                    "</div>"
                ].join("");
                o.append(n);
            });
        }

        var n = document.getElementById("theme-mode");
        if (n) {
            n.addEventListener("click", function () {
                var t = document.documentElement;
                var mode = t.getAttribute("data-bs-theme") === "light" ? "dark" : "light";
                t.setAttribute("data-bs-theme", mode);
                sessionStorage.setItem("themeMode", mode);
            });
        }

        var storedMode = sessionStorage.getItem("themeMode");
        var preferredMode = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
        var finalMode = storedMode || preferredMode;
        document.documentElement.setAttribute("data-bs-theme", finalMode);
        sessionStorage.setItem("themeMode", finalMode);
    }

    initfullScreenListener() {
        var e = document.querySelector('[data-bs-toggle="fullscreen"]');
        if (e) {
            e.addEventListener("click", function (event) {
                event.preventDefault();
                document.body.classList.toggle("fullscreen-enable");
                if (
                    !document.fullscreenElement &&
                    !document.mozFullScreenElement &&
                    !document.webkitFullscreenElement
                ) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.cancelFullScreen) {
                        document.cancelFullScreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitCancelFullScreen) {
                        document.webkitCancelFullScreen();
                    }
                }
            });
        }
    }

    initFormValidation() {
        document.querySelectorAll(".needs-validation").forEach(t => {
            t.addEventListener("submit", e => {
                if (!t.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                t.classList.add("was-validated");
            }, false);
        });
    }

    initMainMenu() {
        var e, t;
        if ($(".app-menu").length) {
            e = $(".app-menu .menu-item .collapse");
            $(".app-menu li [data-bs-toggle='collapse']").on("click", function () { return false; });
            e.on({
                "show.bs.collapse": function (event) {
                    var t = $(event.target).parents(".collapse.show");
                    $(".app-menu .collapse.show").not(event.target).not(t).collapse("hide");
                }
            });

            e = document.querySelectorAll(".app-menu .menu-link");
            t = window.location.href.split(/[?#]/)[0];
            e.forEach(function (e) {
                if (e.href === t) {
                    e.classList.add("active");
                    e.parentNode.classList.add("active");
                    e.parentNode.parentNode.parentNode.classList.add("show");
                    e.parentNode.parentNode.parentNode.parentNode.classList.add("active");
                    e.parentNode.parentNode.parentNode.parentNode.parentNode.classList.add("active");
                    e.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.classList.add("show");
                }
            });
        }
    }

    initSwitchListener() {
        var e = this;
        var t = document.querySelector(".button-toggle-menu");
        if (t) {
            t.addEventListener("click", function () {
                if ("full" === e.html.getAttribute("data-sidebar-size")) {
                    e.showBackdrop();
                }
                e.html.classList.toggle("sidebar-enable");
            });
        }
    }

    showBackdrop() {
        var e = function () {
            const e = document.createElement("div");
            e.style.width = "100px";
            e.style.height = "100px";
            e.style.overflow = "scroll";
            document.body.appendChild(e);
            var t = e.offsetWidth - e.clientWidth;
            document.body.removeChild(e);
            return t;
        }();

        const t = document.createElement("div");
        t.id = "custom-backdrop";
        t.classList = "offcanvas-backdrop fade show";
        document.body.appendChild(t);
        document.body.style.overflow = "hidden";
        document.body.style.paddingRight = e + "px";
        const n = this;
        t.addEventListener("click", function () {
            n.html.classList.remove("sidebar-enable");
            n.hideBackdrop();
        });
    }

    hideBackdrop() {
        var e = document.getElementById("custom-backdrop");
        if (e) {
            document.body.removeChild(e);
            document.body.style.overflow = null;
            document.body.style.paddingRight = null;
        }
    }

    init() {
        this.initComponents();
        this.initfullScreenListener();
        this.initFormValidation();
        this.initMainMenu();
        this.initSwitchListener();
    }
}

(new ThemeLayout).init();