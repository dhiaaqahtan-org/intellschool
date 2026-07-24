(() => {
    "use strict";

    const routePattern = /^\/app\/finance\/reports\/?$/;
    const svgNamespace = "http://www.w3.org/2000/svg";
    const reports = [
        {
            title: "تقرير دفتر اليومية",
            description: "عرض جميع دفاتر اليومية والحركات اليومية المالية",
            href: "/app/finance/reports/day-book",
            icon: [
                "M8 2v4M16 2v4M3 9h18",
                "M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z",
            ],
        },
        {
            title: "تقرير ملخص الرسوم",
            description: "عرض ملخص شامل لجميع الرسوم والإيرادات",
            href: "/app/finance/reports/fee-summary",
            icon: [
                "M6 3h12v18H6z",
                "M9 8h6M9 12h6M9 16h4",
            ],
        },
        {
            title: "تقرير بند الرسوم",
            description: "عرض جميع بنود الرسوم وتفاصيلها",
            href: "/app/finance/reports/fee-head",
            icon: [
                "m20 13-7 7-10-10V3h7l10 10Z",
                "M7.5 7.5h.01",
            ],
        },
        {
            title: "تقرير إعفاء الرسوم",
            description: "عرض جميع إعفاءات الرسوم الممنوحة للطلاب",
            href: "/app/finance/reports/fee-concession",
            icon: [
                "M8.5 11.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM15.5 17.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z",
                "m16.5 6.5-9 11",
                "M4 18h3l2 2h6.5a3 3 0 0 0 2.3-1.1L21 15.3a1.7 1.7 0 0 0-2.4-2.4L16 15.5",
            ],
        },
        {
            title: "تقرير الرسوم المستحقة",
            description: "عرض جميع الرسوم المستحقة والمتأخرات",
            href: "/app/finance/reports/fee-due",
            icon: [
                "M6 2h9l4 4v16H6zM15 2v5h5",
                "M9 11h3M9 15h2",
                "M16 14v3l2 1",
                "M16 21a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z",
            ],
        },
        {
            title: "تقرير دفع الرسوم",
            description: "عرض جميع مدفوعات الرسوم وتفاصيلها",
            href: "/app/finance/reports/fee-payment",
            icon: [
                "M3 7h15a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z",
                "M3 8l11-5 2 4",
                "M16 14h5M17.5 11.5h2a1.5 1.5 0 0 1 0 3h-2a1.5 1.5 0 0 1 0-3Z",
            ],
        },
        {
            title: "تقرير دفع الرسوم الإلكتروني",
            description: "عرض جميع مدفوعات الرسوم الإلكترونية",
            href: "/app/finance/reports/online-fee-payment",
            icon: [
                "M4 5h16v11H4z",
                "M7 20h10M12 16v4",
                "M7 9h5M7 12h2",
            ],
        },
        {
            title: "تقرير دفع الرسوم حسب البند",
            description: "عرض مدفوعات الرسوم مجمعة حسب البند",
            href: "/app/finance/reports/head-wise-fee-payment",
            icon: [
                "M12 12V3a9 9 0 1 1-9 9h9Z",
                "M15 3.5A9 9 0 0 1 20.5 9H15V3.5Z",
            ],
        },
        {
            title: "تقرير ملخص الدفع حسب الطريقة",
            description: "عرض ملخص المدفوعات مجمعة حسب طريقة الدفع",
            href: "/app/finance/reports/payment-method-wise-fee-payment",
            icon: [
                "M3 6h18v12H3zM3 10h18",
                "M7 15h3",
            ],
        },
        {
            title: "تقرير استرداد الرسوم",
            description: "عرض جميع عمليات استرداد الرسوم وتفاصيلها",
            href: "/app/finance/reports/fee-refund",
            icon: [
                "M8 7H4v-4",
                "M4 7a9 9 0 1 1-1 9",
                "M9 12h7M12 9l-3 3 3 3",
            ],
        },
    ];

    let activeEnhancement = null;
    let scheduled = false;

    const createElement = (tag, className, text) => {
        const element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        if (text !== undefined) {
            element.textContent = text;
        }

        return element;
    };

    const createSvg = (paths, attributes = {}) => {
        const svg = document.createElementNS(svgNamespace, "svg");
        const defaults = {
            viewBox: "0 0 24 24",
            fill: "none",
            stroke: "currentColor",
            "stroke-width": "1.8",
            "stroke-linecap": "round",
            "stroke-linejoin": "round",
            "aria-hidden": "true",
        };

        Object.entries({ ...defaults, ...attributes }).forEach(([name, value]) => {
            svg.setAttribute(name, value);
        });

        paths.forEach((definition) => {
            const path = document.createElementNS(svgNamespace, "path");
            path.setAttribute("d", definition);
            svg.appendChild(path);
        });

        return svg;
    };

    const createHeading = () => {
        const hero = createElement("div", "finance-reports-hero");
        const heading = createElement("header", "finance-reports-heading");
        const eyebrow = createElement("div", "finance-reports-eyebrow");
        const eyebrowText = createElement("span", null, "المالية");
        const moneyIcon = createSvg([
            "M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z",
            "M12 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5ZM7 9h.01M17 15h.01",
        ]);
        const title = createElement("h1", null, "التقارير المالية");
        const subtitle = createElement("p", null, "اختر التقرير المطلوب لعرض البيانات والتفاصيل");
        const search = createElement("label", "finance-reports-search");
        const accessibleLabel = createElement("span", "sr-only", "البحث في التقارير");
        const input = createElement("input");
        const searchIcon = createSvg(
            ["M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Zm-1 5 4 4"],
            { "stroke-width": "2" },
        );

        title.id = "finance-reports-title";
        input.type = "search";
        input.placeholder = "البحث في التقارير...";
        input.autocomplete = "off";

        eyebrow.append(eyebrowText, moneyIcon);
        heading.append(eyebrow, title, subtitle);
        search.append(accessibleLabel, input, searchIcon);
        hero.append(heading, search);

        return { hero, input };
    };

    const createCard = (report, index, originalCard) => {
        const card = createElement(
            "a",
            `finance-report-card${index === 1 ? " is-featured" : ""}`,
        );
        const icon = createElement("span", "finance-report-icon");
        const title = createElement("h2", null, report.title);
        const description = createElement("p", null, report.description);
        const arrow = createElement("span", "finance-report-arrow");

        card.href = report.href;
        card.dataset.search = `${report.title} ${report.description}`.toLocaleLowerCase("ar");
        arrow.setAttribute("aria-hidden", "true");

        icon.appendChild(createSvg(report.icon));
        arrow.appendChild(createSvg(["M8 12h8M13 9l3 3-3 3"]));
        card.append(icon, title, description, arrow);

        card.addEventListener("click", (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            originalCard.click();
        });

        return card;
    };

    const cleanup = () => {
        if (!activeEnhancement) {
            return;
        }

        activeEnhancement.page.remove();
        activeEnhancement.originalGrid.classList.remove("finance-reports-original");
        activeEnhancement.originalHeader?.classList.remove("finance-reports-original");
        activeEnhancement = null;
    };

    const findOriginalGrid = () => {
        const main = document.querySelector("main");

        if (!main) {
            return null;
        }

        return [...main.querySelectorAll("div")].find((element) => {
            const className = element.className;

            return typeof className === "string"
                && className.includes("divide-y")
                && className.includes("sm:grid-cols-2")
                && element.children.length >= reports.length;
        }) || null;
    };

    const enhance = () => {
        scheduled = false;

        if (!routePattern.test(window.location.pathname)) {
            cleanup();
            return;
        }

        if (activeEnhancement?.page.isConnected) {
            return;
        }

        const originalGrid = findOriginalGrid();

        if (!originalGrid) {
            return;
        }

        const originalCards = [...originalGrid.children].slice(0, reports.length);

        if (originalCards.length !== reports.length) {
            return;
        }

        const container = originalGrid.parentElement;
        const originalHeader = originalGrid.previousElementSibling;
        const page = createElement("section", "finance-reports-page");
        const grid = createElement("div", "finance-reports-grid");
        const empty = createElement("p", "finance-reports-empty", "لا توجد تقارير مطابقة لبحثك.");
        const { hero, input } = createHeading();

        page.dir = "rtl";
        page.setAttribute("aria-labelledby", "finance-reports-title");
        grid.setAttribute("aria-live", "polite");
        empty.hidden = true;

        reports.forEach((report, index) => {
            grid.appendChild(createCard(report, index, originalCards[index]));
        });

        input.addEventListener("input", () => {
            const query = input.value.trim().toLocaleLowerCase("ar");
            let visibleCount = 0;

            [...grid.children].forEach((card) => {
                const isVisible = !query || card.dataset.search.includes(query);
                card.hidden = !isVisible;
                visibleCount += isVisible ? 1 : 0;
            });

            empty.hidden = visibleCount !== 0;
        });

        page.append(hero, grid, empty);
        originalGrid.classList.add("finance-reports-original");
        originalHeader?.classList.add("finance-reports-original");
        container.insertBefore(page, originalHeader || originalGrid);

        activeEnhancement = {
            page,
            originalGrid,
            originalHeader,
        };
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
