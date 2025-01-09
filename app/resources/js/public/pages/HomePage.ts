export default class HomePage {
    private intro: HTMLElement;
    private skipIntoBtn: HTMLButtonElement;

    constructor() {
        this.init();
    }

    private init(): void {
        this.intro = document.querySelector(".js-intro");

        if (this.intro === null) {
            return;
        }

        this.skipIntoBtn = this.intro.querySelector(".js-skip-intro");

        this.skipIntoBtn.addEventListener("click", (): void => {
            this.intro.style.display = "none";
        });
    }
}
