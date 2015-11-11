<?php

/**
 * Description of BaseXmlFileConnector
 *
 * 
 */
abstract class BaseXmlFileConnector extends BaseConnector
{
	/**
	 * Path to xml file.
	 * 
	 * @access protected
	 * @var string
	 */
	protected $_path;
	
	/**
	 * Load file into SimpleXMLElement object.
	 * 
	 * @access protected
	 * @return null|SimpleXMLElement Null if loading failed.
	 */
	protected function loadXml()
	{
		// Check file existance.
		if (!file_exists($this->_path)) 
		{
			$this->errors[] = 'Can\'t find file '. $path;
			
			return null;
        }
		
		// Trying to load xml
		libxml_use_internal_errors(true);
		
        $xml = simplexml_load_file($path);
        
		// If loading failed
        if ($xml === false)
		{
			$errorMessage = 'Xml load errors: ';
			
            foreach(libxml_get_errors() as $error)
			{
                $errorMessage .=  $error->message ."\n";
            }
			
			$this->errors[] = $errorMessage;
			
			return null;
        }
		
		return $xml;
	}
	
	/**
	 * Checks xml objects.
	 * 
	 * @access protected
	 * @abstract 
	 * @param SimpleXMLElement $xml 
	 */
	abstract protected function validate($xml);
	
	/**
	 * Processes xml objects.
	 * 
	 * @access protected
	 * @abstract 
	 * @param SimpleXMLElement $xml 
	 */
	abstract protected function process($xml);
	
	/**
	 * Ctor.
	 * 
	 * @param ILogger $logger
	 * @param string $path Path to xml file
	 */
	public function __construct($logger, $path)
	{
		parent::__construct($logger);
		
		$this->_path = $path;
	}
	
	public function readData(&$output) 
	{
		parent::readData($output);
		
		$xml = $this->loadXml();
		
		if (is_null($xml))
		{
			$this->errors[] = 'Can\'t load xml file: '. $path;
			
			return false;
		}
		
		if ($this->validate($xml) === false)
		{
			$this->errors[] = 'Validation failed.';
			
			return false;
		}
		
		$this->process($xml);
		
		return true;
	}
	
	public function setParams()
	{
	
	}
}

?>
