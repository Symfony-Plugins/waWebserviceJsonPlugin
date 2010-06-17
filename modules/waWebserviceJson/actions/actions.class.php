<?php
/**
 * webservice actions.
 *
 * @package    sbw
 * @subpackage webservice
 * @author     Christoph Singer
 * @version    SVN: $Id: actions.class.php 74 2010-04-16 07:13:55Z christoph $
 */
class waWebserviceJsonActions extends sfActions
{
  /**
   * Executes index action
   *
   * @param sfRequest $request A request object
   */
  public function executeIndex(sfWebRequest $request)
  {
    try {
      $listbuilder = new wa72RequestListBuilder($request);
    } catch (PeerclassNotFoundException $e) {
      $this->redirect404();
    }
    $r = $listbuilder->getJsonList();
    $this->renderText($r);
    $this->has_layout = false;
    return sfView::NONE;
  }

  public function executeGetObject(sfWebRequest $request) {
    if (false !== array_search('sfPropelPlugin',sfProjectConfiguration::getActive()->getPlugins())) {
      $peerclass = $request->getParameter('object') . 'Peer';
      $this->forward404Unless(class_exists($peerclass));
      $object = call_user_func(array($peerclass, 'retrieveByPK'), $request->getParameter('id'));
    } else if (false !== array_search('sfDoctrinePlugin',sfProjectConfiguration::getActive()->getPlugins())) {
      $object = Doctrine::getTable($request->getParameter('object'))->find(array($request->getParameter('id')));
    } else {
      throw new Exception('waWebserviceJsonActions: neither sfPropelPlugin nor sfDoctrinePlugin enabled');
    }
    $this->forward404Unless($object);
    if (method_exists($object, 'toArrayForWebservice')) {
      $a = $object->toArrayForWebservice();
    }
    else $a = $object->toArray();
    $r = json_encode($a);
    if ($request->hasParameter('callback')) {
      $r = $request->getParameter('callback') . '(' . $r . ');';
    }
    $this->renderText($r);
    $this->has_layout = false;
    return sfView::NONE;
  }
}