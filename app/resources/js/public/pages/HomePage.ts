export default class HomePage {
    private intro: HTMLElement;
    private skipIntoBtn: HTMLButtonElement;
    private appearances: NodeListOf<HTMLElement>;
    private mediaTypeSwitch: NodeListOf<HTMLInputElement>;

    constructor() {
        this.init();
    }

    private init(): void {
        this.initMediaTypeSwitch();

        this.intro = document.querySelector(".js-intro");

        if (this.intro === null) {
            return;
        }

        this.skipIntoBtn = this.intro.querySelector(".js-skip-intro");

        this.skipIntoBtn.addEventListener("click", (): void => {
            this.intro.style.display = "none";
        });
    }

    private initMediaTypeSwitch(): void {
        this.mediaTypeSwitch = document.querySelectorAll(".js-media-type-switch");
        this.appearances = document.querySelectorAll(".js-appearance");

        this.mediaTypeSwitch.forEach((element: HTMLInputElement): void => {
            element.addEventListener("change", (): void => this.showTargetAppearances(element.value));
        });
    }

    private showTargetAppearances(mediaType: string): void {
        if (mediaType === "0") {
            this.appearances.forEach((appearance: HTMLElement): void => appearance.classList.remove("is-hidden"));
            window.dispatchEvent(new Event("scroll"));
            return;
        }

        this.appearances.forEach((appearance: HTMLElement): void => {
            if (appearance.dataset.mediaType !== mediaType) {
                appearance.classList.add("is-hidden");
                return;
            }

            appearance.classList.remove("is-hidden");
        });

        window.dispatchEvent(new Event("scroll"));
    }
}
