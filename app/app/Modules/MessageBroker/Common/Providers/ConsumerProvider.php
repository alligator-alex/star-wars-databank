<?php

declare(strict_types=1);

namespace App\Modules\MessageBroker\Common\Providers;

use App\Modules\MessageBroker\Common\Components\ConsumerLogger;
use App\Modules\MessageBroker\Common\Components\RabbitMQConsumer;
use App\Modules\MessageBroker\Common\Contracts\Consumer;
use Illuminate\Support\ServiceProvider;

class ConsumerProvider extends ServiceProvider
{
    /**
     * @return class-string[]
     */
    public function provides(): array
    {
        return [
            Consumer::class,
        ];
    }

    public function register(): void
    {
        $this->app->bind(Consumer::class, static function (): Consumer {
            return new RabbitMQConsumer(
                logger: new ConsumerLogger(true),
                host: (string) config('rabbitmq.host'),
                port: (int) config('rabbitmq.port'),
                user: (string) config('rabbitmq.user'),
                password: (string) config('rabbitmq.password'),
                exchange: (string) config('rabbitmq.exchange'),
                connectionTimeout: (int) config('rabbitmq.options.connection_timeout', 10),
                readWriteTimeout: (int) config('rabbitmq.options.read_write_timeout', 120),
                heartbeat: (int) config('rabbitmq.options.heartbeat', 60),
                channelRpcTimeout: (int) config('rabbitmq.options.channel_rpc_timeout', 120),
            );
        });
    }
}
