export * from "../global/index";

import Masonry from "masonry-layout";
import TomSelect from "tom-select";
import {TomInput} from "tom-select/dist/types/types";

export type VehicleListResponse = {
    html: {
        list: string,
        pagination: string
    };
};

export default class VehicleListPage {
    private filter: HTMLElement;
    private filterToggle: HTMLElement;
    private list: HTMLElement;
    private listMasonry: Masonry;
    private pagination: HTMLElement;

    constructor() {
        this.init();
    }

    private init(): void {
        this.list = document.querySelector(".js-list-content");

        if (this.list === null) {
            return;
        }

        this.filter = document.querySelector(".js-vehicle-filter");
        this.filterToggle = this.filter.querySelector(".js-filter-toggle");
        this.pagination = document.querySelector(".js-pagination-content");

        if (window.screen.width > 1024) {
            this.initMasonry();
        }

        this.initFilter();
        this.attachPaginationEvents();
    }

    private initMasonry(): void {
        this.listMasonry = new Masonry(this.list);

        document.addEventListener("masonry.reload", (): void => {
            this.listMasonry.destroy();
            this.listMasonry = new Masonry(this.list);
        });
    }

    private initFilter(): void {
        if (this.filter === null) {
            return;
        }

        const selectSettings = {
            allowEmptyOption: true,
            preload: "focus",
            plugins: [
                "remove_button",
                "clear_button",
                "checkbox_options"
            ]
        };

        this.filter.querySelectorAll(".js-filter-input").forEach((element: HTMLElement): void => {
            if (element.nodeName === "SELECT") {
                const select: TomSelect = new TomSelect(element as TomInput, selectSettings);

                this.collapseMultiselectItems(select);

                select.on("item_add item_remove", (): void => this.collapseMultiselectItems(select));
                select.on("change", (): void => this.loadPageContent(1, true));

                return;
            }

            element.addEventListener("change", (): void => this.loadPageContent(1, true));
        });
    }

    private collapseMultiselectItems(select: TomSelect): void {
        let dummyItem: HTMLElement = select.wrapper.querySelector("[data-ts-item-dummy]");

        if (select.items.length <= 1) {
            if (dummyItem) {
                dummyItem.remove();
            }

            select.wrapper.querySelectorAll('[data-ts-item]').forEach((element: HTMLElement): void => {
                element.style.display = "";
            });

            return;
        }

        select.wrapper.querySelectorAll('[data-ts-item]').forEach((element: HTMLElement, key: number): void => {
            if (key === 0) {
                return;
            }

            element.style.display = "none";
        });

        if (dummyItem) {
            dummyItem.textContent = (select.items.length - 1) + "+";
            return;
        }

        dummyItem = document.createElement("div");

        dummyItem.classList.add("item", "item--dummy")

        dummyItem.dataset.tsItemDummy = "1";

        dummyItem.textContent = (select.items.length - 1) + "+";

        select.control.insertBefore(dummyItem, select.control_input);
    }

    private attachPaginationEvents(): void {
        if (this.pagination === null) {
            return;
        }

        this.pagination.querySelectorAll("a").forEach((element: HTMLElement): void => {
            element.addEventListener("click", (event: Event) => this.navigateOtherPage(event, element));
        });
    }

    private detachPaginationEvents(): void {
        if (this.pagination === null) {
            return;
        }

        this.pagination.querySelectorAll("a").forEach((element: HTMLElement): void => {
            element.removeEventListener("click", (event: Event) => this.navigateOtherPage(event, element));
        });
    }

    private navigateOtherPage(event: Event, element: HTMLElement): void {
        event.preventDefault();
        this.loadPageContent(parseInt(element.dataset.pageNum))
    }

    private loadPageContent(page?: number, reload?: boolean): void {
        const loader: HTMLElement = document.querySelector(".js-loader");

        loader.classList.add("is-loading");

        const form: HTMLFormElement = this.filter as HTMLFormElement;

        let url: string = form.getAttribute("action");
        let displayUrl: string = url;

        let filterParams: string = new URLSearchParams(new FormData(form) as unknown as Record<string, string>).toString();

        if (filterParams !== "") {
            url += "?" + filterParams;
            displayUrl = url;

            this.filterToggle.dataset.appliedFiltersCount = filterParams.split('&').length.toString();
        } else {
            this.filterToggle.dataset.appliedFiltersCount = '0';
        }

        if (page > 1) {
            if (filterParams !== "") {
                url += "&";
            } else {
                url += "?";
            }

            url += "page=" + page;
        }

        window.axios.get(url).then(response => {
            this.detachPaginationEvents();

            response.data = response.data as VehicleListResponse;

            if (reload) {
                this.list.innerHTML = response.data.html.list;
            } else {
                this.list.insertAdjacentHTML("beforeend", response.data.html.list);
            }

            this.pagination.innerHTML = response.data.html.pagination;

            //this.listMasonry = new Masonry(this.list);
            document.dispatchEvent(new Event("masonry.reload"));

            this.attachPaginationEvents();

            history.pushState({}, "", displayUrl);
        }).catch(e => {
            console.error(e);
        }).then((): void => {
            loader.classList.remove("is-loading");
        });
    }
}
