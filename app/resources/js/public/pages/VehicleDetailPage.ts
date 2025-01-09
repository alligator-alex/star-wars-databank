import Flickity from "flickity";
import "flickity/dist/flickity.min.css";

export default class VehicleDetailPage {
    private sliderElement: HTMLElement;

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
            on: {
                ready: (): void => {
                    this.sliderElement.classList.add('is-start');
                }
            }
        });

        slider.on('change', (index: number): void => {
            this.sliderElement.classList.remove('is-start', 'is-end');

            if (index === 0) {
                this.sliderElement.classList.add('is-start');
                return;
            }

            if (index === (slider.slides.length - 1)) {
                this.sliderElement.classList.add('is-end');
                return;
            }
        });
    }
}
