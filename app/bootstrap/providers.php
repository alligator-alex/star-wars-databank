<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Core\Common\Providers\MorphServiceProvider::class,
    App\Modules\MessageBroker\Common\Providers\ConsumerProvider::class,
    App\Modules\Databank\Common\Providers\DatabankServiceProvider::class,
];
