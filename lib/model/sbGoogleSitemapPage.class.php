<?php

/**
 * A google sitemap page
 * @package    sbApostropheSitemapsPlugin
 * @subpackage sbGoogleSitemap
 * @author     Giles Smith <tech@superrb.com>
 */
class sbGoogleSitemapPage 
{	
	protected $loc;
	protected $changeFreq;
	protected $priority;
	protected $lastMod;
	protected $defaults;
	protected $possibleFreqs;
	
	/**
	 * Constructs the sbGoogleSitemapPage object
	 * 
	 * @param string $domain
	 * @param string $slug
	 * @param boolean $isSecure
	 * @param float $priority
	 * @param datetimestamp $lastmod 
	 */
	public function __construct($domain = null, $slug = null, $isSecure = FALSE, $changeFreq = null, $priority = null, $lastmod = null) 
	{
		$this->setDefaultValues();
		
		// construct uri
		if($isSecure){ $this->loc = 'https://'; } else { $this->loc = 'http://'; }
		$this->loc .= $domain . $slug;
		
		if(isset($changeFreq) and in_array($changeFreq, $this->possibleFreqs))
		{
			$this->changeFreq = $changeFreq;
		}
		else
		{
			$this->changeFreq = $this->defaults['change_freq'];
		}
		
		if(isset($priority) and is_numeric($priority) and $priority >= 0 and $priority <= 1)
		{
			$this->priority = number_format($priority, 2);
		}
		else
		{
			$this->priority = $this->defaults['priority'];
		}
		
		// @TODO Insert datetime for modified
		if(isset($lastmod) and is_numeric($lastmod))
		{
			try 
			{
				$this->lastmod = date('c', $lastmod);
			}
			catch (Exception $e)
			{
				$this->lastmod = date('c', $this->defaults['lastmod']);
			}
		}
		else
		{
			$this->lastmod = date('c', $this->defaults['lastmod']);
		}
	}
	
	/**
	 * Returns $this->loc
	 * @return string 
	 */
	public function getLoc()
	{
		return $this->loc;
	}
	
	/**
	 * Returns $this->changeFreq
	 * @return string 
	 */
	public function getChangeFreq()
	{
		return $this->changeFreq;
	}
	
	/**
	 * Returns $this->priority
	 * @return float 
	 */
	public function getPriority()
	{
		return $this->priority;
	}
	
	/**
	 * Returns $this->lastmod
	 * @return timestamp 
	 */
	public function getLastmod()
	{
		return $this->lastmod;
	}
	
	/**
	 * Sets up all the default values
	 */
	protected function setDefaultValues()
	{
		$values = sfConfig::get('app_a_sbGoogleSitemap');
		$this->possibleFreqs = array('hourly', 'daily', 'weekly', 'monthly', 'yearly');
		
		if(isset($values['default_change_freq']) and in_array($values['default_change_freq'], $this->possibleFreqs))
		{
			$this->defaults['change_freq'] = $values['default_change_freq'];
		}
		else
		{
			$this->defaults['change_freq'] = 'monthly';
		}
		
		if(isset($values['default_priority']) and is_numeric($values['default_priority']) and $values['default_priority'] >= 0 and $values['default_priority'] <= 1)
		{
			$this->defaults['priority'] = $values['default_priority'];
		}
		else
		{
			$this->defaults['priority'] = 0.6;
		}
		
		if(isset($values['default_lastmod']) and is_numeric($values['default_lastmod']))
		{
			try 
			{
				$this->defaults['lastmod'] = $values['default_lastmod'];
			}
			catch (Exception $e)
			{
				$this->defaults['lastmod'] = strtotime('-1 week');
			}
		}
		else
		{
			$this->defaults['lastmod'] = strtotime('-1 week');
		}
	}
}