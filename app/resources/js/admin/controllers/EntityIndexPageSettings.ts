export * from "../global/index";

export default class EntityIndexPageSettings extends window.Controller {
    private cardLargeToggleTarget: HTMLInputElement;
    private imageCoverToggleTarget: HTMLInputElement;
    private imageScaleToggleTarget: HTMLInputElement;
    private imageScaleTarget: HTMLInputElement;
    private imageOffsetToggleTarget: HTMLInputElement;
    private imageOffsetXTarget: HTMLInputElement;
    private imageOffsetYTarget: HTMLInputElement;

    private iframeDocument: Document;

    private itemElement: HTMLElement;
    private imageContainerElement: HTMLElement;
    private imageElement: HTMLImageElement;
    private imageScaleLabelElement: HTMLElement;
    private imageOffsetXLabelElement: HTMLElement;
    private imageOffsetYLabelElement: HTMLElement;

    private imageScaleLabel: string;
    private imageOffsetXLabel: string;
    private imageOffsetYLabel: string;

    private itemClassName: string = "entity-list__item";
    private imageClassName: string = "entity-list__image";

    public static targets: string[] = [
        "cardLargeToggle",
        "imageCoverToggle",
        "imageScaleToggle",
        "imageScale",
        "imageOffsetToggle",
        "imageOffsetX",
        "imageOffsetY",
    ];

    public connect(): void {
        this.imageScaleLabelElement = this.imageScaleTarget
            .parentElement
            .parentElement
            .querySelector("label");

        this.imageScaleLabel = this.imageScaleLabelElement.textContent;

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

        this.updateImageScaleLabel();
        this.updateImageOffsetLabels();
    }

    private initIframeElements(): void {
        if ((typeof this.imageContainerElement !== "undefined") && (typeof this.imageElement !== "undefined")) {
            return
        }

        const iframe: HTMLIFrameElement = document.querySelector(".js-index-page-preview");
        this.iframeDocument = iframe.contentWindow.document;

        this.itemElement = this.iframeDocument.querySelector("." + this.itemClassName + "[data-preview-target]");
        this.imageContainerElement = this.itemElement.querySelector("." + this.imageClassName);
        this.imageElement = this.imageContainerElement.querySelector("img");
    }

    public toggleCardLarge(): void {
        this.initIframeElements();

        if (this.cardLargeToggleTarget.checked) {
            this.itemElement.classList.add(this.itemClassName + "--large");
        } else {
            this.itemElement.classList.remove(this.itemClassName + "--large");
        }

        this.iframeDocument.dispatchEvent(new Event("masonry.reload"));
    }

    public toggleImageCover(): void {
        this.initIframeElements();

        if (this.imageCoverToggleTarget.checked) {
            this.imageContainerElement.classList.add(this.imageClassName + "--covered");
            return;
        }

        this.imageContainerElement.classList.remove(this.imageClassName + "--covered");
    }

    public toggleImageScale(): void {
        this.initIframeElements();

        this.changeImageOffset();

        if (this.imageScaleToggleTarget.checked) {
            this.imageScaleTarget.disabled = false;
            return;
        }

        this.imageScaleTarget.disabled = true;
        this.imageScaleTarget.value = "1";
        this.imageScaleTarget.dispatchEvent(new Event("change"));

        this.imageElement.style.removeProperty("height");
        this.imageElement.style.removeProperty("width");

        this.updateImageScaleLabel();
    }

    public changeImageScale(): void {
        this.initIframeElements();

        const value: number = Math.round(parseFloat(this.imageScaleTarget.value) * 100);

        this.imageElement.style.height = value + "%";
        this.imageElement.style.width = value + "%";

        this.updateImageScaleLabel();
    }

    public toggleImageOffset(): void {
        this.initIframeElements();

        if (this.imageOffsetToggleTarget.checked) {
            this.imageOffsetXTarget.disabled = false;
            this.imageOffsetYTarget.disabled = false;
            return;
        }

        this.imageOffsetXTarget.value = "0";
        this.imageOffsetXTarget.disabled = true;
        this.imageOffsetYTarget.value = "0";
        this.imageOffsetYTarget.disabled = true;

        this.imageElement.style.objectPosition = "";
        this.imageElement.style.transform = "";

        this.updateImageOffsetLabels();
    }

    public changeImageOffset(): void {
        if (this.imageScaleToggleTarget.checked) {
            this.changeImageOffsetByTransform();
            return;
        }

        this.changeImageOffsetByObjectPosition();
    }

    private changeImageOffsetByTransform(): void {
        if (this.imageElement.style.hasOwnProperty("objectPosition")) {
            this.imageElement.style.objectPosition = "";
        }

        const defaultOffset: number = -50;

        const offsetX: number = defaultOffset - parseInt(this.imageOffsetXTarget.value);
        const offsetY: number = defaultOffset - parseInt(this.imageOffsetYTarget.value);

        this.imageElement.style.transform = "translate(" + offsetX + "%, " + offsetY + "%)";

        this.updateImageOffsetLabels();
    }

    private changeImageOffsetByObjectPosition(): void {
        if (this.imageElement.style.hasOwnProperty("transform")) {
            this.imageElement.style.transform = "";
        }

        const multiplier: number = -5;

        const offsetX: number = multiplier * parseInt(this.imageOffsetXTarget.value);
        const offsetY: number = multiplier * parseInt(this.imageOffsetYTarget.value);

        this.imageElement.style.objectPosition = "calc(50% + " + offsetX + "px)"
            + " calc(50% + " + offsetY + "px)";

        this.updateImageOffsetLabels();
    }

    private updateImageScaleLabel(): void {
        this.imageScaleLabelElement.textContent = this.imageScaleLabel + " (x" + this.imageScaleTarget.value + ")";
    }

    private updateImageOffsetLabels(): void {
        let offsetX: string = this.imageOffsetXTarget.value;
        if (parseInt(offsetX) > 0) {
            offsetX = "+" + offsetX;
        }

        let offsetY: string = this.imageOffsetYTarget.value;
        if (parseInt(offsetY) > 0) {
            offsetY = "+" + offsetY;
        }

        this.imageOffsetXLabelElement.textContent = this.imageOffsetXLabel + " (" + offsetX + ")";
        this.imageOffsetYLabelElement.textContent = this.imageOffsetYLabel + " (" + offsetY + ")";
    }
}
