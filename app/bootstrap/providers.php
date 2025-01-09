<?php

return [
    App\Providers\AppServiceProvider::class,
    \App\Modules\Core\Common\Providers\AdminServiceProvider::class,
    App\Modules\MessageBroker\Common\Providers\ConsumerProvider::class,
    App\Modules\Databank\Common\Providers\DatabankProvider::class,
];
