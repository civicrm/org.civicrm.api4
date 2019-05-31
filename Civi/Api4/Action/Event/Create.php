<?php

namespace Civi\Api4\Action\Event;

/**
 * Create a new Event.
 *
 * Pass a template_id to create this event from a template.
 */
class Create extends \Civi\Api4\Generic\DAOCreateAction {

  protected function writeObjects($items) {
    foreach ($items as &$params) {
      // Clone event from template
      if (!empty($params['template_id'])) {
        $copy = \CRM_Event_BAO_Event::copy($params['template_id']);
        $params['id'] = $copy->id;
        unset($params['template_id']);
      }
    }

    return parent::writeObjects($items);
  }

}
