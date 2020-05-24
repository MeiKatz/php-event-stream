PHP EventStream for Server-sent Events
================
```php
require_once "Event.php";
require_once "EventStream.php";

use ServerSentEvents\Event;
use ServerSentEvents\EventStream;

# Create a nameless event (will be send to "message")
# `data` is a required field
$evt1 = new Event([
  "data" => "ping",
]);

# Create a named event (will be send to the defined name, in this case "login")
# `data` is a required field
$evt2 = new Event([
  "name" => "login",
  "data" => "ping",
]);

# Set content for the events (as a string)
$evt3 = new Event([
  "data" => join("\n", [
    "new message",
    "in your inbox"
  ]),
]);

# You can also use an array.
# The library will encode it in JSON.
$evt4 = new Event([
  "data" => [
    "user" => "foobar",
    "id"   => 42,
  ],
]);

# You can also define the retry time (in milliseconds).
# By default the browser will try to reconnect every three seconds.
$evt5 = new Event([
  "data" => "foo",
  "retry" => 1000, # = 1 second
]);

# And you can a define an id for your event.
$evt6 = new Event([
  "data" => "foo",
  "id" => 1337,
]);

# Create a event-stream
$stream = new EventStream();

# Add events to the stream.
# Events without a name are collected at the beginning of the message.
$stream->add($evt1);
$stream->add($evt2);
$stream->add($evt3);
$stream->add($evt4);
$stream->add($evt5);
$stream->add($evt6);

# generate content
echo $stream->print();
```
The script below would generate the following message:
```
data: ping

name: login
data: ping

data: new message
data: in your inbox

data: {"user":"foobar","id":42}

retry: 1000
data: foo

id: 1337
data: foo
```
You can also send the events directly with the appropriate headers set:
```php
$stream->send();
```
Or you use your own printer. The printer is a callback function that gets one parameter, containing the generated event stream as a string:
```php
$stream->print(function ($string) {
  # do stuff with $string
});
```
You can also use it to modify the data before it is returned:
```php
$value = $stream->print(function ($string) {
  return $string . "\n\ndata: last event\n";
});
```

On the client-side you can fetch this with the following script:
```JavaScript
if (!window.EventStream) {
  console.warn("EventStream is not available");
}

var stream = new EventStream("events.php");
// for nameless events
stream.addEventListener("message", function (evt) {
  console.log(evt.data);
});

// for login events
stream.addEventListener("login", function (evt) {
  var data = JSON.parse(evt.data);
  console.log(`user '${data.user}' with id '${data.id}' has logged in`);
});

// on error
stream.addEventListener("error", function (evt) {
  console.warn("an error occured: " + evt.data);
});
```
