<?php

namespace Dunkul\ReturnData;

use Illuminate\Http\Request;

class ReturnDataClass
{
  private $data;
  private $message;
  private $request;
  private $defaultErrorCode;

  public function __construct()
  {
    $this->data = null;
    $this->message = null;
    $this->condition = [];
    $this->defaultErrorCode = [
      400 => 'BAD_REQUEST',
      401 => 'UNAUTHORIZED',
      404 => 'NOT_FOUND',
      408 => 'TIME_OUT',
      422 => 'VALIDATION_ERROR',
      500 => 'INTERNAL_SERVER_ERROR',
    ];
  }

  public function setData($data, Request $request = null)
  {
    $this->data = $data;
    $this->message = '';
    $this->request = $request;

    return $this;
  }

  public function setError($message, Request $request = null)
  {
    $this->message = $message;
    $this->request = $request;

    return $this;
  }

  public function send($code, bool $only = false, $cookie = null): Object
  {
    if (!$code || !is_numeric($code) || $code == 0 || $code >= 600) $code = 500;

    $returnCode = $only === true ? 200 : $code;

    if ($this->request && !is_null($this->data)) {
      $this->condition = $this->request->all();

      if (isset($this->condition['password']) && $this->condition['password']) {
        unset($this->condition['password']);
      }

      foreach (['token', 'access_token'] as $value) {
        if (isset($this->condition[$value])) {
          unset($this->condition[$value]);
        }
      }
    }

    if ($this->message) {
      if (!is_array($this->message) || count($this->message) == 1) {
        $this->message = ['code' => $this->defaultErrorCode[$code] ?? 500, 'message' => $this->message];
      } else {
        $this->message = ['code' => $this->message[0], 'message' => $this->message[1]];
      }
    }

    if (is_array($this->condition)) {
      array_walk($this->condition, function (&$condition) {
        if (!is_array($condition)) {
          $jsonCondition = json_decode($condition, true);
          if (json_last_error() === JSON_ERROR_NONE) {
            $condition = $jsonCondition;
          }
        }
      });
    }

    $response = response()->json(
      array_merge(
        [
          'response' => $code,
          'data' => $this->data,
          'error' => $this->message,
          'condition' => $this->condition,
        ],
        isset($this->request->access_token) ? ['access_token' => $this->request->access_token] : [],
      ),
      $returnCode,
      [],
      JSON_UNESCAPED_UNICODE
    );

    if (!$cookie) {
      return $response;
    } else {
      return $response->withCookie($cookie);
    }
  }
}
