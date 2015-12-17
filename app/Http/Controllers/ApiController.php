<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response as IlluminateResponse;

class ApiController extends Controller
{
    protected $statusCode = IlluminateResponse::HTTP_OK;

    public function root()
    {
        return $this->respond([
            "feeds" => "/api/v1/feeds?include={entries}",
            "entries" => "/api/v1/entries"
        ], $headers = array("Content-Type"=>"application/json"));
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function respond($data, $headers = [])
    {
        if (!array_key_exists("Content-Type", $headers)) {
            $headers['Content-Type'] = 'application/vnd.api+json';
        }
        if ($data) {
            return response()->json($data, $this->getStatusCode(), $headers, $options = JSON_PRETTY_PRINT);
        } else {
            $response = response("", $this->getStatusCode());
            foreach ($headers as $k => $v) {
                $response->header($k, $v);
            }
            return $response;
        }
    }
}
