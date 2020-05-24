<?php
  declare(strict_types=1);

  namespace ServerSentEvents;

  require_once "EventStreamInterface.php";
  require_once "EventInterface.php";

  class EventStream implements EventStreamInterface {
    private $events = array();

    public function add(EventInterface $event): void {
      array_push($this->events, $event);
    }

    public function print(\Closure $printer = null) {
      if ($printer === null) {
        return $this->generate();
      }

      return $printer(
        $this->generate()
      );
    }

    public function __toString(): string {
      return $this->print();
    }

    public function send(): void {
      $this->print(function ($message) {
        if (empty($message)) {
          return;
        }

        if (headers_sent()) {
          throw new \RuntimeException(
            "could not send contents: headers already sent"
          );
        }

        header("Content-Type: text/event-stream");
        header("Content-Length: " . strlen($message));

        echo $message;
      });
    }

    private function generate(): string {
      # no events, no content
      if (empty($this->events)) {
        return "";
      }

      # convert all events to strings
      $messages = array_map(function ($event) {
        return $event->print();
      }, $this->events);

      return implode("\n", $messages);
    }
  }
