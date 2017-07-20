<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiInterface;
use Civi\Api4\GetParameterBag;
use Civi\Api4\ApiRequest;

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
  public function modify(ApiRequest $request) {
    $this->setOptionGroupId($request);
  }

  /**
   * @inheritdoc
   */
  public function applies(ApiRequest $request) {
    return $request->getEntity() === 'OptionValue';
  }

  /**
   * @param ApiRequest $request
   *
   * @throws \Exception
   */
  private function setOptionGroupId(ApiRequest $request) {
    $optionGroupName = $request->get('option_group');
    if (!$optionGroupName || $request->get('option_group_id')) {
      return;
    }

    $params = new GetParameterBag();
    $params->addSelect('id');
    $params->addWhere('name', '=', $optionGroupName);
    $optionGroup = $this->optionGroupApi->request('get', $params, FALSE);

    if ($optionGroup->count() !== 1) {
      throw new \Exception('Option group name must match only a single group');
    }

    $request->set('option_group_id', $optionGroup->first()['id']);
  }
}
