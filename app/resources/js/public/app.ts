import WOW from "wow.js";
import "wow.js/css/libs/animate.css";

import HomePage from "./pages/HomePage";
import EntityIndexPage from "./pages/EntityIndexPage";
import EntityDetailPage from "./pages/EntityDetailPage";

document.addEventListener("DOMContentLoaded", function (): void {
    new WOW().init();

    new HomePage();
    new EntityIndexPage();
    new EntityDetailPage();

    const cookieModal = document.querySelector('.js-cookie-modal');
    if (cookieModal === null) {
        return;
    }

    const cookieConsent = cookieModal.querySelector('.js-cookie-consent');
    if (cookieConsent === null) {
        return;
    }

    cookieConsent.addEventListener("click", (event: Event) => {
        event.preventDefault();

        const date = new Date();

        date.setTime(date.getTime() + 60 * 60 * 24 * 365 * 1000);

        document.cookie = "cookie_consent=Y; path=/; expires=" + date.toUTCString();

        cookieModal.remove();
    });
});
