<?php
class wa72WebserviceJsonRouting
{
  /**
   * the first path segment for routes to waWebserviceJson module
   * @var string
   */
  public static $route_prefix = 'waWebserviceJson';
  
  /**
   * Listens to the routing.load_configuration event.
   *
   * @param sfEvent An sfEvent instance
   */
  static public function listenToRoutingLoadConfigurationEvent(sfEvent $event)
  {
    $r = $event->getSubject();
    // preprend our routes
    $r->prependRoute('jsonwebservice_index', new sfRoute('/'.self::$route_prefix.'/:object', array('module' => 'waWebserviceJson', 'action' => 'index')));
    $r->prependRoute('jsonwebservice_get', new sfRoute('/'.self::$route_prefix.'/:object/:id', array('module' => 'waWebserviceJson', 'action' => 'getObject')));
  }
}