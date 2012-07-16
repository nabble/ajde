<?php

class Ajde_Crud_Options_List_View extends Ajde_Crud_Options
{	
	/**
	 *
	 * @return Ajde_Crud_Options_List 
	 */
	public function up($obj = false) {
		return parent::up($this);
	}
	
	// =========================================================================
	// Select functions
	// =========================================================================
			
	// =========================================================================
	// Set functions
	// =========================================================================
	
	public function getPage()				{ return parent::getPage(); }
	public function getPageSize()			{ return parent::getPageSize(); }	
	public function getSearch()				{ return parent::getSearch(); }
	public function getOrderBy()			{ return parent::getOrderBy(); }
	public function getOrderDir()			{ return parent::getOrderDir(); }
	public function getFilter()				{ return parent::getFilter(); }
	
	
	/**
	 * Sets the current page
	 * 
	 * @param integer $page
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function setPage($page) { return $this->_set('page', $page); }
	
	/**
	 * Sets the page size
	 * 
	 * @param integer $size
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function setPageSize($size) { return $this->_set('pageSize', $size); }
	
	/**
	 * Sets a search term
	 * 
	 * @param string $q
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function setSearch($q) { return $this->_set('search', $q); }
	
	/**
	 * Sets the ordering field
	 * 
	 * @param string $orderBy
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function setOrderBy($orderBy) { return $this->_set('orderBy', $orderBy); }
	
	/**
	 * Sets the ordering direction
	 * 
	 * @param enum $dir (Ajde_Query::ORDER_ASC|Ajde_Query::ORDER_DESC)
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function setOrderDir($dir) { return $this->_set('dir', $dir); }
	
	/**
	 * Adds a filter
	 * 
	 * @param string $field
	 * @param string $value
	 * @return Ajde_Crud_Options_List_View 
	 */
	public function addFilter($field, $value) {
		$filter = $this->get('filter');
		$filter[$field] = $value;
		$this->set('filter', $filter);
		return $this;
	}
}