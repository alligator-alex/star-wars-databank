<?php

declare(strict_types=1);

namespace App\Modules\MessageBroker\Common\Components;

use App\Modules\MessageBroker\Common\Contracts\Consumer;
use Exception;
use Generator;
use Illuminate\Support\Str;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use SplQueue;

/**
 * RabbitMQ consumer.
 */
class RabbitMQConsumer implements Consumer
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private SplQueue $messages;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly string $exchange,
        private readonly int $connectionTimeout = 10,
        private readonly int $readWriteTimeout = 120,
        private readonly int $heartbeat = 60,
        private readonly int $channelRpcTimeout = 120
    ) {
        $this->messages = new SplQueue();
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function getMessages(): Generator
    {
        $this->start();

        while ($this->channel->is_consuming()) {
            while (!$this->messages->isEmpty()) {
                yield $this->messages->dequeue();
            }

            try {
                $this->channel->wait(timeout: 3600);
            } catch (AMQPTimeoutException $e) {
                $this->logger->error('Timeout: ' . $e->getMessage());
                $this->stop();
            }
        }

        $this->stop();
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function start(): void
    {
        $this->establishConnection();

        $queue = $this->declareNewQueue();

        $this->channel->basic_consume(
            queue: $queue,
            callback: function (AMQPMessage $message): void {
                $logMessage = Str::limit($message->getBody(), 5000);
                $this->logger->info('Received message: "' . $logMessage . '"');

                if ($message->getBody() === '<<< THE END >>>') {
                    $message->ack();
                    $this->stop();
                    return;
                }

                $this->messages->enqueue($message->getBody());
                $message->ack();
            }
        );

        $this->logger->info('Consuming from queue "' . $queue . '" bound to exchange "' . $this->exchange . '"');
    }

    /**
     * Establish connection with host.
     *
     * @return void
     *
     * @throws Exception
     */
    private function establishConnection(): void
    {
        $this->connection = new AMQPStreamConnection(
            host: $this->host,
            port: $this->port,
            user: $this->user,
            password: $this->password,
            connection_timeout: $this->connectionTimeout,
            read_write_timeout: $this->readWriteTimeout,
            heartbeat: $this->heartbeat,
            channel_rpc_timeout: $this->channelRpcTimeout
        );

        $this->channel = $this->connection->channel();

        $this->logger->info('Established connection with ' .  $this->host . ':' . $this->port);
    }

    /**
     * Declare new queue with "<exchange>-queue." prefix and bind it to related exchange.
     *
     * @return string
     */
    private function declareNewQueue(): string
    {
        $queueName = uniqid($this->exchange . '-queue.', true);

        $this->channel->queue_declare(queue: $queueName, durable: true, exclusive: true);
        $this->channel->queue_bind(queue: $queueName, exchange: $this->exchange);

        return $queueName;
    }

    /**
     * @throws Exception
     */
    private function stop(): void
    {
        if ((!isset($this->connection) || !$this->connection->isConnected())
            && (!isset($this->channel) || !$this->channel->is_open())) {
            return;
        }

        $this->logger->info('Stopping consumer');

        if ($this->channel->is_open()) {
            $this->channel->stopConsume();
            $this->channel->close();

            $this->logger->info('Channel closed');
        }

        if ($this->connection->isConnected()) {
            $this->connection->close();

            $this->logger->info('Connection closed');
        }

        $this->logger->info('Consumer stopped');
    }

    public function __destruct()
    {
        $this->stop();
    }
}
