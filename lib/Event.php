<?php
namespace ServerSentEvents {
  class Event {
    private $name  = null;
    private $id    = null;
    private $data  = '';
    private $retry = null;
    
    public static function create( $name = null ) {
      return new self( $name );
    }
    
    private function __construct(  $name ) {
      $this->name = $name;
    }
    
    public function __get( $name ) {
      switch ( $name ) {
        case 'name'  : return $this->name;
        case 'id'    : return $this->id;
        case 'data'  : return $this->data;
        case 'retry' : return $this->retry;
      }
    }
    
    public function __set( $name, $value ) {
      switch ( $name ) {
        case 'id':
          if ( is_numeric( $value ) ) {
            $this->id = (int) $value;
          } else {
            $this->id = null;
          }
        break;
        
        case 'data':
          # convert scalar values to string
          if ( is_scalar( $value ) ) {
            $this->data = (string) $value;
          # format arrays in JSON
          } elseif ( is_array( $value ) ) {
            $this->data = json_encode( $value );
          # call __toString method on objects, if exists
          } elseif ( is_object( $value ) && method_exists( $value, '__toString' ) ) {
            $this->data = $value->__toString();
            # reset value to an empty string, if __toString did not return a string
            if ( !is_string( $this->data ) ) {
              $this->data = '';
            }
          # reset value to an empty string
          } else {
            $this->data = '';
          }
        break;
        
        case 'retry':
          # set value to a positive integer
          if ( is_numeric( $value ) && $value > 0 ) {
            $this->retry = (int) $value;
          # reset value to default
          } else {
            $this->retry = null;
          }
        break;
      }
    }
    
    # generate event information
    public function generate() {
      $message = '';
      
      # a event message must have some content, if not exit
      if ( empty( $this->data ) ) {
        return;
      }
      
      if ( $this->name !== null ) {
        $message .= 'event: ' . $this->name . "\n";
      }
      
      if ( $this->id !== null ) {
        $message .= 'id: ' . $this->id . "\n";
      }
      
      if ( $this->retry !== null ) {
        $message .= 'retry: ' . $this->retry . "\n";
      }
      
      # every line must start with a "data: "
      $lines    = explode( "\n", $this->data );
      $message .= 'data: ' . implode( "\n" . 'data: ', $lines ) . "\n";
      return $message;
    }
    
    # return string representation of the event information
    public function __toString() {
      $ret = $this->generate();
      return ( $ret === null ? '' : $ret );
    }
  }
}
