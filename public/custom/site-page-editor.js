(() => {
    "use strict";

    const routePattern = /^\/app\/site\/pages\/([^/]+)\/edit\/?$/;
    const root = document.getElementById("root");
    let scheduled = false;

    const localizedPart = (value, locale = "ar") => {
        const parts = String(value || "").split("||").map((part) => part.trim());

        if (parts.length < 2) {
            return parts[0] || "";
        }

        return locale === "en" ? parts[1] : parts[0];
    };

    const createIcon = (name) => {
        const icon = document.createElement("i");
        icon.className = `fa-solid ${name}`;
        icon.setAttribute("aria-hidden", "true");
        return icon;
    };

    const createCardHeading = ({ eyebrow, title, description, icon }) => {
        const heading = document.createElement("div");
        const iconWrap = document.createElement("span");
        const copy = document.createElement("div");
        const eyebrowText = document.createElement("span");
        const titleText = document.createElement("h3");
        const descriptionText = document.createElement("p");

        heading.className = "site-editor-card-heading";
        heading.dataset.siteEditorHeading = title;
        iconWrap.className = "site-editor-card-heading__icon";
        eyebrowText.className = "site-editor-card-heading__eyebrow";
        eyebrowText.textContent = eyebrow;
        titleText.textContent = title;
        descriptionText.textContent = description;

        iconWrap.appendChild(createIcon(icon));
        copy.append(eyebrowText, titleText, descriptionText);
        heading.append(iconWrap, copy);

        return heading;
    };

    const pagePath = (slug) => {
        const cleanSlug = String(slug || "").trim().replace(/^\/+|\/+$/g, "");
        return cleanSlug === "home" || cleanSlug === "" ? "/" : `/pages/${cleanSlug}`;
    };

    const makeAction = ({ label, icon, secondary = false, onClick, href }) => {
        const action = document.createElement(href ? "a" : "button");

        action.className = `site-editor-action${secondary ? " site-editor-action--secondary" : ""}`;
        action.append(createIcon(icon), document.createTextNode(label));

        if (href) {
            action.href = href;
            action.target = "_blank";
            action.rel = "noopener";
        } else {
            action.type = "button";
            action.addEventListener("click", onClick);
        }

        return action;
    };

    const addFieldHelp = (input, text, id) => {
        const field = input.closest("div.mt-1")?.parentElement || input.parentElement;

        if (!field || field.querySelector(`[data-site-editor-help="${id}"]`)) {
            return;
        }

        const help = document.createElement("p");
        help.id = id;
        help.className = "site-editor-field-help";
        help.dataset.siteEditorHelp = id;
        help.textContent = text;
        field.appendChild(help);
        input.setAttribute("aria-describedby", id);
    };

    const addCounter = (input, limit, label) => {
        const field = input.closest("div.mt-1")?.parentElement || input.parentElement;

        if (!field || field.querySelector(`[data-site-editor-counter="${input.name}"]`)) {
            return;
        }

        const meta = document.createElement("div");
        const descriptor = document.createElement("span");
        const count = document.createElement("span");
        const update = () => {
            count.textContent = `${input.value.length} / ${limit}`;
            count.style.color = input.value.length > limit ? "#b42318" : "";
        };

        meta.className = "site-editor-field-meta";
        meta.dataset.siteEditorCounter = input.name;
        descriptor.textContent = label;
        meta.append(descriptor, count);
        field.appendChild(meta);
        input.addEventListener("input", update);
        update();
    };

    const renameFormActions = (form, submitLabel) => {
        const submit = form.querySelector('button[type="submit"] span') || form.querySelector('button[type="submit"]');
        const reset = [...form.querySelectorAll('button[type="button"]')].find((button) => {
            return !button.classList.contains("site-editor-advanced__toggle")
                && !button.closest(".site-section-editor");
        });

        if (submit) {
            submit.textContent = submitLabel;
        }

        if (reset) {
            reset.textContent = "التراجع عن التغييرات";
        }
    };

    const sectionDefinitions = [
        { label: "المراحل والبرامج الدراسية", icon: "fa-graduation-cap" },
        { label: "المنهج الدراسي", icon: "fa-book-open" },
        { label: "أسلوب التعليم", icon: "fa-lightbulb" },
        { label: "مرافق المدرسة", icon: "fa-building-columns" },
        { label: "مخرجات التعليم", icon: "fa-chart-line" },
        { label: "الأسئلة الشائعة", icon: "fa-circle-question" },
    ];

    const ownText = (element) => {
        if (!element) {
            return "";
        }

        return [...element.childNodes]
            .filter((node) => node.nodeType === Node.TEXT_NODE)
            .map((node) => node.nodeValue)
            .join(" ")
            .replace(/\s+/g, " ")
            .trim();
    };

    const setOwnText = (element, value) => {
        const textNodes = [...element.childNodes].filter((node) => node.nodeType === Node.TEXT_NODE);

        if (!textNodes.length) {
            element.append(document.createTextNode(value));
            return;
        }

        textNodes[0].nodeValue = value;
        textNodes.slice(1).forEach((node) => {
            node.nodeValue = "";
        });
    };

    const isSafeEditorUrl = (value, attribute) => {
        const normalized = String(value || "").trim().toLowerCase();

        if (!normalized) {
            return true;
        }

        if (normalized.startsWith("/") && !normalized.startsWith("//")) {
            return true;
        }

        if (attribute === "href" && (normalized.startsWith("#") || normalized.startsWith("mailto:") || normalized.startsWith("tel:"))) {
            return true;
        }

        return normalized.startsWith("https://") || normalized.startsWith("http://");
    };

    const createStructuredField = ({
        label,
        element,
        attribute,
        multiline = false,
        direction,
        help,
        onChange,
    }) => {
        const field = document.createElement("div");
        const fieldLabel = document.createElement("label");
        const control = document.createElement(multiline ? "textarea" : "input");
        const id = `site-section-field-${crypto.randomUUID()}`;

        field.className = `site-section-field${multiline ? " site-section-field--wide" : ""}`;
        fieldLabel.htmlFor = id;
        fieldLabel.textContent = label;
        control.id = id;
        control.className = "site-section-field__control";

        if (!multiline) {
            control.type = "text";
        } else {
            control.rows = 3;
        }

        if (direction) {
            control.dir = direction;
        }

        control.value = attribute ? element.getAttribute(attribute) || "" : ownText(element);
        control.addEventListener("input", () => {
            if (attribute) {
                const value = control.value.trim();
                const isUrlAttribute = attribute === "href" || attribute === "src";
                const isValid = !isUrlAttribute || isSafeEditorUrl(value, attribute);

                control.setCustomValidity(isValid ? "" : "استخدم رابطاً داخلياً يبدأ بـ / أو رابط http أو https آمناً.");
                control.setAttribute("aria-invalid", String(!isValid));

                if (isValid) {
                    element.setAttribute(attribute, value);
                }
            } else {
                setOwnText(element, control.value);
            }
            onChange();
        });

        field.append(fieldLabel, control);

        if (help) {
            const helpText = document.createElement("p");
            helpText.className = "site-section-field__help";
            helpText.textContent = help;
            field.appendChild(helpText);
            control.setAttribute("aria-describedby", `${id}-help`);
            helpText.id = `${id}-help`;
        }

        return field;
    };

    const createStructuredItem = ({ item, index, locale, onChange }) => {
        const card = document.createElement("details");
        const summary = document.createElement("summary");
        const summaryCopy = document.createElement("span");
        const summaryType = document.createElement("small");
        const summaryTitle = document.createElement("strong");
        const body = document.createElement("div");
        const fields = document.createElement("div");
        const image = item.querySelector("img");
        const link = item.querySelector("a");
        const isQuote = item.matches("blockquote");
        const titleElement = item.querySelector("h3, summary, strong, cite");
        const metaElement = item.querySelector(".school-card__meta");
        const descriptionElement = isQuote
            ? item.querySelector("p")
            : item.matches(".school-value")
                ? item.querySelector("span")
                : item.querySelector("p");

        card.className = "site-section-item";
        summaryCopy.className = "site-section-item__summary";
        summaryType.textContent = item.matches("details")
            ? "سؤال"
            : isQuote
                ? "اقتباس"
                : `عنصر ${index + 1}`;
        summaryTitle.textContent = ownText(titleElement) || `عنصر ${index + 1}`;
        summaryCopy.append(summaryType, summaryTitle);
        summary.append(summaryCopy, createIcon("fa-chevron-down"));
        body.className = "site-section-item__body";
        fields.className = "site-section-item__fields";

        if (titleElement) {
            fields.appendChild(createStructuredField({
                label: item.matches("details")
                    ? "السؤال"
                    : isQuote
                        ? "مصدر الاقتباس"
                        : "عنوان البطاقة",
                element: titleElement,
                onChange: () => {
                    summaryTitle.textContent = ownText(titleElement) || `عنصر ${index + 1}`;
                    onChange();
                },
            }));
        }

        if (metaElement) {
            fields.appendChild(createStructuredField({
                label: "المعلومة المختصرة",
                element: metaElement,
                onChange,
            }));
        }

        if (descriptionElement) {
            fields.appendChild(createStructuredField({
                label: isQuote ? "نص الاقتباس" : item.matches("details") ? "الإجابة" : "وصف البطاقة",
                element: descriptionElement,
                multiline: true,
                onChange,
            }));
        }

        if (image) {
            const mediaFields = document.createElement("div");
            const thumbnail = document.createElement("img");

            mediaFields.className = "site-section-item__media";
            thumbnail.src = image.getAttribute("src") || "";
            thumbnail.alt = "";
            thumbnail.loading = "lazy";
            mediaFields.append(
                thumbnail,
                createStructuredField({
                    label: "مسار الصورة",
                    element: image,
                    attribute: "src",
                    direction: "ltr",
                    help: "استخدم صورة من مكتبة الموقع مثل /images/school/photo.webp",
                    onChange: () => {
                        thumbnail.src = image.getAttribute("src") || "";
                        onChange();
                    },
                }),
                createStructuredField({
                    label: "وصف الصورة",
                    element: image,
                    attribute: "alt",
                    help: "وصف قصير يساعد مستخدمي قارئ الشاشة.",
                    onChange,
                }),
            );
            body.appendChild(mediaFields);
        }

        if (link) {
            const linkFields = document.createElement("div");
            linkFields.className = "site-section-link-fields";
            linkFields.append(
                createStructuredField({
                    label: "نص الرابط",
                    element: link,
                    onChange,
                }),
                createStructuredField({
                    label: "وجهة الرابط",
                    element: link,
                    attribute: "href",
                    direction: "ltr",
                    help: "مثال: /pages/academics",
                    onChange,
                }),
            );
            fields.appendChild(linkFields);
        }

        body.prepend(fields);
        card.append(summary, body);
        return card;
    };

    const createStructuredSectionEditor = ({ editor, form, uuid }) => {
        let documentModel;
        let pageSnapshot;
        let isDirty = false;
        const shell = document.createElement("section");
        const heading = document.createElement("div");
        const headingIcon = document.createElement("span");
        const headingCopy = document.createElement("div");
        const eyebrow = document.createElement("span");
        const title = document.createElement("h3");
        const description = document.createElement("p");
        const toolbar = document.createElement("div");
        const languageControls = document.createElement("div");
        const count = document.createElement("span");
        const panels = document.createElement("div");
        const status = document.createElement("p");
        const submitButton = form.querySelector('button[type="submit"]');
        const nameInput = form.querySelector('input[name="name"]');
        const titleInput = form.querySelector('input[name="title"]');
        const subtitleInput = form.querySelector('input[name="subTitle"]');
        const endpoint = `/api/v1/app/site/pages/${uuid}`;
        const resetButton = [...form.querySelectorAll('button[type="button"]')].find((button) => {
            return !editor.contains(button);
        });
        let activeLocale = "ar";

        shell.className = "site-section-editor";
        shell.dataset.siteSectionEditor = uuid;
        heading.className = "site-section-editor__heading";
        headingIcon.className = "site-section-editor__heading-icon";
        headingCopy.className = "site-section-editor__heading-copy";
        eyebrow.className = "site-section-editor__eyebrow";
        title.textContent = "أقسام الصفحة الرئيسية";
        eyebrow.textContent = "المحتوى الذي يراه الزائر";
        description.textContent = "اختر اللغة، ثم افتح أي قسم وعدّل عناوينه وبطاقاته وصوره مباشرة.";
        headingIcon.appendChild(createIcon("fa-layer-group"));
        headingCopy.append(eyebrow, title, description);
        heading.append(headingIcon, headingCopy);
        toolbar.className = "site-section-editor__toolbar";
        languageControls.className = "site-section-editor__languages";
        languageControls.setAttribute("aria-label", "لغة محتوى الصفحة");
        count.className = "site-section-editor__count";
        panels.className = "site-section-editor__panels";
        status.className = "site-section-editor__status";
        status.setAttribute("aria-live", "polite");
        status.textContent = "جارٍ تحميل أقسام الصفحة…";

        const updateIntegritySnapshot = () => {
            if (!documentModel) {
                return;
            }

            shell.dataset.serializedLength = String(documentModel.body.innerHTML.length);
            shell.dataset.headingCount = String(documentModel.querySelectorAll("h2").length);
            shell.dataset.localeRootCount = String(documentModel.querySelectorAll("[data-home-locale]").length);
        };

        const writeToEditor = () => {
            isDirty = true;
            updateIntegritySnapshot();
            status.classList.add("is-dirty");
            status.textContent = "لديك تغييرات غير محفوظة — اضغط «حفظ محتوى الصفحة» عند الانتهاء.";
        };

        const activateLocale = (locale) => {
            activeLocale = locale;
            [...languageControls.querySelectorAll("button")].forEach((button) => {
                const selected = button.dataset.locale === locale;
                button.classList.toggle("is-active", selected);
                button.setAttribute("aria-pressed", String(selected));
            });
            [...panels.children].forEach((panel) => {
                panel.hidden = panel.dataset.locale !== locale;
            });
        };

        const render = (content) => {
            documentModel = new DOMParser().parseFromString(content, "text/html");
            const localeRoots = [...documentModel.querySelectorAll("[data-home-locale]")];

            if (!localeRoots.length) {
                return false;
            }

            languageControls.replaceChildren();
            panels.replaceChildren();

            let itemTotal = 0;

            localeRoots.forEach((localeRoot) => {
                const locale = localeRoot.getAttribute("data-home-locale") || "ar";
                const localeButton = document.createElement("button");
                const panel = document.createElement("div");
                const sections = [...localeRoot.querySelectorAll(":scope > section")];

                localeButton.type = "button";
                localeButton.dataset.locale = locale;
                localeButton.textContent = locale === "en" ? "English" : "العربية";
                localeButton.setAttribute("aria-pressed", "false");
                localeButton.addEventListener("click", () => activateLocale(locale));
                languageControls.appendChild(localeButton);

                panel.className = "site-section-editor__locale";
                panel.dataset.locale = locale;
                panel.lang = locale;
                panel.dir = locale === "en" ? "ltr" : "rtl";

                sections.forEach((section, sectionIndex) => {
                    const definition = sectionDefinitions[sectionIndex] || {
                        label: `قسم ${sectionIndex + 1}`,
                        icon: "fa-layer-group",
                    };
                    const sectionCard = document.createElement("article");
                    const sectionToggle = document.createElement("button");
                    const sectionNumber = document.createElement("span");
                    const sectionIcon = document.createElement("span");
                    const sectionSummary = document.createElement("span");
                    const sectionLabel = document.createElement("small");
                    const sectionTitle = document.createElement("strong");
                    const sectionMeta = document.createElement("span");
                    const sectionBody = document.createElement("div");
                    const mainFields = document.createElement("div");
                    const intro = section.querySelector(".school-intro, .school-section__heading");
                    const kicker = intro?.querySelector(".school-kicker");
                    const headingElement = intro?.querySelector("h2");
                    const introText = intro?.querySelector("p");
                    const callToAction = intro?.querySelector("a");
                    const itemNodes = [
                        ...section.querySelectorAll("article"),
                        ...section.querySelectorAll(".school-value"),
                        ...section.querySelectorAll("details"),
                        ...section.querySelectorAll(".school-quote"),
                    ].filter((item, index, items) => items.indexOf(item) === index);
                    const sectionBodyId = `site-section-${locale}-${sectionIndex}-${uuid}`;

                    itemTotal += itemNodes.length;
                    sectionCard.className = "site-section-card";
                    sectionToggle.className = "site-section-card__toggle";
                    sectionToggle.type = "button";
                    sectionToggle.setAttribute("aria-controls", sectionBodyId);
                    sectionToggle.setAttribute("aria-expanded", String(sectionIndex === 0));
                    sectionNumber.className = "site-section-card__number";
                    sectionNumber.textContent = String(sectionIndex + 1).padStart(2, "0");
                    sectionIcon.className = "site-section-card__icon";
                    sectionIcon.appendChild(createIcon(definition.icon));
                    sectionSummary.className = "site-section-card__summary";
                    sectionLabel.textContent = definition.label;
                    sectionTitle.textContent = ownText(headingElement) || definition.label;
                    sectionMeta.textContent = itemNodes.length
                        ? `${itemNodes.length} ${itemNodes.length === 1 ? "عنصر" : "عناصر"}`
                        : "نص تعريفي";
                    sectionSummary.append(sectionLabel, sectionTitle, sectionMeta);
                    sectionToggle.append(sectionNumber, sectionIcon, sectionSummary, createIcon("fa-chevron-down"));
                    sectionBody.className = "site-section-card__body";
                    sectionBody.id = sectionBodyId;
                    sectionBody.hidden = sectionIndex !== 0;
                    mainFields.className = "site-section-card__main-fields";

                    if (kicker) {
                        mainFields.appendChild(createStructuredField({
                            label: "العبارة التعريفية",
                            element: kicker,
                            onChange: writeToEditor,
                        }));
                    }

                    if (headingElement) {
                        mainFields.appendChild(createStructuredField({
                            label: "عنوان القسم",
                            element: headingElement,
                            onChange: () => {
                                sectionTitle.textContent = ownText(headingElement) || definition.label;
                                writeToEditor();
                            },
                        }));
                    }

                    if (introText) {
                        mainFields.appendChild(createStructuredField({
                            label: "مقدمة القسم",
                            element: introText,
                            multiline: true,
                            onChange: writeToEditor,
                        }));
                    }

                    if (callToAction) {
                        const ctaFields = document.createElement("div");
                        ctaFields.className = "site-section-link-fields";
                        ctaFields.append(
                            createStructuredField({
                                label: "نص زر القسم",
                                element: callToAction,
                                onChange: writeToEditor,
                            }),
                            createStructuredField({
                                label: "رابط زر القسم",
                                element: callToAction,
                                attribute: "href",
                                direction: "ltr",
                                help: "مثال: /pages/about",
                                onChange: writeToEditor,
                            }),
                        );
                        mainFields.appendChild(ctaFields);
                    }

                    sectionBody.appendChild(mainFields);

                    if (itemNodes.length) {
                        const itemsHeading = document.createElement("div");
                        const itemsTitle = document.createElement("h4");
                        const itemsDescription = document.createElement("p");
                        const itemsList = document.createElement("div");

                        itemsHeading.className = "site-section-card__items-heading";
                        itemsTitle.textContent = sectionIndex === 5 ? "أسئلة هذا القسم" : "بطاقات هذا القسم";
                        itemsDescription.textContent = "افتح أي بطاقة لتعديل النص أو الصورة أو الرابط.";
                        itemsHeading.append(itemsTitle, itemsDescription);
                        itemsList.className = "site-section-card__items";
                        itemNodes.forEach((item, itemIndex) => {
                            itemsList.appendChild(createStructuredItem({
                                item,
                                index: itemIndex,
                                locale,
                                onChange: writeToEditor,
                            }));
                        });
                        sectionBody.append(itemsHeading, itemsList);
                    }

                    sectionToggle.addEventListener("click", () => {
                        const open = sectionToggle.getAttribute("aria-expanded") === "true";

                        if (!open) {
                            [...panel.querySelectorAll(".site-section-card")].forEach((otherCard) => {
                                if (otherCard === sectionCard) {
                                    return;
                                }

                                otherCard.querySelector(".site-section-card__toggle")?.setAttribute("aria-expanded", "false");
                                const otherBody = otherCard.querySelector(".site-section-card__body");
                                if (otherBody) {
                                    otherBody.hidden = true;
                                }
                            });
                        }

                        sectionToggle.setAttribute("aria-expanded", String(!open));
                        sectionBody.hidden = open;
                    });
                    sectionCard.append(sectionToggle, sectionBody);
                    panel.appendChild(sectionCard);
                });

                panels.appendChild(panel);
            });

            count.textContent = `${localeRoots[0].querySelectorAll(":scope > section").length} أقسام • ${Math.round(itemTotal / localeRoots.length)} بطاقة ومعلومة`;
            activateLocale(localeRoots.some((root) => root.getAttribute("data-home-locale") === activeLocale) ? activeLocale : "ar");
            updateIntegritySnapshot();
            return true;
        };

        toolbar.append(languageControls, count);
        shell.append(heading, toolbar, panels, status);
        editor.classList.add("site-section-editor__source");
        editor.setAttribute("aria-hidden", "true");
        editor.parentElement.insertBefore(shell, editor);

        const showLoadingState = (message) => {
            const loading = document.createElement("div");
            loading.className = "site-section-editor__loading";
            loading.append(createIcon("fa-spinner"), document.createTextNode(message));
            panels.replaceChildren(loading);
            languageControls.replaceChildren();
            count.textContent = "";
        };

        const loadPage = async (successMessage = "أصبحت الأقسام جاهزة للتعديل.") => {
            showLoadingState("جارٍ تحميل المحتوى الكامل للصفحة…");

            try {
                const response = await fetch(endpoint, {
                    headers: {
                        Accept: "application/json",
                    },
                    credentials: "same-origin",
                });

                if (!response.ok) {
                    throw new Error("تعذر تحميل محتوى الصفحة.");
                }

                pageSnapshot = await response.json();

                if (!render(pageSnapshot.content || "")) {
                    throw new Error("محتوى هذه الصفحة لا يستخدم بنية أقسام الصفحة الرئيسية.");
                }

                isDirty = false;
                status.classList.remove("is-dirty", "is-error");
                status.textContent = successMessage;
            } catch (error) {
                status.classList.remove("is-dirty");
                status.classList.add("is-error");
                status.textContent = error.message || "تعذر تحميل أقسام الصفحة.";
                showLoadingState("تعذر عرض الأقسام. أعد تحميل الصفحة وحاول مرة أخرى.");
            }
        };

        const savePage = async (event) => {
            event.preventDefault();
            event.stopImmediatePropagation();

            if (submitButton?.disabled || !documentModel || !pageSnapshot) {
                return;
            }

            const invalidControl = form.querySelector(":invalid");
            const invalidLocale = invalidControl?.closest(".site-section-editor__locale")?.dataset.locale;

            if (invalidLocale) {
                activateLocale(invalidLocale);
            }

            if (!form.reportValidity()) {
                status.classList.add("is-error");
                status.textContent = "راجع الحقول المعلّمة وصحح الرابط أو القيمة قبل الحفظ.";
                invalidControl?.focus();
                return;
            }

            const buttonLabel = submitButton?.querySelector("span") || submitButton;
            const previousLabel = buttonLabel?.textContent;
            const nextContent = documentModel.body.innerHTML;
            const arabicSections = documentModel.querySelectorAll('[data-home-locale="ar"] > section').length;
            const englishSections = documentModel.querySelectorAll('[data-home-locale="en"] > section').length;
            const headingCount = documentModel.querySelectorAll("h2").length;
            updateIntegritySnapshot();

            if (arabicSections !== 6 || englishSections !== 6 || headingCount !== 12) {
                status.classList.add("is-error");
                status.textContent = "توقف الحفظ لحماية الصفحة: بنية الأقسام غير مكتملة. أعد تحميل الصفحة وحاول مرة أخرى.";
                return;
            }

            const headers = {
                Accept: "application/json",
                "Content-Type": "application/json",
            };
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const xsrfCookie = document.cookie
                .split("; ")
                .find((item) => item.startsWith("XSRF-TOKEN="))
                ?.split("=")
                .slice(1)
                .join("=");

            if (csrfToken) {
                headers["X-CSRF-TOKEN"] = csrfToken;
            }

            if (xsrfCookie) {
                headers["X-XSRF-TOKEN"] = decodeURIComponent(xsrfCookie);
            }

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.setAttribute("aria-busy", "true");
            }

            if (buttonLabel) {
                buttonLabel.textContent = "جارٍ حفظ الصفحة…";
            }

            status.classList.remove("is-error");
            status.textContent = "جارٍ حفظ جميع حقول الصفحة وأقسامها…";

            try {
                const response = await fetch(endpoint, {
                    method: "PUT",
                    headers,
                    credentials: "same-origin",
                    body: JSON.stringify({
                        name: nameInput?.value || pageSnapshot.name,
                        title: titleInput?.value || pageSnapshot.title,
                        sub_title: subtitleInput?.value || null,
                        content: nextContent,
                    }),
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const validationMessage = Object.values(result.errors || {}).flat()[0];
                    throw new Error(validationMessage || result.message || "تعذر حفظ محتوى الصفحة.");
                }

                pageSnapshot = {
                    ...pageSnapshot,
                    name: nameInput?.value || pageSnapshot.name,
                    title: titleInput?.value || pageSnapshot.title,
                    sub_title: subtitleInput?.value || null,
                    content: nextContent,
                };
                isDirty = false;
                status.classList.remove("is-dirty", "is-error");
                status.textContent = "تم حفظ معلومات الصفحة وجميع الأقسام بنجاح.";
            } catch (error) {
                status.classList.add("is-error");
                status.textContent = error.message || "تعذر حفظ الصفحة. لم تُفقد تعديلاتك؛ صحح الخطأ وحاول مرة أخرى.";
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.removeAttribute("aria-busy");
                }

                if (buttonLabel) {
                    buttonLabel.textContent = previousLabel || "حفظ محتوى الصفحة";
                }
            }
        };

        form.addEventListener("click", (event) => {
            const clickedSubmit = event.target.closest('button[type="submit"]');

            if (clickedSubmit && form.contains(clickedSubmit)) {
                savePage(event);
            }
        }, true);
        form.addEventListener("submit", savePage, true);

        const restoreStructuredEditor = () => {
            window.setTimeout(() => {
                loadPage("تم التراجع عن تغييرات الأقسام واستعادة النسخة المحفوظة.");
            }, 150);
        };

        resetButton?.addEventListener("click", restoreStructuredEditor);
        form.addEventListener("reset", restoreStructuredEditor);
        window.addEventListener("beforeunload", (event) => {
            if (!isDirty || !shell.isConnected) {
                return;
            }

            event.preventDefault();
            event.returnValue = "";
        });
        loadPage();

        return shell;
    };

    const enhance = () => {
        scheduled = false;
        const routeMatch = window.location.pathname.match(routePattern);

        if (!routeMatch) {
            root?.classList.remove("site-page-editor-active");
            return;
        }

        const nameInput = document.querySelector('input[name="name"]');
        const titleInput = document.querySelector('input[name="title"]');
        const subtitleInput = document.querySelector('input[name="subTitle"]');
        const slugInput = document.querySelector('input[name="slug"]');
        const seoTitle = document.querySelector('input[name="seoMetaTitle"]');
        const seoDescription = document.querySelector('textarea[name="seoMetaDescription"]');
        const seoKeywords = document.querySelector('textarea[name="seoMetaKeywords"]');
        const editor = document.querySelector(".md-editor");

        if (!root || !nameInput || !titleInput || !subtitleInput || !slugInput || !editor) {
            return;
        }

        root.classList.add("site-page-editor-active");

        if (document.querySelector("[data-site-editor-shell]")) {
            return;
        }

        const mainForm = nameInput.closest("form");
        const seoForm = slugInput.closest("form");
        const mainSection = mainForm?.closest("section");
        const seoSection = seoForm?.closest("section");
        const layout = mainSection?.parentElement?.parentElement;
        const mainCard = mainForm?.parentElement?.parentElement;
        const seoCard = seoForm?.parentElement;

        if (!mainForm || !seoForm || !mainSection || !seoSection || !layout || !mainCard || !seoCard) {
            return;
        }

        layout.classList.add("site-page-editor-layout");
        mainSection.classList.add("site-page-editor-main");
        seoSection.classList.add("site-page-editor-sidebar");
        mainForm.classList.add("site-page-editor-content-form");
        seoForm.classList.add("site-page-editor-seo-form");
        mainCard.classList.add("site-editor-card", "site-editor-main-card");
        seoCard.classList.add("site-editor-card", "site-editor-seo-card");

        const contentFields = titleInput.closest(".mt-4.grid");
        const seoGrid = slugInput.closest(".grid");
        contentFields?.classList.add("site-editor-content-fields");
        seoGrid?.classList.add("site-editor-seo-grid");

        const pageHeaderTitle = [...document.querySelectorAll("h2")].find((heading) => {
            return heading.textContent.trim() === titleInput.value.trim();
        });

        if (pageHeaderTitle) {
            pageHeaderTitle.textContent = "تعديل صفحة الموقع";
        }

        const overview = document.createElement("section");
        const identity = document.createElement("div");
        const mark = document.createElement("span");
        const identityCopy = document.createElement("div");
        const eyebrow = document.createElement("span");
        const overviewTitle = document.createElement("h2");
        const overviewDescription = document.createElement("p");
        const meta = document.createElement("div");
        const status = document.createElement("span");
        const path = document.createElement("span");
        const actions = document.createElement("div");
        const isPublicInput = seoForm.querySelector('input[name="isPublic"]');
        const publicSwitch = isPublicInput?.parentElement?.querySelector('[role="switch"]');
        const publicLabelId = publicSwitch?.getAttribute("aria-labelledby");
        const publicLabel = publicLabelId ? document.getElementById(publicLabelId) : null;

        if (publicLabel) {
            publicLabel.textContent = "الصفحة منشورة";
        }

        overview.className = "site-editor-overview";
        overview.dataset.siteEditorShell = "true";
        identity.className = "site-editor-overview__identity";
        mark.className = "site-editor-overview__mark";
        identityCopy.className = "site-editor-overview__copy";
        eyebrow.className = "site-editor-overview__eyebrow";
        meta.className = "site-editor-overview__meta";
        status.className = "site-editor-status";
        path.className = "site-editor-path";
        actions.className = "site-editor-overview__actions";

        eyebrow.textContent = "إدارة الموقع العام";
        overviewTitle.textContent = `تحرير ${localizedPart(nameInput.value) || "الصفحة"}`;
        overviewDescription.textContent = "عدّل ما يراه الزائر، ثم احفظ كل قسم من مكانه. تعرض المعاينة النسخة المحفوظة حالياً.";
        mark.appendChild(createIcon("fa-pen-ruler"));

        const updateOverview = () => {
            const publicPath = pagePath(slugInput.value);
            status.textContent = isPublicInput?.checked ? "منشورة للزوار" : "غير منشورة";
            status.classList.toggle("is-private", !isPublicInput?.checked);
            path.textContent = `${window.location.origin}${publicPath}`;
            path.title = path.textContent;
            overviewTitle.textContent = `تحرير ${localizedPart(nameInput.value) || "الصفحة"}`;
            const previewLink = actions.querySelector("a");
            if (previewLink) {
                previewLink.href = publicPath;
            }
        };

        const previewAction = makeAction({
            label: "فتح الصفحة",
            icon: "fa-arrow-up-right-from-square",
            href: pagePath(slugInput.value),
        });
        const copyAction = makeAction({
            label: "نسخ الرابط",
            icon: "fa-link",
            secondary: true,
            onClick: async (event) => {
                const button = event.currentTarget;
                const originalLabel = "نسخ الرابط";

                try {
                    await navigator.clipboard.writeText(path.textContent);
                    button.lastChild.textContent = "تم نسخ الرابط";
                } catch {
                    button.lastChild.textContent = "تعذر النسخ";
                }

                window.setTimeout(() => {
                    button.lastChild.textContent = originalLabel;
                }, 1800);
            },
        });
        copyAction.classList.add("site-editor-copy-button");
        copyAction.setAttribute("aria-live", "polite");

        meta.append(status, path);
        identityCopy.append(eyebrow, overviewTitle, overviewDescription, meta);
        identity.append(mark, identityCopy);
        actions.append(previewAction, copyAction);
        overview.append(identity, actions);
        layout.parentElement.insertBefore(overview, layout);

        slugInput.addEventListener("input", updateOverview);
        nameInput.addEventListener("input", updateOverview);
        isPublicInput?.addEventListener("change", updateOverview);
        updateOverview();

        const cover = mainCard.firstElementChild;
        mainCard.insertBefore(createCardHeading({
            eyebrow: "واجهة الصفحة",
            title: "الصورة الرئيسية",
            description: "هذه الصورة تساعدك على ربط التحرير بالشكل الذي يراه زائر الموقع.",
            icon: "fa-image",
        }), cover);

        const formWrap = mainForm.parentElement;
        mainCard.insertBefore(createCardHeading({
            eyebrow: "النص والمحتوى",
            title: "معلومات الصفحة",
            description: "اسم رابط التنقل والعنوان والنص الذي يظهر في واجهة الموقع.",
            icon: "fa-align-right",
        }), formWrap);

        seoCard.insertBefore(createCardHeading({
            eyebrow: "الظهور والوصول",
            title: "النشر ومحركات البحث",
            description: "تحكم في ظهور الصفحة والرابط وبيانات نتائج البحث.",
            icon: "fa-magnifying-glass",
        }), seoCard.firstElementChild);

        const ogCard = [...seoSection.querySelectorAll(":scope > div > div")].find((card) => {
            return card !== seoCard && card.textContent.includes("OG");
        });
        ogCard?.classList.add("site-editor-og-card");

        const titlePreview = document.createElement("div");
        const previewImage = document.createElement("img");
        const previewCopy = document.createElement("div");
        const previewLabel = document.createElement("span");
        const previewTitle = document.createElement("h3");
        const previewSubtitle = document.createElement("p");

        titlePreview.className = "site-editor-title-preview";
        previewImage.className = "site-editor-title-preview__media";
        previewImage.src = "/images/school/home-hero.webp";
        previewImage.alt = "معاينة الصورة الرئيسية للصفحة";
        previewCopy.className = "site-editor-title-preview__copy";
        previewLabel.className = "site-editor-title-preview__label";
        previewLabel.textContent = "معاينة العنوان في الواجهة";

        const updateTitlePreview = () => {
            previewTitle.textContent = localizedPart(titleInput.value) || "عنوان الصفحة";
            previewSubtitle.textContent = localizedPart(subtitleInput.value) || "العنوان الفرعي للصفحة";
        };

        previewCopy.append(previewLabel, previewTitle, previewSubtitle);
        titlePreview.append(previewImage, previewCopy);
        mainForm.prepend(titlePreview);
        titleInput.addEventListener("input", updateTitlePreview);
        subtitleInput.addEventListener("input", updateTitlePreview);
        updateTitlePreview();

        addFieldHelp(nameInput, "اكتب العربية أولاً ثم || ثم الإنجليزية. هذا الاسم يظهر في شريط تنقل الموقع.", "site-page-name-help");
        addFieldHelp(titleInput, "العنوان الرئيسي للصفحة. استخدم || للفصل بين النص العربي والإنجليزي.", "site-page-title-help");
        addFieldHelp(subtitleInput, "وصف مختصر يدعم العنوان ويظهر في مقدمة الصفحة.", "site-page-subtitle-help");
        addCounter(titleInput, 90, "عنوان واضح ومباشر");
        addCounter(subtitleInput, 170, "وصف موجز");

        const slugField = slugInput.closest("div.mt-1")?.parentElement || slugInput.parentElement;
        const urlPreview = document.createElement("span");
        const updateUrlPreview = () => {
            urlPreview.textContent = `${window.location.origin}${pagePath(slugInput.value)}`;
        };
        urlPreview.className = "site-editor-url-preview";
        urlPreview.dataset.siteEditorUrl = "true";
        slugField.appendChild(urlPreview);
        slugInput.addEventListener("input", updateUrlPreview);
        updateUrlPreview();

        if (seoTitle) addCounter(seoTitle, 60, "العنوان المقترح لنتائج البحث");
        if (seoDescription) addCounter(seoDescription, 160, "الوصف المقترح لنتائج البحث");
        if (seoKeywords) addCounter(seoKeywords, 180, "افصل الكلمات بفاصلة");

        const structuredEditor = createStructuredSectionEditor({
            editor,
            form: mainForm,
            uuid: routeMatch[1],
        });

        if (!structuredEditor) {
            const editorField = editor.parentElement;
            const advanced = document.createElement("div");
            const toggle = document.createElement("button");
            const toggleText = document.createElement("span");
            const editorId = editor.id || "site-page-content-editor";
            const storageKey = `site-page-editor.advanced.${routeMatch[1]}`;
            const setEditorState = (open) => {
                advanced.classList.toggle("is-open", open);
                toggle.setAttribute("aria-expanded", String(open));
                toggleText.textContent = open ? "إخفاء محرر المحتوى المتقدم" : "محرر المحتوى المتقدم";
                localStorage.setItem(storageKey, open ? "1" : "0");
            };

            advanced.className = "site-editor-advanced";
            toggle.className = "site-editor-advanced__toggle";
            toggle.type = "button";
            toggle.setAttribute("aria-controls", editorId);
            toggleText.textContent = "محرر المحتوى المتقدم";
            toggle.append(toggleText, createIcon("fa-chevron-down"));
            editor.id = editorId;
            editorField.insertBefore(advanced, editor);
            advanced.append(toggle, editor);
            toggle.addEventListener("click", () => setEditorState(!advanced.classList.contains("is-open")));
            setEditorState(localStorage.getItem(storageKey) === "1");
        }

        renameFormActions(mainForm, "حفظ محتوى الصفحة");
        renameFormActions(seoForm, "حفظ إعدادات النشر");

        const previewButton = document.querySelector('button i.fa-eye')?.closest("button");
        if (previewButton) {
            previewButton.setAttribute("aria-label", "معاينة الصفحة المحفوظة");
        }
    };

    const scheduleEnhancement = () => {
        if (scheduled) {
            return;
        }

        scheduled = true;
        window.requestAnimationFrame(enhance);
    };

    new MutationObserver(scheduleEnhancement).observe(document.documentElement, {
        childList: true,
        subtree: true,
    });

    window.addEventListener("popstate", scheduleEnhancement);
    scheduleEnhancement();
})();
