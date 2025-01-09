export * from "./global/index";

import ExtendedSelectController from "./controllers/ExtendedSelect";
import VehicleListPageSettingsController from "./controllers/VehicleListPageSettings";
import VehicleDetailPageSettingsController from "./controllers/VehicleDetailPageSettings";

window.application.register("extended-select", ExtendedSelectController as RegistrationOptions);
window.application.register("vehicle-list-page-settings", VehicleListPageSettingsController as RegistrationOptions);
window.application.register("vehicle-detail-page-settings", VehicleDetailPageSettingsController as RegistrationOptions);
