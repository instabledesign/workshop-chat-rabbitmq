# Workshop chat server with RabbitMQ

## Installation

You need to
 
```
docker-compose up -d
```

> The docker use local port `80` `5672` `15672`

Open your browser and go to [http://localhost/](http://localhost/)
You can verify your config at [http://localhost/configtest.php](http://localhost/configtest.php).
All is ok if the last text block appear over the time

```
Begin stream text during 3sec with 10 character string...
 ├ 1 ..........
 ├ 2 ..........
 ├ 3 ..........
 └ End test.
 ```
