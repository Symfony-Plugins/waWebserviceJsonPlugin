<?php

interface wa72SimpleListBuilder {

	public function __construct($tableClassName);

	/**
	 *
	 * @param array $filters fieldname => value pairs of filters
	 * @return wa72SimpleListBuilder
	 */
	public function addFilters($filters);

	/**
	 * set the fields that should be searched by full text search
	 *
	 * @param array $fieldnames
	 * @return wa72SimpleListBuilder
	 */
	public function setSearchFields($fieldnames);

	/**
	 * 
	 * 
	 * @param string $searchterm
	 * @return wa72SimpleListBuilder
	 */
	public function addSearch($searchterm);
	
	/**
	 *
	 * @param string $sort
	 * @param string $dir
	 * @return wa72SimpleListBuilder
	 */
	public function addSort($sort, $dir = null);
	public function addLimit($offset = 0, $limit = false);
	public function count();
	public function execute();
	public function _makeFieldArray($object, $fieldnames);
	public function getDefaultListFieldnames();
	public function getDefaultSearchFieldnames();
	public function getDefaultListFilters();
}
class PeerclassNotFoundException extends Exception {}
