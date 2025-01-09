export * from "./global/index";

import WOW from "wow.js";
import "wow.js/css/libs/animate.css";
import axios from "axios";

import HomePage from "./pages/HomePage";
import VehicleListPage from "./pages/VehicleListPage";
import VehicleDetailPage from "./pages/VehicleDetailPage";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

document.addEventListener("DOMContentLoaded", function (): void {
    new WOW().init();

    new HomePage();
    new VehicleListPage();
    new VehicleDetailPage();
});
