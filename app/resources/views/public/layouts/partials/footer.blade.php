@php
use App\Modules\Databank\Public\Enums\VehicleRouteName;
use Illuminate\Support\Facades\Route;
@endphp
<footer class="footer">
    <div class="footer__content container">
        <p>
            <a href="https://github.com/alligator-alex/star-wars-databank" target="_blank" rel="nofollow noopener">Source code</a>
        </p>
        @if (!isset($exception))
            <p>
                @if (Route::currentRouteName() === VehicleRouteName::ONE->value)
                    This page uses materials from the Wookieepedia article "<a href="@yield('copyright-external-url')" target="_blank">@yield('copyright-title')</a>"<br>
                @else
                    This page uses materials from the <a href="https://starwars.fandom.com/" target="_blank">Wookieepedia</a><br>
                @endif
                and licensed under the <a href="https://creativecommons.org/licenses/by-sa/3.0/" target="_blank">CC BY-SA 3.0</a>
            </p>
        @endif
        <p>
            All images and logos are the&nbsp;property of <a href="https://www.lucasfilm.com/" target="_blank" rel="nofollow noopener">Lucasfilm&nbsp;Ltd</a>
        </p>
    </div>
</footer>
