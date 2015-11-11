<?php

/**
 * Description of BaseClientConnector
 *
 * @author
 */
abstract class BaseClientConnector extends BaseComponent implements IClientConnector
{
	/**
	 * @access protected
	 * @var array 
	 */
	protected $_errors = array();
	
		/**
	 * Array of errors.
	 * 
	 * @return array
	 */
	public function errors()
	{
		return $this->_errors;
	}
}

?>
