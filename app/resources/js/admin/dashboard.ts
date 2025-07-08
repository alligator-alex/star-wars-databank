export * from "./global/index";

import ExtendedSelectController from "./controllers/ExtendedSelect";
import EntityIndexPageSettingsController from "./controllers/EntityIndexPageSettings";
import EntityDetailPageSettingsController from "./controllers/EntityDetailPageSettings";

window.application.register("extended-select", ExtendedSelectController as RegistrationOptions);
window.application.register("entity-index-page-settings", EntityIndexPageSettingsController as RegistrationOptions);
window.application.register("entity-detail-page-settings", EntityDetailPageSettingsController as RegistrationOptions);
