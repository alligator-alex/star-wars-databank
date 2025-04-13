import TomSelect from "tom-select";

interface DataStorage {
    set(selector: string): void;
    get(selector: string): string;
}

export default class ExtendedSelect extends window.Controller {
    private choices: TomSelect;
    private element: HTMLElement;
    private data: DataStorage;

    connect() {
        if (document.documentElement.hasAttribute('data-turbo-preview')) {
            return;
        }

        const select = this.element.querySelector('select');
        const plugins = [
            'change_listener',
            'drag_drop',
        ];

        if (select.hasAttribute('multiple')) {
            plugins.push('remove_button');
            plugins.push('clear_button');
        }

        this.choices = new TomSelect(select, {
            create: this.data.get('allow-add') === 'true',
            allowEmptyOption: true,
            maxOptions: null,
            placeholder: select.getAttribute('placeholder') === 'false' ? '' : select.getAttribute('placeholder'),
            preload: true,
            plugins,
            maxItems: parseInt(select.getAttribute('maximumSelectionLength'))
                || (select.hasAttribute('multiple') ? null : 1),
            render: {
                option_create: (data, escape) => `<div class="create">${this.data.get('message-add')} <strong>${escape(data.input)}</strong>&hellip;</div>`,
                no_results: () => `<div class="no-results">${this.data.get('message-not-found')}</div>`,
            },
            onDelete: () => !! this.data.get('allow-empty'),
            onItemAdd: function() {
                this.setTextboxValue('');
                this.refreshOptions(false);
            }
        });
    }

    /**
     *
     */
    disconnect() {
        this.choices?.destroy();
    }
}
