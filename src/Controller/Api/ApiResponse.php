<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends Response
{
  private $code;
  private $msg;

  public function __construct(
    $content = '',
    $status = 200,
    $headers = [],
    $code = null,
    $msg = null
  ) {
    parent::__construct($content, $status, $headers);
    $this->code = $code;
    $this->msg = $msg;
  }

  public static function success(
    $content = '',
    $code = null,
    $msg = null
  ) {
    $response = new ApiResponse('', 200, ['Content-Type' => 'application/json'], $code, $msg);
    $data = [
      'code' => $code,
      'message' => $msg,
      'data' => json_decode($content),
    ];
    $response->setContent(json_encode($data));
    return $response;
  }

  public static function error(
    $content = '',
    $code = null,
    $msg = null
  ) {
    $response = new ApiResponse('', 500, ['Content-Type' => 'application/json'], $code, $msg);
    $data = [
        'code' => $code,
        'message' => $msg,
        'data' => json_decode($content),
    ];
    $response->setContent(json_encode($data));
    return $response;
  }
}
