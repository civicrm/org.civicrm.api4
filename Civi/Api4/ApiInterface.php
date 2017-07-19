<?php

namespace Civi\Api4;

use Civi\Api4\Handler\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

interface ApiInterface {

  /**
   * @param $action
   *   The name of the action
   * @param ParameterBag|array|NULL $params
   *   Parameters to be used in thr request
   *
   * @return Response
   */
  public function request($action, $params = NULL);

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
