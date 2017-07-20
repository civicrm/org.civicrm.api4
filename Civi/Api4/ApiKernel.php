<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */
namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Event\AuthorizeEvent;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\ExceptionEvent;
use Civi\Api4\Event\PrepareEvent;
use Civi\Api4\Event\RespondEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Runs APIv4 requests
 */
class ApiKernel {
  /**
   * @var EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Parse and execute an API request
   *
   * @param ApiRequest $apiRequest
   *
   * @return Response
   * @throws \API_Exception
   */
  public function run(ApiRequest $apiRequest) {
    try {
      return $this->runRequest($apiRequest);
    }
    catch (\Exception $e) {
      $event = new ExceptionEvent($e, $apiRequest->get('debug'));
      $this->dispatcher->dispatch(Events::EXCEPTION, $event);

      if ($event->hasResponse()) {
        return $event->getResponse();
      }

      // exception was not handled by response formatter
      throw $e;
    }
  }

  /**
   * Execute an API request.
   *
   * The request must be in canonical format. Exceptions will be propagated out.
   *
   * @param ApiRequest $apiRequest
   *
   * @return Response
   */
  protected function runRequest(ApiRequest $apiRequest) {
    $this->authorize($apiRequest); // todo authorization
    $this->prepare($apiRequest);
    $result = $apiRequest->getHandler()->handle($apiRequest);
    $this->respond($result);

    return $result;
  }

  /**
   * Determine if the API request is allowed (under current policy)
   *
   * @param ApiRequest $apiRequest
   *   The full description of the API request.
   *
   * @throws UnauthorizedException
   */
  public function authorize(ApiRequest $apiRequest) {
    $event = new AuthorizeEvent($apiRequest);
    $this->dispatcher->dispatch(Events::AUTHORIZE, $event);
    if (!$event->isAuthorized()) {
       throw new UnauthorizedException("Authorization failed");
    }
  }

  /**
   * Allow third-party code to manipulate the API request before execution.
   *
   * @param ApiRequest $apiRequest
   *   The full description of the API request.
   */
  public function prepare(ApiRequest $apiRequest) {
    $this->dispatcher->dispatch(Events::PREPARE, new PrepareEvent($apiRequest));
  }

  /**
   * Allow third-party code to manipulate the API response after execution.
   *
   * @param Response $result
   *   The response to return to the client.
   */
  public function respond($result) {
    $this->dispatcher->dispatch(Events::RESPOND, new RespondEvent($result));
  }
}
