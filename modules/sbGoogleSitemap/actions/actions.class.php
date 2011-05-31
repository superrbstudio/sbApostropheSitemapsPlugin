<?php
/**
 * 
 * a actions.
 * @package    apostrophe
 * @subpackage a
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class sbGoogleSitemapActions extends BaseaActions
{
	protected $pages = array();
	
	public function executeSitemap(sfWebRequest $request)
	{
		$this->getResponse()->setContentType('text/xml');  
		$root = aPageTable::retrieveBySlug('/');
		$this->createSitemapPages($root->getTreeInfo());
		
		var_dump($treeInfo);
	}
	
	protected function createSitemapPages($array)
	{
		var_dump($array);
		
		foreach($array as $p)
		{
			if(is_array($p))
			{
				$this->createSitemapPages($p);
			}
			else
			{
				//$url = 
			}
		}
	}
}
