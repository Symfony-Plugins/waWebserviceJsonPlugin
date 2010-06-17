<?php
/**
 * Class for building a basic record list from an sfWebRequest
 * <ul>
 *   <li>supports both Propel and Doctrine</li>
 *   <li>supports filters, search on multiple fields, sorting and limit set by request parameters</li>
 *   <li>returns the record list as native ORM resultset (propel or doctrine), as array of arrays or in JSON format
 * </ul>
 * <p>It takes an sfWebRequest as constructor argument and then creates the record resultset by building
 * the Criteria object (Propel) or Doctrine query object according to the following request parameters:</p>
 * <ul>
 *   <li><strong>object</strong>: The classname of the record objects to fetch</li>
 *   <li><strong>filter</strong>: array (fieldname => filtervalue) for record filtering, e.g. filter[category]=35</li>
 *   <li><strong>search</strong>: search term for searching in predefined search fields (set by method setSearchFields(array_of_fieldnames), e.g. 'title', 'description')</li>
 *   <li><strong>sort</strong>: The fieldname of the field for sorting, e.g. sort=title</li>
 *   <li><strong>dir</strong>: sorting direction, 'ASC' or 'DESC', default 'ASC'</li>
 *   <li><strong>offset</strong>: the first record to fetch</li>
 *   <li><strong>limit</strong>: max. number of records to fetch</li>
 *   <li><strong>fields</strong>: array of fieldnames to include in Array or JSON list, e.g. fields[]=title&fields[]=description</li>
 *   <li><strong>callback</strong>: For JSON format: if provided, the response array is wrapped with callback(...)</li>
 * </ul>
 *
 * @author Christoph Singer <singer@webagentur72.de>
 * @license MIT
 */
class wa72RequestListBuilder {
  /**
   * filters that should always be applied to the list, e.g. array('is_approved' => 1)
   * @var array
   */
  protected $defaultFilters;
  /**
   * @var wa72SimpleListBuilder
   */
  protected $ormListBuilder;
  /**
   * @var sfWebRequest
   */
  protected $request;

  /**
   * @param sfWebRequest $request
   * @param string $object_class Classname of model object
   */
  public function __construct(sfWebRequest $request, $object_class = null) {
    $this->request = $request;
    if (!$object_class) $object_class = $request->getParameter('object');
    if (false !== array_search('sfPropelPlugin',sfProjectConfiguration::getActive()->getPlugins())) {
      $this->ormListBuilder = new wa72SimplePropelListBuilder($object_class);
    } else if (false !== array_search('sfDoctrinePlugin',sfProjectConfiguration::getActive()->getPlugins())) {
      $this->ormListBuilder = new wa72SimpleDoctrineListBuilder($object_class);
    } else {
      throw new Exception('wa72RequestListBuilder: neither sfPropelPlugin nor sfDoctrinePlugin enabled');
    }
  }
  /**
   * add filters to the list, e.g. array('is_approved' => true)
   * @param array $filters array(fieldname => value)
   */
  public function addFilters($filters) {
    $this->ormListBuilder->addFilters($filters);
  }
  /**
   * set fields that should be searched when a free text search is requested, e.g. array('title', 'description')
   * @param array $fieldnames Array of fieldnames
   */
  public function setSearchFields($fieldnames) {
    $this->ormListBuilder->setSearchFields($fieldnames);
  }
  /**
   * get result list as objects
   */
  public function getObjectList() {
    if ($this->request->hasParameter('filter')) $this->ormListBuilder->addFilters($this->request->getParameter('filter'));
    if ($this->request->hasParameter('search')) $this->ormListBuilder->addSearch($this->request->getParameter('search'));
    if ($this->request->hasParameter('sort')) $this->ormListBuilder->addSort($this->request->getParameter('sort'), $this->request->getParameter('dir', null));
    if ($this->request->hasParameter('limit')) $this->ormListBuilder->addLimit($this->request->getParameter('offset', 0), $this->request->getParameter('limit'));
    return $this->ormListBuilder->execute();
  }
  /**
   * get result list as array of arrays
   */
  public function getArrayList() {
    $r = array();
    if ($this->request->hasParameter('fields') && is_array($this->request->getParameter('fields'))) {
      $fields = $this->request->getParameter('fields');
    } else {
      $fields = $this->ormListBuilder->getDefaultListFieldnames();
    }
    $list = $this->getObjectList();
    foreach ($list as $object) {
      $r['items'][] = $this->ormListBuilder->_makeFieldArray($object, $fields);
    }
    return $r;
  }
  /**
   * get result list in JSON format
   */
  public function getJsonList() {
    $r = json_encode($this->getArrayList());
    if ($this->request->hasParameter('callback')) {
      $r = $this->request->getParameter('callback') . '(' . $r . ');';
    }
    return $r;
  }
}