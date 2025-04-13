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
});
