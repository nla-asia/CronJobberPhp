<?php
class CronJobberPhp_CronJobber
{
	const JOB_FILE_NAME = 'jobs';
	const LOG_FILE_NAME = '.log';
	const LOCK_FILE_NAME = '.lock';
	
	private $_timeRun;
	
	private $_jobFileContents; //array
	private $_logFileContents; //array
	
	private $_jobs;
	
	public function __construct()
	{
		$this->_timeRun = time();
	}
	
	public function run()
	{
		$this->_tryGetLock();
		
		try
		{					
			$this->_loadJobFile();
			$this->_loadLogFile();
			
			$this->_parseJobs();
			$this->_parseLogs();		
			
			$this->_runJobs();
		}
		catch( Exception $e )
		{
			$this->_releaseLock();
			throw $e;
		}
		
		$this->_releaseLock();
	}
	
	protected function _runJobs()
	{		
		$logFileContents == '' ;
		
		foreach( $this->_jobs as $job )
		{
			if( $job->shouldRun() )
			{
				$job->run();
				$logFileEntry = $job->getHash().' '.$this->_timeRun;
			}
			else
			{
				$logFileEntry = $job->getHash().' '.$job->getLastRun();
			}
			
			
			if( $logFileContents != '' ) $logFileContents .= "\n";
			$logFileContents .= $logFileEntry;
		}
		
		file_put_contents(
			dirname(__FILE__).'/'.self::LOG_FILE_NAME,
			$logFileContents
		);
	}
	
	protected function _parseJobs()
	{
		$this->_jobs = array();
		
		foreach( $this->_jobFileContents as $jobLine )
		{
			$trimmedJobLine = trim($jobLine);
			if( $trimmedJobLine[0] == '#' ) continue;
			
			$newJob = new CronJobberPhp_Job($trimmedJobLine, $this->_timeRun);
			$newJobHash = $newJob->getHash();

			if( isset($this->_jobs[$newJobHash]) )
			{
				echo "Ignoring duplicate command '",$newJob,"'\n";
				continue;
			}
			
			$this->_jobs[$newJobHash] = $newJob;
		}
	}

	protected function _parseLogs()
	{
		foreach( $this->_logFileContents as $logLine )
		{
			$explodedLine = explode(' ', $logLine);
			if( isset($this->_jobs[$explodedLine[0]]) )
			{
				$this->_jobs[$explodedLine[0]]->setLastRun(trim($explodedLine[1]));
			}
		}
	}
	
	protected function _tryGetLock()
	{
		$fileToTry = dirname(__FILE__).'/'.self::LOCK_FILE_NAME;
		
		if( !file_exists($fileToTry) )
		{
			touch($fileToTry);
			return;
		}
		
		throw new Exception('Unable to get cron lock. Halting.');
	}
	
	protected function _releaseLock()
	{
		unlink(dirname(__FILE__).'/'.self::LOCK_FILE_NAME);
	}
	
	protected function _loadJobFile()
	{
		$fileToTry = dirname(__FILE__).'/'.self::JOB_FILE_NAME;
		
		if( file_exists($fileToTry) )
		{
			$this->_jobFileContents = file($fileToTry);
		}
		else
		{
			throw new Exception('Could not open job file: "'.$fileToTry.'"');
		}
	}
	
	protected function _loadLogFile()
	{
		$fileToTry = dirname(__FILE__).'/'.self::LOG_FILE_NAME;
		
		if( file_exists($fileToTry) )
		{
			$this->_logFileContents = file($fileToTry);
		}
		else
		{
			touch($fileToTry);
			$this->_logFileContents = array();
		}
	}
}