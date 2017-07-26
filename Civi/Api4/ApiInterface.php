<?php

namespace Civi\Api4;

use Civi\Api4\Handler\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

interface ApiInterface {

  /**
   * @param $action
   *   The action to be performed. @see Actions
   * @param ParameterBag|array|NULL $params
   *   The parameters to use in the request.
   * @param bool $checkPermission
   *   Whether to check permission or not.
   *
   * @return Response
   */
  public function request($action, $params = NULL, $checkPermission = TRUE);

  /**
   * @param RequestHandlerInterface $handler
   *
   * @return void
   */
  public function addHandler(RequestHandlerInterface $handler);

  /**
   * @return string[]
   *   An array of available actions for this API
   */
  public function getActions();

  /**
   * @return string
   *   The name of the entity this API gives access to.
   *   Defaults to class shortname, minus the last three characters (API)
   */
  public function getEntity();
}
