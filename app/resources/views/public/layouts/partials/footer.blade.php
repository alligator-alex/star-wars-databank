@php
    use App\Modules\Vehicle\Public\Enums\VehicleRouteName;
    use Illuminate\Support\Facades\Request;
@endphp
<footer class="footer">
    <div class="footer__content container">
        <p>
            <a href="https://github.com/alligator-alex/star-wars-databank"
               target="_blank"
               rel="nofollow noopener"
               class="source-code">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 432 416">
                    <path fill="currentColor"
                          d="M213.5 0q88.5 0 151 62.5T427 213q0 70-41 125.5T281 416q-14 2-14-11v-58q0-27-15-40q44-5 70.5-27t26.5-77q0-34-22-58q11-26-2-57q-18-5-58 22q-26-7-54-7t-53 7q-18-12-32.5-17.5T107 88h-6q-12 31-2 57q-22 24-22 58q0 55 27 77t70 27q-11 10-13 29q-42 18-62-18q-12-20-33-22q-2 0-4.5.5t-5 3.5t8.5 9q14 7 23 31q1 2 2 4.5t6.5 9.5t13 10.5T130 371t30-2v36q0 13-14 11q-64-22-105-77.5T0 213q0-88 62.5-150.5T213.5 0z"/>
                </svg>
                <span>Source code</span>
            </a>
        </p>
        @if (!isset($exception))
            <p>
                @if (Request::route()?->getName() === VehicleRouteName::DETAIL->value)
                    This page uses materials from the Wookieepedia article "<a href="@yield('copyright-external-url')"
                                                                               target="_blank">@yield('copyright-title')</a>
                    "<br>
                @else
                    This page uses materials from the <a href="https://starwars.fandom.com/" target="_blank">Wookieepedia</a>
                    <br>
                @endif
                and licensed under the <a href="https://creativecommons.org/licenses/by-sa/3.0/" target="_blank">CC
                    BY-SA 3.0</a>
            </p>
        @endif
        <p>
            All images and logos are the&nbsp;property of <a href="https://www.lucasfilm.com/" target="_blank"
                                                             rel="nofollow noopener">Lucasfilm&nbsp;Ltd</a>
        </p>
    </div>
</footer>
