(() => {
    "use strict";

    const collapsedStorageKey = "instikit.sidebar.collapsed";
    const retiredSitePaths = new Set([
        "/app/site/menus",
        "/app/site/blocks",
    ]);
    const svgNamespace = "http://www.w3.org/2000/svg";
    let currentShell = null;
    let autoExpandedPath = null;
    let scheduled = false;

    const createChevronIcon = () => {
        const svg = document.createElementNS(svgNamespace, "svg");

        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("fill", "none");
        svg.setAttribute("stroke", "currentColor");
        svg.setAttribute("stroke-width", "1.8");
        svg.setAttribute("stroke-linecap", "round");
        svg.setAttribute("stroke-linejoin", "round");
        svg.setAttribute("aria-hidden", "true");

        ["M13 6 7 12l6 6", "M19 6l-6 6 6 6"].forEach((definition) => {
            const path = document.createElementNS(svgNamespace, "path");
            path.setAttribute("d", definition);
            svg.appendChild(path);
        });

        return svg;
    };

    const setCollapsed = (shell, collapsed) => {
        shell.classList.toggle("is-collapsed", collapsed);
        localStorage.setItem(collapsedStorageKey, collapsed ? "1" : "0");

        shell.querySelectorAll("[data-sidebar-toggle]").forEach((button) => {
            button.setAttribute("aria-expanded", collapsed ? "false" : "true");
            button.title = collapsed ? "توسيع القائمة" : "طي القائمة";
        });
    };

    const createToggle = (className, label) => {
        const button = document.createElement("button");

        button.type = "button";
        button.className = className;
        button.dataset.sidebarToggle = "true";
        button.setAttribute("aria-controls", "desktop-main-navigation");
        button.appendChild(createChevronIcon());

        if (label) {
            const text = document.createElement("span");
            text.textContent = label;
            button.prepend(text);
        }

        return button;
    };

    const createShellControls = (shell, panel, scroller) => {
        const heading = document.createElement("div");
        const title = document.createElement("h2");
        const topToggle = createToggle("app-sidebar-toggle");
        const footer = document.createElement("div");
        const bottomToggle = createToggle("", "طي القائمة");

        heading.className = "app-sidebar-heading";
        title.textContent = "القائمة الرئيسية";
        footer.className = "app-sidebar-footer";
        scroller.id = "desktop-main-navigation";

        heading.append(title, topToggle);
        footer.appendChild(bottomToggle);
        panel.insertBefore(heading, scroller);
        panel.appendChild(footer);

        [topToggle, bottomToggle].forEach((button) => {
            button.addEventListener("click", () => {
                setCollapsed(shell, !shell.classList.contains("is-collapsed"));
            });
        });
    };

    const expandActiveSection = (panel) => {
        if (autoExpandedPath === window.location.pathname) {
            return;
        }

        if (!window.location.pathname.startsWith("/app/finance/")) {
            return;
        }

        const topLevelItems = panel.querySelectorAll("nav > div > ul > li");
        const financeItem = [...topLevelItems].find((item) => {
            return item.firstElementChild?.textContent.trim() === "المالية";
        });

        if (!financeItem) {
            return;
        }

        autoExpandedPath = window.location.pathname;

        if (financeItem.children.length === 1) {
            financeItem.firstElementChild.click();
        }
    };

    const removeRetiredSiteLinks = () => {
        document.querySelectorAll('a[href^="/app/site/"]').forEach((link) => {
            let path;

            try {
                path = new URL(link.href, window.location.origin).pathname.replace(/\/+$/, "");
            } catch {
                return;
            }

            if (!retiredSitePaths.has(path)) {
                return;
            }

            (link.closest("li") || link).remove();
        });
    };

    const enhance = () => {
        scheduled = false;
        removeRetiredSiteLinks();

        const shell = document.querySelector(".hidden.lg\\:flex.lg\\:shrink-0");
        const panel = shell?.firstElementChild;
        const scroller = panel?.querySelector(":scope > .scroller-thin-y");

        if (!shell || !panel || !scroller) {
            return;
        }

        if (currentShell !== shell || !panel.classList.contains("app-sidebar-panel")) {
            currentShell = shell;
            shell.classList.add("app-sidebar-shell");
            panel.classList.add("app-sidebar-panel");
            autoExpandedPath = null;

            if (!panel.querySelector(".app-sidebar-heading")) {
                createShellControls(shell, panel, scroller);
            }

            setCollapsed(shell, localStorage.getItem(collapsedStorageKey) === "1");

            if (panel.classList.contains("w-16") && !shell.classList.contains("is-collapsed")) {
                panel.dispatchEvent(new MouseEvent("mouseenter", { bubbles: true }));
                return;
            }
        }

        expandActiveSection(panel);
    };

    const scheduleEnhancement = () => {
        if (scheduled) {
            return;
        }

        scheduled = true;
        window.requestAnimationFrame(enhance);
    };

    const observer = new MutationObserver(scheduleEnhancement);
    observer.observe(document.documentElement, { childList: true, subtree: true });
    window.addEventListener("popstate", scheduleEnhancement);

    ["pushState", "replaceState"].forEach((method) => {
        const original = history[method];

        history[method] = function (...args) {
            const result = original.apply(this, args);
            scheduleEnhancement();
            return result;
        };
    });

    scheduleEnhancement();
})();
