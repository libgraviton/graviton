# GravitonRabbitMqBundle

# Features

* Provides a RequestListener that creates `EventStatus` entities when workers are subscribed to certain events.
* Invokes clients that publish those events to the queue.

## Inner Working

Central to this bundle is `Graviton\RabbitMqBundle\Listener\EventStatusLinkResponseListener`.
It implements the logic as described in the [publicly available documentation](https://gravity-platform-docs.nova.scapp.io/api/event/).


