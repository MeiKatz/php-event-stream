<?php
namespace ServerSentEvents {
  require_once 'event.php';
  
  class EventStream {
    private $events = array();
    
    public static function create() {
      return new self();
    }
    
    private function __construct() {}
    
    public function generate() {
      $message = '';
      
      # no events, no content
      if ( empty( $this->events ) ) {
        return;
      }
      
      # collect events without a name
      $nameless_events = array_filter( $this->events, function ( $event ) {
        return ( $event->name === null );
      });
      # collect events with a name
      $named_events = array_filter( $this->events, function ( $event ) {
        return ( $event->name !== null );
      });
      
      # send nameless events before the named events
      $events = array_merge( $nameless_events, $named_events );
      
      # collect generated event messages
      foreach ( $events as $event ) {
        $tmp      = $event->generate();
        # return-value could be null
        $message .= ( $tmp === null ? '' : $tmp . "\n" );
      }
      
      return $message;
    }
    
    public function __toString() {
      $ret = $this->generate();
      return ( $ret === null ? '' : $ret );
    }
    
    public function send() {
      $message = $this->generate();
      
      if ( $message === null ) {
        return false;
      }
      
      if ( headers_sent() ) {
        return false;
      }
      
      header( 'Content-Type: text/event-stream' );
      header( 'Content-Length: ' . strlen( $message ) );
      echo $message;
      return true;
    }
  }
}
