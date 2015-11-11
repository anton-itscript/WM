<?php

/**
 * Description of TcpClientConnectorTests
 *
 * @author
 */
class TcpClientConnectorTests extends CTestCase
{
	protected static function createConnector()
	{
		$logger = LoggerFactory::getTestLogger();
		
		//return new TcpClientConnector($logger, 'tcp', '192.168.101.212', '1040');
		//return new TcpClientConnector($logger, 'tcp', 'localhost', '89');
		return new TcpClientConnector($logger, 'tcp', '192.168.101.191', '1039');
	}
	
	public function testSendMessage_OneConnectTry()
	{
		$connector = $this->createConnector();
		
		$this->assertTrue($connector->connect(3));				
		$this->assertTrue($connector->sendMessage('TestMessage'));
		$this->assertTrue($connector->disconnect());				
	}
	
	public function testSendMessage_SeveralConnectTries()
	{
		$connector = $this->createConnector();
		
		$this->assertTrue($connector->connect(3));				
		$this->assertTrue($connector->sendMessage('TestMessage1'));
		$this->assertTrue($connector->disconnect());
		
		$this->assertTrue($connector->connect(3));				
		$this->assertTrue($connector->sendMessage('TestMessage2'));
		$this->assertTrue($connector->disconnect());	
		
		$this->assertTrue($connector->connect(3));				
		$this->assertTrue($connector->sendMessage('TestMessage3'));
		$this->assertTrue($connector->disconnect());	
	}
	
	public function testSendMessage_SeveralConnectors_SeveralConnectTries()
	{
		$connector1 = $this->createConnector();
		$connector2 = $this->createConnector();
		
		$this->assertTrue($connector1->connect(3));				
		$this->assertTrue($connector2->connect(3));		
		$this->assertTrue($connector1->sendMessage('Connector1 TestMessage1'));
		$this->assertTrue($connector2->sendMessage('Connector2 TestMessage1'));		
		$this->assertTrue($connector1->disconnect());
		$this->assertTrue($connector2->disconnect());
		
		$this->assertTrue($connector1->connect(3));				
		$this->assertTrue($connector2->connect(3));		
		$this->assertTrue($connector1->sendMessage('Connector1 TestMessage2'));
		$this->assertTrue($connector2->sendMessage('Connector2 TestMessage2'));		
		$this->assertTrue($connector1->disconnect());
		$this->assertTrue($connector2->disconnect());
		
		$this->assertTrue($connector1->connect(3));				
		$this->assertTrue($connector2->connect(3));		
		$this->assertTrue($connector1->sendMessage('Connector1 TestMessage3'));
		$this->assertTrue($connector2->sendMessage('Connector2 TestMessage3'));		
		$this->assertTrue($connector1->disconnect());
		$this->assertTrue($connector2->disconnect());
	}
	
	public function testSendMessage_LongMessage()
	{
		// Message length > 1024
		$message = 
<<<TXT
The Christian by William Cowper
				
Honor and happiness unite
To make the Christian's name a praise;
How fair the scene, how clear the light,
That fills the remnant of His days!

A kingly character He bears,
No change His priestly office knows;
Unfading is the crown He wears,
His joys can never reach a close.

Adorn'd with glory from on high,
Salvation shines upon His face;
His robe is of the ethereal dye,
His steps are dignity and grace.

Inferior honors He disdains,
Nor stoops to take applause from earth;
The King of kings Himself maintains
The expenses of His heavenly birth.

The noblest creature seen below,
Ordain'd to fill a throne above;
God gives him all He can bestow,
His kingdom of eternal love!

My soul is ravished at the thought!
Methinks from earth I see Him rise!
Angels congratulate His lot,
And shout Him welcome to the skies.
TXT;
		
		
		$connector = $this->createConnector();
		
		$this->assertTrue($connector->connect(3));				
		$this->assertTrue($connector->sendMessage($message));
		$this->assertTrue($connector->disconnect());				
	}
}

?>
