<?php
if (in_array('waWebserviceJson', sfConfig::get('sf_enabled_modules', array())))
{
  $this->dispatcher->connect('routing.load_configuration', array('wa72WebserviceJsonRouting', 'listenToRoutingLoadConfigurationEvent'));
}