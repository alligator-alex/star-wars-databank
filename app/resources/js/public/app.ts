import WOW from "wow.js";
import "wow.js/css/libs/animate.css";

import HomePage from "./pages/HomePage";
import VehicleListPage from "./pages/VehicleListPage";
import VehicleDetailPage from "./pages/VehicleDetailPage";

document.addEventListener("DOMContentLoaded", function (): void {
    new WOW().init();

    new HomePage();
    new VehicleListPage();
    new VehicleDetailPage();

    const cookieModal = document.querySelector('.js-cookie-modal');
    const cookieConsent = cookieModal.querySelector('.js-cookie-consent');

    cookieConsent.addEventListener("click", (event: Event) => {
        event.preventDefault();

        const date = new Date();

        date.setTime(date.getTime() + 60 * 60 * 24 * 365 * 1000);

        document.cookie = "cookie_consent=Y; path=/; expires=" + date.toUTCString();

        cookieModal.remove();
    });
});
