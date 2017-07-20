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

use Civi\API\Event\AuthorizeEvent;
use Civi\API\Event\PrepareEvent;
use Civi\API\Event\ExceptionEvent;
use Civi\API\Event\RespondEvent;
use Civi\API\Events;
use Civi\API\Exception\UnauthorizedException;
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
   * @var ErrorFormatter
   */
  private $errorFormatter;

  /**
   * @param EventDispatcherInterface $dispatcher
   */
  public function __construct(EventDispatcherInterface $dispatcher) {
    $this->dispatcher = $dispatcher;
  }

  /**
   * Parse and execute an API request
   *
   * @param Request $apiRequest
   *
   * @return Response
   * @throws \API_Exception
   */
  public function run(Request $apiRequest) {
    try {
      return $this->runRequest($apiRequest);
    }
    catch (\Exception $e) {
      $event = new ExceptionEvent($e, NULL, $apiRequest, NULL);
      $this->dispatcher->dispatch(Events::EXCEPTION, $event);
      $err = $this->errorFormatter->formatError($e, $apiRequest);

      // todo format result
      return $this->formatResult($apiRequest, $err);
    }
  }

  /**
   * Execute an API request.
   *
   * The request must be in canonical format. Exceptions will be propagated out.
   *
   * @param Request $apiRequest
   * @return Response
   */
  protected function runRequest(Request $apiRequest) {
    $this->authorize($apiRequest); // todo authorization
    $this->prepare($apiRequest);
    $result = $apiRequest->getHandler()->handle($apiRequest);
    $this->respond($apiRequest, $result);

    return $result;
  }

  /**
   * Determine if the API request is allowed (under current policy)
   *
   * @param Request $apiRequest
   *   The full description of the API request.
   * @throws UnauthorizedException
   */
  public function authorize(Request $apiRequest) {
    $event = new AuthorizeEvent(NULL, $apiRequest, NULL); // todo replace event
    $this->dispatcher->dispatch(Events::AUTHORIZE, $event);
    if (!$event->isAuthorized()) {
      // throw new UnauthorizedException("Authorization failed"); todo authorization
    }
  }

  /**
   * Allow third-party code to manipulate the API request before execution.
   *
   * @param Request $apiRequest
   *   The full description of the API request.
   */
  public function prepare(Request $apiRequest) {
    $event = new PrepareEvent(NULL, $apiRequest, NULL); // todo replace event
    $this->dispatcher->dispatch(Events::PREPARE, $event);
  }

  /**
   * Allow third-party code to manipulate the API response after execution.
   *
   * @param Request $apiRequest
   *   The full description of the API request.
   * @param array $result
   *   The response to return to the client.
   */
  public function respond(Request $apiRequest, $result) {
    $event = new RespondEvent(NULL, $apiRequest, $result, NULL); // todo replace event
    $this->dispatcher->dispatch(Events::RESPOND, $event);
  }
}
