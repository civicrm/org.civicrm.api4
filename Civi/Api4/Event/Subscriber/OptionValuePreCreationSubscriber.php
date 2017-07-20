<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\ApiInterface;
use Civi\Api4\GetParameterBag;
use Civi\Api4\Request;

class OptionValuePreCreationSubscriber extends AbstractPreCreationSubscriber {

  /**
   * @var ApiInterface
   */
  protected $optionGroupApi;

  /**
   * @param ApiInterface $optionGroupApi
   */
  public function __construct(ApiInterface $optionGroupApi) {
    $this->optionGroupApi = $optionGroupApi;
  }

  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @inheritdoc
   */
  protected function applies(Request $request) {
    return $request->getEntity() === 'OptionValue';
  }

  /**
   * @param Request $request
   *
   * @throws \Exception
   */
  private function setOptionGroupId(Request $request) {
    $optionGroupName = $request->get('option_group');
    if (!$optionGroupName || $request->get('option_group_id')) {
      return;
    }

    $params = new GetParameterBag();
    $params->addSelect('id');
    $params->addWhere('name', '=', $optionGroupName);
    $optionGroup = $this->optionGroupApi->request('get', $params);

    if ($optionGroup->count() !== 1) {
      throw new \Exception('Option group name must match only a single group');
    }

    $request->set('option_group_id', $optionGroup->first()['id']);
  }
}
