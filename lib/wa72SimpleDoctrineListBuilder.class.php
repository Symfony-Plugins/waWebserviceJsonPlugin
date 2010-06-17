<?php

class wa72SimpleDoctrineListBuilder implements wa72SimpleListBuilder {

  /**
   * @var Doctrine_Query
   */
  protected $q;
  /**
   * @var array
   */
  protected $searchfields = null;

  protected $peerclass;

  public function __construct($tableClassName) {
    $this->q = Doctrine::getTable($tableClassName)->createQuery('o');
    $this->peerclass = $tableClassName . 'Table';
    if (!class_exists($this->peerclass)) throw new PeerclassNotFoundException('Peerlcass ' . $this->peerclass . ' does not exist');
    $defaultFilters = $this->getDefaultListFilters();
    if ($defaultFilters) $this->addFilters($defaultFilters);
  }

  /**
   *
   * @param array $filters fieldname => value pairs of filters
   * @return wa72SimpleDoctrineListBuilder
   */
  public function addFilters($filters) {
    foreach ($filters as $filterfield => $value) {
      $this->q->addWhere('o.' . $filterfield  . ' = ?', $value);
    }
    return $this;
  }

  /**
   * set the fields that should be searched by full text search
   *
   * @param array $fieldnames
   * @return wa72SimpleDoctrineListBuilder
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
    if (!$this->searchfields) throw new Exception('wa72SimpleDoctrineListBuilder: no searchfields');
    $where = array();
    $values = array();
    foreach ($this->searchfields as $field) {
      $where[] = 'o.' . $field  . ' LIKE ?';
      $values[] = '%'. $searchterm . '%';
    }
    $this->q->addWhere('('  . join(' OR ', $where) .')', $values);
    return $this;
  }

  /**
   *
   * @param unknown_type $sort
   * @param unknown_type $dir
   * @return unknown_type
   */
  public function addSort($sort, $dir = null) {
    $this->q->addOrderBy('o.'.$sort . ($dir == 'DESC' ? ' DESC' : ' ASC'));
    return $this;
  }

  public function addLimit($offset = 0, $limit = false) {
    if (intval($offset)) $this->q->offset(intval($offset));
    if (intval($limit)) $this->q->limit(intval($limit));
    return $this;
  }

  public function count() {
    return $this->q->count();
  }

  public function execute() {
    return $this->q->execute();
  }
  /**
   * get the query object
   * @return Doctrine_Query
   */
  public function getQuery() {
    return $this->q;
  }

  public function _makeFieldArray($object, $fieldnames) {
    $r = array();
    foreach ($fieldnames as $fieldname) {
      if ($fieldname == '__string') {
        $r['__string'] = $object->__toString();
      } elseif ($fieldname == '__pk') {
        $r['__pk'] = $object->getId();
      } else {
        $r[$fieldname] = $object->get($fieldname);
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
}
