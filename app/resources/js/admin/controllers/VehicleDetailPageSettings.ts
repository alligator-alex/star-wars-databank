export * from "../global/index";

export default class VehicleListPageSettings extends window.Controller {
    private imageOffsetXTarget: HTMLInputElement;
    private imageOffsetYTarget: HTMLInputElement;
    private imageMaxHeightTarget: HTMLInputElement;

    private imageOffsetXLabelElement: HTMLLabelElement;
    private imageOffsetYLabelElement: HTMLLabelElement;
    private imageMaxHeightLabelElement: HTMLLabelElement;

    private imageOffsetXLabel: string;
    private imageOffsetYLabel: string;
    private imageMaxHeightLabel: string;

    private imageContainerElement: HTMLPictureElement;
    private imageElement: HTMLImageElement;

    public static targets: string[] = [
        "imageOffsetX",
        "imageOffsetY",
        "imageMaxHeight",
    ];

    public connect(): void {
        this.imageOffsetXLabelElement = this.imageOffsetXTarget
            .parentElement
            .parentElement
            .querySelector("label");

        this.imageOffsetXLabel = this.imageOffsetXLabelElement.textContent;

        this.imageOffsetYLabelElement = this.imageOffsetYTarget
            .parentElement
            .parentElement
            .querySelector("label");

        this.imageOffsetYLabel = this.imageOffsetYLabelElement.textContent;

        this.imageMaxHeightLabelElement = this.imageMaxHeightTarget
            .parentElement
            .parentElement
            .querySelector("label");

        this.imageMaxHeightLabel = this.imageMaxHeightLabelElement.textContent;

        this.updateImageOffsetLabels();
        this.updateImageMaxHeightLabels();
    }

    private initIframeElements(): void {
        if ((typeof this.imageContainerElement !== "undefined") && (typeof this.imageElement !== "undefined")) {
            return
        }

        const iframe: HTMLIFrameElement = document.querySelector(".js-detail-page-preview");
        const iframeDocument: Document = iframe.contentWindow.document;

        this.imageContainerElement = iframeDocument.querySelector(".vehicle-detail__image");
        this.imageElement = this.imageContainerElement.querySelector("img");
    }

    public changeImageOffset(): void {
        this.initIframeElements();

        this.imageContainerElement.style.left = parseInt(this.imageOffsetXTarget.value) + "%";
        this.imageElement.style.top = parseInt(this.imageOffsetYTarget.value) + "%";

        this.updateImageOffsetLabels();
    }

    private updateImageOffsetLabels(): void {
        const offsetX: string = this.imageOffsetXTarget.value;
        const offsetY: string = this.imageOffsetYTarget.value;

        this.imageOffsetXLabelElement.textContent = this.imageOffsetXLabel + " (" + offsetX + "%)";
        this.imageOffsetYLabelElement.textContent = this.imageOffsetYLabel + " (" + offsetY + "%)";
    }

    public changeImageMaxHeight(): void {
        this.initIframeElements();

        this.imageElement.style.maxHeight = parseInt(this.imageMaxHeightTarget.value) + "vh";

        this.updateImageMaxHeightLabels();
    }

    private updateImageMaxHeightLabels(): void {
        const maxHeight: string = this.imageMaxHeightTarget.value;

        this.imageMaxHeightLabelElement.textContent = this.imageMaxHeightLabel + " (" + maxHeight + "vh)";
    }
}
