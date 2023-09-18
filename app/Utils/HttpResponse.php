<?php
namespace App\Utils;

class HttpResponse
{

  public static function ok($message, $data = null, $code = 200)
  {
    return response()->json([
      'httpCode' => $code,
      'jsonApi' => [
        'version' => '1.0.0',
        'meta' => [
          'author' => 'Simamart - MA AL-GHAZALI',
        ]
      ],
      'message' => $message,
      'data' => $data
    ]);
  }

  public static function error($message, $data = null)
  {
    return response()->json(
      [
        'httpCode' => 500,
        'jsonApi' => [
          'version' => '1.0.0',
          'meta' => [
            'author' => 'Simamart - MA AL-GHAZALI',
          ]
        ],
        'message' => $message,
        'data' => $data
      ],
      500
    );
  }

  public static function unauthorized() {
    return response()->json([
      'httpCode' => 401,
      'jsonApi' => [
        'version' => '1.0.0',
        'meta' => [
          'author' => 'Simamart - MA AL-GHAZALI',
        ]
      ],
      'message' => 'Unauthorized'
    ], 401);
  }
}