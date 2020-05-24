<?php
  declare(strict_types=1);

  namespace ServerSentEvents;

  require_once "EventInterface.php";

  class Event implements EventInterface {
    private $name  = null;
    private $id    = null;
    private $data  = "";
    private $retry = null;

    public function __construct(array $params) {
      $this->name  = $this->extractName($params);
      $this->id    = $this->extractId($params);
      $this->data  = $this->extractData($params);
      $this->retry = $this->extractRetry($params);

      if ($this->data === null) {
        throw new \InvalidArgumentException(
          "expected `data` to be present"
        );
      }
    }

    # print event information
    public function print(\Closure $printer = null) {
      if ($printer === null) {
        $printer = $this->defaultPrinter();
      }

      return $printer([
        "data"  => $this->data,
        "id"    => $this->id,
        "name"  => $this->name,
        "retry" => $this->retry,
      ]);
    }

    # return string representation of the event information
    public function __toString(): string {
      return $this->print();
    }

    private function defaultPrinter(): \Closure {
      return function (array $values) {
        $message = "";

        if ($values["name"] !== null) {
          $message .= sprintf(
            "event: %s\n",
            $values["name"]
          );
        }

        if ($values["id"] !== null) {
          $message .= sprintf(
            "id: %u\n",
            $values["id"]
          );
        }

        if ($values["retry"] !== null) {
          $message .= sprintf(
            "retry: %u\n",
            $values["retry"]
          );
        }

        # every line must start with a "data: "
        $lines = explode("\n", $values["data"]);
        $lines = array_map(function ($line) {
          return sprintf(
            "data: %s\n",
            $line
          );
        }, $lines);

        $message .= implode("\n", $lines);

        return $message;
      };
    }

    private function extractName(array $params): ?string {
      if (!isset($params["name"])) {
        return null;
      }

      $name = $params["name"];

      if (!is_string($name)) {
        throw new \InvalidArgumentException(
          "expected `name` to be a string"
        );
      }

      $name = trim($name);

      if (strlen($name) === 0) {
        return null;
      }

      return $name;
    }

    private function extractId(array $params): ?int {
      if (!isset($params["id"])) {
        return null;
      }

      $id = $params["id"];

      if (!is_numeric($id)) {
        throw new \InvalidArgumentException(
          "expected `id` to be a number"
        );
      }

      $possibleIntegerString = (string) (int) $id;
      $possibleString = (string) $id;

      if ($possibleIntegerString !== $possibleString) {
        throw new \InvalidArgumentException(
          "expected `id` to be an integer"
        );
      }

      return (int) $id;
    }

    private function extractData(array $params): ?string {
      if (!isset($params["data"])) {
        return null;
      }

      $data = $params["data"];

      # convert scalar values to string
      if (is_scalar($data)) {
        return (string) $data;
      }

      # format arrays in JSON
      if (is_array($data)) {
        return json_encode($data);
      }

      # call __toString method on objects, if exists
      if (is_object($data) && method_exists($data, "__toString")) {
        $data = $data->__toString();

        # throw an exception, if __toString did not return a string
        if (!is_string($data)) {
          throw new \InvalidArgumentException(
            "expected return value of #__toString() to be a string"
          );
        }

        return $data;
      }

      throw new \InvalidArgumentException(
        "expected `data` to be either a string, an array, " .
        "or an object with a __toString() method"
      );
    }

    private function extractRetry(array $params): ?int {
      if (!isset($params["retry"])) {
        return null;
      }

      $retry = $params["retry"];

      # set value to a positive integer
      if (!is_numeric($retry) || $retry <= 0) {
        throw new \InvalidArgumentException(
          "expected `retry` to be a number greater then zero"
        );
      }

      return (int) $retry;
    }
  }
