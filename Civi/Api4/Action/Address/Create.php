<?php

namespace Civi\Api4\Action\Address;

use Civi\Api4\Action\Create as BaseCreate;

class Create extends BaseCreate {
  /**
   * @param $params
   *
   * @return \CRM_Core_BAO_Address|null
   */
  protected function create($params) {
    /** @var \CRM_Core_BAO_Address $bao */
    $bao = $this->bao;
    return $bao::add($params, TRUE);
  }

}
