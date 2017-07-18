<?php

namespace Civi\Api4;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

interface ApiInterface {
  /**
   * @param ParameterBag $params
   *
   * @return Response
   */
  public function get(ParameterBag $params = NULL);

  /**
   * @param ParameterBag $params
   *
   * @return Response
   */
  public function create(ParameterBag $params);

  /**
   * @param RequestHandler $handler
   *
   * @return void
   */
  public function addHandler(RequestHandler $handler);

  /**
   * @return RequestHandler[]
   */
  public function getActions();

  /**
   * @return string
   *   The name of the entity this API gives access to.
   *   Defaults to class shortname, minus the last three characters (API)
   */
  public function getEntity();
}
