<?php
namespace Civi\Api4\Generic\Traits;

/**
 * @inheritDoc
 */
trait IsCurrentTrait {

  /**
   * Convenience filter for selecting items that are enabled and do not have a past end-date.
   *
   * Adding current = TRUE is a shortcut for
   *   WHERE is_active = 1 AND (end_date IS NULL OR end_date >= now)
   *
   * Adding current = FALSE is a shortcut for
   *   WHERE is_active = 0 OR end_date < now
   *
   * @var bool
   */
  protected $current;

  /**
   * @return bool
   */
  public function getCurrent() {
    return $this->current;
  }

  /**
   * @param bool $current
   * @return $this
   */
  public function setCurrent($current) {
    $this->current = $current;
    return $this;
  }

  protected function getObjects() {
    if ($this->current) {
      $this->addWhere('is_active', '=', '1');
      $this->addClause('OR', ['end_date', 'IS NULL'], ['end_date', '>=', 'now']);
    }
    elseif ($this->current === FALSE) {
      $this->addClause('OR', ['is_active', '=', '0'], ['end_date', '<', 'now']);
    }
    return parent::getObjects();
  }

}
