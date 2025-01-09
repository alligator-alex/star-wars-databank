package broker

import "sw-vehicles/internal/app/helpers"

type Config struct {
	host     string
	port     string
	user     string
	password string
}

func NewConfig(host, port, user, password string) Config {
	return Config{
		host:     host,
		port:     port,
		user:     user,
		password: password,
	}
}

func (c *Config) GetDsn() string {
	return helpers.ConcatStrings("amqp://", c.user, ":", c.password, "@", c.host, ":", c.port, "/")
}
