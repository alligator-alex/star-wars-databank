import ApplicationController from "../../../../vendor/orchid/platform/resources/js/controllers/application_controller.js";

declare global {
    interface Window {
        application: ServiceWorkerContainer,
        Controller: typeof ApplicationController,
    }
}
