<?php
/**
 * CronJobberPHP (http://github.com/CoreyLoose/CronJobberPhp)
 *
 * Licensed under The Clear BSD License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Corey Losenegger (http://coreyloose.com)
 * @license Clear BSD (http://labs.metacarta.com/license-explanation.html)
 */
class CronJobberPhp_Job
{
	private $_timeStr;
	private $_cmd;
	
	const SECONDS_MOD_H = '* 60 * 60';
	const SECONDS_MOD_M = '* 60';
	
	private $_timeMode;
	const TIME_MODE_EXPLICIT = 'TIME_MODE_EXPLICIT';
	const TIME_MODE_INTERVAL = 'TIME_MODE_INTERVAL';
	
	private $_timestampToRun;
	private $_currentTime;	
	private $_lastRun;
	private $_secondsMod;
	
	private $_wasRun = false;
	
	public function __construct( $jobFileLine, $currentTime )
	{
		$this->_currentTime = $currentTime;
	
		$firstSpaceLocation = strpos($jobFileLine, ' ');
		$this->_timeStr = substr($jobFileLine, 0, $firstSpaceLocation);
		$this->_cmd = substr($jobFileLine, $firstSpaceLocation);
				
		$this->_parseTimeStr();
	}
	
	public function run()
	{
		echo 'Running: ',$this->_cmd,"\n";
		exec($this->_cmd);
	}
	
	public function shouldRun()
	{
		if( $this->_lastRun === null ) return true;
		
		if( $this->_timeMode == self::TIME_MODE_INTERVAL ) {
			$this->_timestampToRun = $this->_lastRun + $this->_secondsMod;
		}
		
		if( $this->_timestampToRun > $this->_lastRun
		    && $this->_currentTime > $this->_timestampToRun )
		{
			return true;
		}
		
		return false;
	}
	
	public function getCmd()
	{
		return $this->_cmd;
	}
	
	public function setLastRun( $time )
	{
		$this->_lastRun = $time;
	}
	
	public function getLastRun()
	{
		return $this->_lastRun;
	}
	
	public function __toString()
	{
		return $this->_timeStr.' '.$this->_cmd;
	}
	
	public function getHash()
	{
		return md5($this->__toString());
	}
	
	protected function _parseTimeStr()
	{
		if( preg_match('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', $this->_timeStr) )
		{
			$this->_timeMode = self::TIME_MODE_EXPLICIT;
			$this->_timestampToRun = strtotime(date('Y-m-d').' '.$this->_timeStr);
		}
		else if( preg_match('/[0-9]+[M|H|m|h]/', $this->_timeStr) )
		{
			$this->_timeMode = self::TIME_MODE_INTERVAL;
			
			$number = (int)substr($this->_timeStr, 0, strlen($this->_timeStr)-1);
			$letter = strtolower(substr($this->_timeStr, -1));
			
			//Turn H or M into a multiplication statement and eval it
			$letterMod = self::SECONDS_MOD_M;
			if( $letter == 'h' ) {
				$letterMod = self::SECONDS_MOD_H;
			}
			eval('$this->_secondsMod = '.$number.$letterMod.';');			
		}
		else
		{
			throw new Exception("Unknown time format '".$this->_timeStr."'");	
		}
	}
}