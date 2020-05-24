<?php
  declare(strict_types=1);

  namespace ServerSentEvents;

  interface EventInterface {
    public function print(\Closure $printer = null);
    public function __toString(): string;
  }
