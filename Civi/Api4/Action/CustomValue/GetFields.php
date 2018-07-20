<?php

namespace Civi\Api4\Action\CustomValue;

use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Api4\Generic\Result;
use Civi\Api4\Service\Spec\SpecFormatter;

/**
 * Get fields for a custom group.
 *
 * @method $this setIncludeCustom(bool $value)
 * @method bool getIncludeCustom()
 * @method $this setAction(string $value)
 */
class GetFields extends \Civi\Api4\Action\GetFields {

  public function _run(Result $result) {
    /** @var SpecGatherer $gatherer */
    $gatherer = \Civi::container()->get('spec_gatherer');
    $spec = $gatherer->getSpec('Custom_' . $this->getCustomGroup(), $this->getAction(), $this->includeCustom);
    $specArray = SpecFormatter::specToArray($spec->getFields(), $this->getOptions);
    $result->action = 'getFields';
    $result->exchangeArray(array_values($specArray));
  }

}
