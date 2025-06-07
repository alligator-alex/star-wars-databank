package broker

import (
	"log"
	"time"

	amqp "github.com/rabbitmq/amqp091-go"
)

// RabbitMQClient is a client for RabbitMQ broker.
type RabbitMQClient struct {
	exchangeName string
	connection   *amqp.Connection
	channel      *amqp.Channel
}

func NewRabbitMQClient(config Config, exchangeName string) RabbitMQClient {
	conn, err := amqp.DialConfig(config.GetDsn(), amqp.Config{
		Heartbeat: 30 * time.Second,
	})

	if err != nil {
		log.Panicf("%s: %s", "Failed to connect to RabbitMQ", err)
	}

	channel, err := conn.Channel()
	if err != nil {
		log.Panicf("%s: %s", "Failed to open a channel", err)
	}

	err = channel.ExchangeDeclare(exchangeName, "fanout", true, false, false, false, nil)
	if err != nil {
		log.Panicf("%s: %s", "Failed to declare an Exchange", err)
	}

	return RabbitMQClient{
		exchangeName: exchangeName,
		connection:   conn,
		channel:      channel,
	}
}

// Publish sends the message to exchange.
func (c *RabbitMQClient) Publish(body string) {
	message := amqp.Publishing{
		ContentType: "application/json",
		Body:        []byte(body),
	}

	err := c.channel.Publish(c.exchangeName, "", false, false, message)
	if err != nil {
		log.Panicf("%s: %s", "Failed to publish a message", err)
	}
}

// Stop closes all channels and connections.
func (c *RabbitMQClient) Stop() {
	err := c.channel.Close()
	if err != nil {
		panic(err)
	}

	err = c.connection.Close()
	if err != nil {
		panic(err)
	}
}
