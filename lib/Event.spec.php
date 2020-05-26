<?php
  use \ServerSentEvents\Event;

  class ClassWithToString {
    public function __toString() {
      return "foo";
    }
  }

  class ClassWithoutToString {}

  describe(Event::class, function () {
    describe("constructor()", function () {
      $check = function ($name, $cases, $block) {
        foreach ($cases as list($description, $value, $throws)) {
          describe(
            sprintf("when `%s` %s", $name, $description),
            function () use ($value, $throws, $block) {
              # if is expected to throw an exception
              if ($throws) {
                it(
                  "throws an exception",
                  function () use ($block, $value) {
                    expect(function () use ($block, $value) {
                        $block($value);
                      })
                      ->to->throw("InvalidArgumentException");
                  }
                );
              # if is expected to _not_ throw an exception
              } else {
                it(
                  "throws not an exception",
                  function () use ($block, $value) {
                    expect(function () use ($block, $value) {
                        $block($value);
                      })
                      ->to->not->throw("InvalidArgumentException");
                  }
                );
              }
            }
          );
        }
      };

      describe("`data`", function () use ($check) {
        $cases = [
          [
            "is a string",
            "foo",
            false,
          ],
          [
            "is an integer",
            42,
            false,
          ],
          [
            "is a float",
            13.37,
            false,
          ],
          [
            "is an array",
            [ "foo" => "bar" ],
            false,
          ],
          [
            "is an object with ->__toString()",
            new ClassWithToString(),
            false,
          ],
          [
            "is missing",
            null,
            true,
          ],
          [
            "is an object without ->__toString()",
            new ClassWithoutToString(),
            true,
          ],
        ];

        $check("data", $cases, function ($value) {
          new Event([
            "data" => $value,
          ]);
        });
      });

      describe("`id`", function () use ($check) {
        $cases = [
          [
            "is missing",
            null,
            false,
          ],
          [
            "is an integer",
            42,
            false,
          ],
          [
            "is an integer-like string",
            "42",
            false,
          ],
          [
            "is a float",
            13.37,
            true,
          ],
          [
            "is a float-like string",
            "13.37",
            true,
          ],
        ];

        $check("id", $cases, function ($value) {
          new Event([
            "data" => "foo",
            "id" => $value,
          ]);
        });
      });

      describe("`name`", function () use ($check) {
        $cases = [
          [
            "is missing",
            null,
            false,
          ],
          [
            "is a string",
            "foo",
            false,
          ],
          [
            "is a number",
            42,
            true,
          ],
        ];

        $check("name", $cases, function ($value) {
          new Event([
            "data" => "foo",
            "name" => $value,
          ]);
        });
      });

      describe("`retry`", function () use ($check) {
        $cases = [
          [
            "is missing",
            null,
            false,
          ],
          [
            "is a string",
            "foo",
            true,
          ],
          [
            "is a number",
            42,
            false,
          ],
          [
            "is a number less than or equal to 0",
            -6,
            true,
          ],
        ];

        $check("retry", $cases, function ($value) {
          new Event([
            "data" => "foo",
            "retry" => $value,
          ]);
        });
      });
    });

    describe("->print()", function () {
      beforeEach(function () {
        $this->values = [
          "data" => "foo",
          "id" => 42,
          "name" => "test",
          "retry" => 1337,
        ];
        $this->event = new Event($this->values);
      });

      describe("when parameter is omitted", function () {
        it("uses the default printer", function () {
          expect($this->event->print())
            ->to->equal($this->event->__toString());
        });
      });

      describe("when parameter is passed", function () {
        it("uses the custom printer", function () {
          $printer = function ($values) {
            return $values;
          };

          expect($this->event->print($printer))
            ->to->equal($this->values);
        });
      });
    });

    describe("->__toString()", function () {
      it("formats the values correct", function () {
        $event = new Event([
          "data" => "foo",
          "id" => 42,
          "name" => "test",
          "retry" => 1337,
        ]);

        expect($event->__toString())
          ->to->equal(implode("\n", [
            "event: test",
            "id: 42",
            "retry: 1337",
            "data: foo",
            "",
          ]));
      });

      describe("when `data` is an array", function () {
        it("is converted to JSON", function () {
          $event = new Event([
            "data" => [
              "foo" => "bar"
            ],
          ]);

          expect($event->__toString())
            ->to->equal(implode("\n", [
              "data: {\"foo\":\"bar\"}",
              "",
            ]));
        });
      });

      describe("when `id` is omitted", function () {
        $event = new Event([
          "data" => "foo",
          "name" => "test",
          "retry" => 1337,
        ]);

        it("omits the id", function () use ($event) {
          expect($event->__toString())
            ->to->equal(implode("\n", [
              "event: test",
              "retry: 1337",
              "data: foo",
              "",
            ]));
        });
      });

      describe("when `name` is omitted", function () {
        $event = new Event([
          "data" => "foo",
          "id" => 42,
          "retry" => 1337,
        ]);

        it("omits the name", function () use ($event) {
          expect($event->__toString())
            ->to->equal(implode("\n", [
              "id: 42",
              "retry: 1337",
              "data: foo",
              "",
            ]));
        });
      });

      describe("when `retry` is omitted", function () {
        $event = new Event([
          "data" => "foo",
          "id" => 42,
          "name" => "test",
        ]);

        it("omits the retry", function () use ($event) {
          expect($event->__toString())
            ->to->equal(implode("\n", [
              "event: test",
              "id: 42",
              "data: foo",
              "",
            ]));
        });
      });
    });
  });
