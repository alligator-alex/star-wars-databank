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
        this.filterToggle = document.querySelector(".js-filter-toggle");
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

        this.filterToggle.addEventListener("click", (event: Event) => {
            event.preventDefault();

            this.filterToggle.classList.toggle("is-active");
            this.filter.classList.toggle("is-active");
        });

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
                element.style.display = "";
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

        const url = new URL(form.getAttribute("action"), window.location.origin);

        const filterParams: URLSearchParams = new URLSearchParams(
            new FormData(form) as unknown as Record<string, string>
        );

        if (filterParams.size > 0) {
            filterParams.forEach((value: string, key: string) => {
                url.searchParams.append(key, value);
            });
        }

        let uniqueFilters: Array<string> = [];
        for (const key of url.searchParams.keys()) {
            if (uniqueFilters.includes(key)) {
                continue;
            }

            uniqueFilters.push(key);
        }

        this.filterToggle.querySelector(".js-filter-toggle-label").dataset.appliedFiltersCount = uniqueFilters.length;

        if (page) {
            url.searchParams.append("page", page.toString());
        }

        try {
            fetch(url, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
            }).then((response): Promise<any> => {
                return response.json();
            }).then((jsonResponse): void => {
                this.detachPaginationEvents();

                jsonResponse = jsonResponse as VehicleListResponse;

                if (reload) {
                    this.list.innerHTML = jsonResponse.html.list;
                } else {
                    this.list.insertAdjacentHTML("beforeend", jsonResponse.html.list);
                }

                this.pagination.innerHTML = jsonResponse.html.pagination;

                document.dispatchEvent(new Event("masonry.reload"));

                this.attachPaginationEvents();

                url.searchParams.delete("page");
                history.pushState({}, "", url);
            }).then((): void => {
                loader.classList.remove("is-loading");
            });
        } catch (error) {
            console.error(error.message);
        }
    }
}
