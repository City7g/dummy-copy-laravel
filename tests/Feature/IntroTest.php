<?php

describe("GET /api/test", function () {
    it("success responce", function () {
        $response = $this->getJson("/api/test");

        $response->assertStatus(200)->assertJson([
            "status" => "ok",
            "method" => "GET",
        ]);
    });
});

describe("GET /api/ip", function () {
    it("returns hardcode ip", function () {
        $response = $this->getJson("/api/ip");

        $response->assertStatus(200)->assertJson([
            "ip" => "127.0.0.1",
        ]);
    });
});
