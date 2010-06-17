<?php

class wa72SimplePropelListBuilder implements wa72SimpleListBuilder {

  /**
   * @var array
   */
  protected $searchfields = null;
  /**
   *
   * @var string
   */
  protected $peerclass;
  /**
   *
   * @var Criteria
   */
  protected $criteria;

  /**
   *
   * @param $tableClassName
   */
  public function __construct($tableClassName) {
    $this->criteria = new Criteria;
    $this->peerclass = $tableClassName . 'Peer';
    if (!class_exists($this->peerclass)) throw new PeerclassNotFoundException('Peerclass ' . $this->peerclass . ' does not exist');
    $defaultFilters = $this->getDefaultListFilters();
    if ($defaultFilters) $this->addFilters($defaultFilters);
  }

  /**
   *
   * @param array $filters fieldname => value pairs of filters
   * @return wa72SimpleListBuilder
   */
  public function addFilters($filters) {
    foreach ($filters as $filterfield => $value) {
      $filterfield = $this->translateFieldName($filterfield);
      $this->criteria->add($filterfield, $value, '=');
    }
    return $this;
  }

  /**
   * set the fields that should be searched by full text search
   *
   * @param array $fieldnames
   * @return wa72SimpleListBuilder
   */
  public function setSearchFields($fieldnames) {
    if (is_array($fieldnames)) $this->searchfields = $fieldnames;
    else $this->searchfields = array($fieldnames);
    return $this;
  }

  public function addSearch($searchterm) {
    if (!$this->searchfields) {
      $this->searchfields = $this->getDefaultSearchFieldnames();
    }
    if (!$this->searchfields) throw new Exception('wa72SimplePropelListBuilder: no searchfields');
    $where = array();
    $values = array();
    $crit_or = null;
    foreach ($this->searchfields as $field) {
      $field = $this->translateFieldName($field);
      if ($crit_or === null) $crit_or = $this->criteria->getNewCriterion($field, '%'. $searchterm . '%', Criteria::LIKE);
      else $crit_or->addOr($this->criteria->getNewCriterion($field, '%'. $searchterm . '%', Criteria::LIKE));
    }
    if ($crit_or instanceof Criterion) $this->criteria->add($crit_or);
    return $this;
  }

  /**
   *
   * @param string $sort
   * @param string $dir
   * @return wa72SimpleListBuilder
   */
  public function addSort($sort, $dir = null) {
    $orderfield = $this->translateFieldName($sort);
    if ($dir == 'DESC') {
      $this->criteria->addDescendingOrderByColumn($orderfield);
    } else {
      $this->criteria->addAscendingOrderByColumn($orderfield);
    }
    return $this;
  }

  public function addLimit($offset = 0, $limit = false) {
    if (intval($offset)) $this->criteria->setOffset($offset);
    if (intval($limit)) $this->criteria->setLimit($limit);
    return $this;
  }

  public function count() {
    return call_user_func(array($this->peerclass, 'doCount'), $this->criteria);
  }

  public function execute() {
    return call_user_func(array($this->peerclass, 'doSelect'), $this->criteria);
  }
  /*
   * get the query object
   * @return Doctrine_Query

   public function getQuery() {
   return $this->q;
   }
   */

  public function _makeFieldArray($object, $fieldnames) {
    $r = array();
    foreach ($fieldnames as $fieldname) {
      if ($fieldname == '__string') {
        $r['__string'] = $object->__toString();
      } elseif ($fieldname == '__pk') {
        $r['__pk'] = $object->getId();
      } else {
        $r[$fieldname] = call_user_func(array($object, 'get'.call_user_func(array($this->peerclass, 'translateFieldName'), $fieldname, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_PHPNAME)));
      }
    }
    return $r;
  }
  public function getDefaultListFieldnames() {
    if (method_exists($this->peerclass, 'getDefaultListFieldnames')) {
      return call_user_func(array($this->peerclass, 'getDefaultListFieldnames'));
    }
    else return array('__pk', '__string');
  }
  public function getDefaultSearchFieldnames() {
    if (method_exists($this->peerclass, 'getDefaultSearchFieldnames')) {
      return call_user_func(array($this->peerclass, 'getDefaultSearchFieldnames'));
    }
    else return null;
  }
  public function getDefaultListFilters() {
    if (method_exists($this->peerclass, 'getDefaultListFilters')) {
      return call_user_func(array($this->peerclass, 'getDefaultListFilters'));
    }
    else return null;
  }
  /**
   * translate fieldname into column name
   * @param string $fieldname
   * @return string column name
   */
  protected function translateFieldName($fieldname) {
    return call_user_func(array($this->peerclass, 'translateFieldName'), $fieldname, BasePeer::TYPE_FIELDNAME, BasePeer::TYPE_COLNAME);
  }

}
