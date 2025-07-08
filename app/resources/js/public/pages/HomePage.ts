export default class HomePage {
    private intro: HTMLElement;
    private skipIntoBtn: HTMLButtonElement;
    private explorer: HTMLElement;
    private appearances: NodeListOf<HTMLElement>;
    private mediaTypeSwitch: NodeListOf<HTMLInputElement>;

    constructor() {
        this.init();
    }

    private init(): void {
        this.initExplorer();
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

    private initExplorer(): void {
        this.explorer = document.querySelector(".js-explorer");
        if (!this.explorer) {
            return;
        }

        const defaultInterval = 2000;
        const hoverInterval = 500;

        const items = this.explorer.querySelectorAll(".js-explorer-item");

        items.forEach((item: HTMLElement, itemIndex: number): void => {
            const cards = item.querySelectorAll(".js-explorer-card");

            let loopIndex = 0;
            let previousIndex = cards.length - 1;
            let interval = defaultInterval;

            let loopIntervalId: number | undefined;

            const loop = () => {
                cards.forEach((card: HTMLElement, cardIndex: number): void => {
                    card.classList.toggle("is-active", cardIndex === loopIndex);
                    card.classList.toggle("is-previous-active", cardIndex === previousIndex);
                });

                previousIndex = loopIndex;
                loopIndex = (loopIndex + 1) % cards.length;
            };

            const startLoop = () => {
                if (loopIntervalId !== undefined) {
                    clearInterval(loopIntervalId);
                }

                loop();

                loopIntervalId = setInterval(loop, interval);
            };

            setTimeout(() => {
                startLoop();
            }, (defaultInterval / items.length) * itemIndex);

            item.addEventListener("mouseenter", (): void => {
                interval = hoverInterval;
                startLoop();
            });

            item.addEventListener("mouseleave", (): void => {
                interval = defaultInterval;
                startLoop();
            });
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
