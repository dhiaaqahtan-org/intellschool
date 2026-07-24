(() => {
    const createRegistrationPath = /^\/app\/student\/registrations\/create\/?$/;
    const pageTitleSelector = [
        '[aria-current="page"]',
        'h2.text-xl.font-bold.leading-7',
    ].join(',');

    const updateCreateRegistrationTitle = () => {
        if (!createRegistrationPath.test(window.location.pathname)) {
            return;
        }

        const root = document.getElementById('root');
        const title = root?.dataset.studentRegistrationCreateTitle?.trim();

        if (!title) {
            return;
        }

        document.querySelectorAll(pageTitleSelector).forEach((element) => {
            if (element.textContent.trim() !== title) {
                element.textContent = title;
            }
        });
    };

    let updateScheduled = false;

    const scheduleTitleUpdate = () => {
        if (updateScheduled) {
            return;
        }

        updateScheduled = true;
        window.requestAnimationFrame(() => {
            updateScheduled = false;
            updateCreateRegistrationTitle();
        });
    };

    new MutationObserver(scheduleTitleUpdate).observe(document.body, {
        childList: true,
        subtree: true,
    });

    window.addEventListener('popstate', scheduleTitleUpdate);
    scheduleTitleUpdate();
})();
