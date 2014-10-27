PHP EventStream for Server-sent Events
================
```php
require_once 'event_stream.php';

# create a nameless event (will be send to "message")
$a = Event::create();

# create a named event (will be send to the defined name, in this case "login")
$b = Event::create( 'login' );

# set content for the events
$a->data = 'new message' . "\n" . 'in your inbox';
$b->data = [
  'user' => 'foobar',
  'id'   => 42
];

# you can also define a retry time or an event id
$a->id = 1337;
$b->retry = 3000; # 3000 milliseconds = 3 seconds

# create a event-stream
$stream = EventStream::create();

# add events to the stream (events without a name are collected at the beginning of the message)
$stream->add( $b );
$stream->add( $a );

# generate content
echo $stream->generate();
```
The script below would generate the following message:
```
id: 1337
data: new message
data: in your inbox
event: login
retry: 3000
data: {"user":"foobar","id":42}
```
On the client-side you can fetch this with the following script:
```JavaScript
if ( !window.EventStream ) {
  console.warn( 'EventStream is not available' );
}

var stream = new EventStream( 'events.php' );
// for nameless events
stream.addEventListener( 'message', function ( e ) {
  console.log( e.data );
});

// for login events
stream.addEventListener( 'login', function ( e ) {
  var data = JSON.parse( e.data );
  console.log( 'user "' + data.user + '" with id ' + data.id + ' has logged in' );
});

// on error
stream.addEventListener( 'error' function ( e ) {
  console.warn( 'an error occured: ' + e.data );
});
```
