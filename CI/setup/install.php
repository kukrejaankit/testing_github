<?php
/**
 * class CHARGEPOINT_INSTALLATION
 * 
 * @author: Ankit
 * 
 * This class helps set the basic constants for any enviornment. This is 
 * targetted for local deployemnts where user need not to go and modify various configurations localted at 
 * different locations and applications.
 * 
 * Source for configuration: setup.ini, should be located in same directory as this file.
 * 
 */
Class CHARGEPOINT_INSTALLATION
{

	private $result = NULL;
	private $base_path = '';
	private $confiuration = NULL;

	function __construct()
	{
		$this->result = array();
		$this->base_path = realpath(__DIR__);
		$this->confiuration = array();
	}
	
	/**
	 * @method readIni
	 * @author Ankit
	 * 
	 * Reads setup.ini and generates the constant files for all 3 applications
	 * BL, UI and gatewayserver.
	 * 
	 */
	function readIni()
	{
		$file_path = $this->base_path;

		$this->configuration = parse_ini_file($file_path .DIRECTORY_SEPARATOR.'setup.ini', true);
		$this->result = array();
		
		// Read Data from various sections.
		$this->readDBInfo();
		$this->readServerInfo();
		$this->readChargePointConfig();
		
		// Set correct path(s) to for applicaiton document root.
		$this->setPathInfo();
		
		// Now generate constant file.
		$this->GenerateConstantfile();
	}
	
	function readDBInfo()
	{
		$db = $this->configuration['DB'];

		if (!empty($db))
		{
			$this->result['DB_HOST_NAME']		= !empty($db['dbhost']) ? $db['dbhost'] : '';
			$this->result['DB_USER_NAME']		= !empty($db['username']) ? $db['username'] : '';
			$this->result['DB_PASSWORD']		= !empty($db['password']) ? $db['password'] : '';
			$this->result['DB_DATABASE_NAME']	= !empty($db['database']) ? $db['database'] : '';
		}		
	}


	function readServerInfo()
	{
		$server = $this->configuration['server'];

		if (!empty($server))
		{
			$this->result['ACTIVEMQ_SERVER_IP']		= !empty($server['activemq_ip']) ? $server['activemq_ip'] : '';
			$this->result['API_SERVER_NAME']		= !empty($server['api_server_name']) ? $server['api_server_name'] : '';
		}		
	}
	
	function setPathInfo()
	{
		$this->result['CPS_BASE_PATH'] = dirname($this->base_path);
		
		if (!empty($this->result['CPS_BASE_PATH']))
		{
			$this->result['COULOMB_EXECUTE_PHP_PATH']	= $this->result['CPS_BASE_PATH'].DIRECTORY_SEPARATOR.'gatewayserver'.DIRECTORY_SEPARATOR;
			$this->result['GW_BASE_PATH']				= $this->result['COULOMB_EXECUTE_PHP_PATH'];
		}
	}
	
	function readChargePointConfig()
	{
		$chargepoint = $this->configuration['chargepoint'];

		if (!empty($chargepoint))
		{
			$this->result['COULOMB_DOMAIN_NAME']		= !empty($chargepoint['domain_name']) ? $chargepoint['domain_name'] : '';
			$this->result['SUB_DOMAIN_NAME']			= !empty($chargepoint['sub_domain']) ? $chargepoint['sub_domain'] : '';
			$this->result['COULOMB_SERVER_PROTOCOL']	= !empty($chargepoint['protocol']) ? $chargepoint['protocol'] : '';
			$this->result['CPS_REPLICATION']			= !empty($chargepoint['replication']) ? TRUE : FALSE;
			$this->result['USE_BATCH_HANDLING']			= !empty($chargepoint['batch_handling']) ? TRUE : FALSE;
			
			// Derived Parameters...
			$this->result['CPS_BASE_URL'] 				= $this->result['COULOMB_SERVER_PROTOCOL'].'://'.$this->result['SUB_DOMAIN_NAME'].'.'.$this->result['COULOMB_DOMAIN_NAME'];
			$this->result['BATCH_HANDLING_LIVE'] 		= $this->result['USE_BATCH_HANDLING'];
		}		
	}


	function GenerateConstantFile()
	{
		//print_r($this->result);
		if (!empty($this->result))
		{
			$doc_root = dirname($this->base_path);
			$file_content = " <?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ".PHP_EOL.PHP_EOL;
			
			$file_content .= "define('BASE_CONSTANT', TRUE);".PHP_EOL;
			foreach ($this->result as $key => $value) 
			{
				if (is_bool($value) === true)
				{
					if ($value === FALSE)
					{
						$file_content .= "define('$key', FALSE);".PHP_EOL;
					}
					else 
					{
						$file_content .= "define('$key', TRUE);".PHP_EOL;
					}
				}
				else
				{
					$file_content .= "define('$key', '".addslashes($value)."');".PHP_EOL;
				}
			}
			
			//file_put_contents($this->base_path.'/base_constant.php', $file_content);
			
			// BL Constant file.
			file_put_contents($doc_root.'/application/config/base_constant.php', $file_content);
			print('File Successfully generated... @ '.$doc_root.'/BL/application/config/base_constant.php'.PHP_EOL);

			// UI Constant file.
			//file_put_contents($doc_root.'/UI/application/config/base_constant.php', $file_content);
			//print('File Successfully generated... @ '.$doc_root.'/UI/application/config/base_constant.php'.PHP_EOL);

			// Gatewayserver Constant file.
			//file_put_contents($doc_root.'/gatewayserver/config/base_constant.php', $file_content);
			//print('File Successfully generated... @ '.$doc_root.'/gatewayserver/config/base_constant.php'.PHP_EOL);
			
			//print('File Successfully generated @ '.$this->base_path.'/base_constant.php');
		}
	}

}

$cp_installation = new CHARGEPOINT_INSTALLATION();
$cp_installation->readIni();
