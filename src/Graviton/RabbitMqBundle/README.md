# GravitonRabbitMqBundle

# Features

* Provides a RequestListener that creates `EventStatus` entities when workers are subscribed to certain events.
* Invokes clients that publish those events to the queue.

## Inner Working

Central to this bundle is `Graviton\RabbitMqBundle\Listener\EventStatusLinkResponseListener`.
It implements the logic as described in the [publicly available documentation](https://gravity-platform-docs.nova.scapp.io/api/event/).

## RabbitMQ Handling

The "onKernelResponse"-Method of EventStatusLinkResponseListener defines permanent Queue(s) on the RabbitMQ-Server, 
named after the workerId(s) corresponding to the given Event. These are defined in the MongoDB according to the Registration of the Worker in Graviton
Check endpoint /event/worker
Whoever starts first - Graviton-Event (Producer) or Worker (Consumer) - defines the Queue. The Queue-Definitions must be compatible on both sides. 
Changing the Queue-Settings only on one Side will lead to an Error.

The Queues are defined as persistant even to a failure of the RabbitMQ-Server. RabbitMQ does a balanced round-robin dispatching to multiple workers 
subscribed to one Queue. The RabbitMQ-Job will stay in the Queue until the Worker assigns a Message Aknowledgment to it.

All this Behaviour is defined by settings on Graviton and/or Worker Side. The Infrastructure i.e. Docker Enviroment just has to provide a RabbitMQ-Server. 
The Connection-Credentials are set in app/config