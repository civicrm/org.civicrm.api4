<?php

namespace Civi\Api4\Action\MockArrayEntity;
use Civi\Api4\Generic\ArrayQueryTrait;
use \Civi\Api4\Generic\Result;
use \Civi\Api4\Action\Get as DefaultGet;

/**
 * Get
 */
class Get extends DefaultGet {
  use ArrayQueryTrait;

  public function getObjects() {
    $data = [
      [
        'field1' => 1,
        'field2' => 'zebra',
        'field3' => NULL,
        'field4' => [1, 2, 3],
        'field5' => 'apple',
      ],
      [
        'field1' => 2,
        'field2' => 'yack',
        'field3' => 0,
        'field4' => [2, 3, 4],
        'field5' => 'banana',
        'field6' => '',
      ],
      [
        'field1' => 3,
        'field2' => 'x ray',
        'field4' => [3, 4, 5],
        'field5' => 'banana',
        'field6' => 0,
      ],
      [
        'field1' => 4,
        'field2' => 'wildebeest',
        'field3' => 1,
        'field4' => [4, 5, 6],
        'field5' => 'apple',
        'field6' => '0',
      ],
      [
        'field1' => 5,
        'field2' => 'vole',
        'field3' => 1,
        'field4' => [4, 5, 6],
        'field5' => 'apple',
        'field6' => 0,
      ],
    ];
    return $this->queryArray($data);
  }

}
