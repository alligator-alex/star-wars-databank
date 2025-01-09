@component($typeForm, get_defined_vars())
    <div data-controller="extended-select"
        data-extended-select-placeholder="{{$attributes['placeholder'] ?? ''}}"
        data-extended-select-allow-empty="{{ $allowEmpty }}"
        data-extended-select-message-notfound="{{ __('No results found') }}"
        data-extended-select-allow-add="{{ var_export($allowAdd, true) }}"
        data-extended-select-message-add="{{ __('Add') }}"
    >
        <select {{ $attributes }}>
            @foreach($options as $key => $option)
                @if (is_string($option))
                    <option value="{{$key}}"
                            @isset($value)
                                @if (is_array($value) && in_array($key, $value)) selected
                            @elseif (isset($value[$key]) && $value[$key] == $option) selected
                            @elseif ($key == $value) selected
                        @endif
                        @endisset
                    >{{$option}}</option>
                @elseif (is_array($option))
                    <optgroup label="{{ $key }}">
                        @foreach($option as $optionKey => $optionValue)
                            <option value="{{$optionKey}}"
                                    @isset($value)
                                        @if (is_array($value) && in_array($optionKey, $value)) selected
                                    @elseif (isset($value[$optionKey]) && $value[$optionKey] == $optionValue) selected
                                    @elseif ($optionKey == $value) selected
                                @endif
                                @endisset
                            >{{$optionValue}}</option>
                        @endforeach
                    </optgroup>
                @endif
            @endforeach
        </select>
    </div>
@endcomponent
