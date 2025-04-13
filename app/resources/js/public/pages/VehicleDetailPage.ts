import Flickity from "flickity";
import "flickity/dist/flickity.min.css";

export default class VehicleDetailPage {
    private sliderElement: HTMLElement;
    private isStart: boolean = false;
    private isEnd: boolean = false;

    constructor() {
        this.init();
    }

    private init(): void {
        this.sliderElement = document.querySelector('.js-appearances-slider');

        if ((this.sliderElement !== null) && (this.sliderElement.childElementCount > 3)) {
            this.initSlider();
        }
    }

    private initSlider(): void {
        const slider = new Flickity(this.sliderElement, {
            contain: true,
            groupCells: 2,
            prevNextButtons: false,
            on: {
                ready: (): void => {
                    this.isStart = true;

                    this.sliderElement.classList.add("is-start");
                }
            }
        });

        slider.on("change", (index: number): void => {
            this.isStart = false;
            this.isEnd = false;

            this.sliderElement.classList.remove("is-start", "is-end");

            if (index === 0) {
                this.sliderElement.classList.add("is-start");
            } else if (index === (slider.slides.length - 1)) {
                this.sliderElement.classList.add("is-end");
            }
        });

        slider.on("settle", (index: number): void => {
            if (index === 0) {
                this.isStart = true;
            } else if (index === (slider.slides.length - 1)) {
                this.isEnd = false;
            }
        });

        this.sliderElement.addEventListener("wheel", (event: WheelEvent) => {
            if (!this.isStart && !this.isEnd) {
                event.preventDefault();
            }

            if (event.deltaY > 0) {
                slider.next();
            } else if (event.deltaY < 0) {
                slider.previous();
            }
        });
    }
}
