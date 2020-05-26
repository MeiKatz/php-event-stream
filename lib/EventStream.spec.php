<?php
  use \ServerSentEvents\EventStream;
  use \ServerSentEvents\Event;

  class TestClass {
  }

  describe(EventStream::class, function () {
    beforeEach(function () {
      $this->stream = new EventStream();
    });

    describe("->add()", function () {
      it("accepts an instance of Event", function () {
        $event = new Event([
          "data" => "foo",
        ]);

        expect(function () use ($event) {
            $this->stream->add($event);
          })
          ->not->to->throw("Exception");
      });
    });

    describe("->print()", function () {
      it("prints the events in the order they were added", function () {
        $event1 = new Event([
          "data" => "foo",
          "name" => "event1",
        ]);

        $event2 = new Event([
          "data" => "bar",
          "name" => "event2",
        ]);

        $this->stream->add($event1);
        $this->stream->add($event2);

        expect($this->stream->print())
          ->to->equal(implode("\n", [
            "event: event1",
            "data: foo",
            "",
            "event: event2",
            "data: bar",
            "",
          ]));
      });

      describe("when a custom printer is used", function () {
        it("passes the generated stream as argument", function () {
          $that = $this;

          $this->stream->print(function ($stream) use ($that) {
            expect($stream)
              ->to->equal($that->stream->print());
          });
        });
      });
    });
  });
