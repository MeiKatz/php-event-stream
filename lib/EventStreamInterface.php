<?php
  declare(strict_types=1);

  namespace ServerSentEvents;

  require_once "EventInterface.php";

  interface EventStreamInterface {
    public function add(EventInterface $event): void;
    public function print(\Closure $printer = null);
    public function __toString(): string;
    public function send(): void;
  }
