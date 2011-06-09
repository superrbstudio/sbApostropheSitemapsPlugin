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
	public $sitemapPages = array();
	protected $defaultPriority = 0.8;
	protected $settings = null;
	protected $domain = null;
	
	/**
	 * Execute the sitemap action
	 * 
	 * @param sfWebRequest $request
	 */
	public function executeSitemap(sfWebRequest $request)
	{	
		$this->settings = sfConfig::get('app_a_sbGoogleSitemap');
		$this->getResponse()->setContentType('text/xml'); 
		
		// set the domain
		$this->domain = $request->getHost();
		
		// add the homepage to the sitemap
		$this->createHomepage();
		
		// add all apostrophe pages
		$root = aPageTable::retrieveBySlug('/');
		$this->createSitemapPagesFromPages($root->getTreeInfo());
		
		// add blog pages
		$this->createSitemapPagesFromBlog($request);
		
		$this->outputPages = $this->sitemapPages;
	}
	
	/**
	 * 
	 */
	protected function createSitemapPagesFromBlog($request)
	{
		// if not required return
		if($this->settings['blog_pages'] != 'true'){ return; }
		
		// get all blog pages
		$this->blogPosts = aBlogPostTable::getInstance()->findByStatus('published');
		aBlogItemTable::populatePages($this->blogPosts);
		
		foreach($this->blogPosts as $post)
		{
			$this->createPage($post->getEngineSlug() . '/' . $post->getYear() . '/' . $post->getMonth() . '/' . $post->yetDay() . '/' . $post->getSlug(), FALSE, array('priority_setting_name' => 'blog_page_priority', 'lastmod' => strtotime($post->getUpdatedAt())));
		}
	}
	
	/**
	 * Turn page tree into page objects for xml
	 * 
	 * @param array $array Apostrophe page tree
	 */
	protected function createSitemapPagesFromPages($array)
	{	
		foreach($array as $p)
		{
			if(isset($p['children']) and is_array($p['children']))
			{
				$this->createSitemapPagesFromPages($p['children']);
			}
			else
			{	
				if($p['view_guest'] == 1)
				{
					if($p['view_is_secure'] == 1) { $secure = TRUE; } else { $secure = FALSE; }
					$this->createPage($p['slug'], $secure, array('priority_setting_name' => 'priority'));
				}
			}
		}
	}
	
	/**
	 * Create the homepage
	 */
	protected function createHomepage()
	{
		$this->createPage('/', FALSE, array('priority_setting_name' => 'homepage_priority'));
	}
	
	/**
	 * Create pages for the pages array
	 * 
	 * @param string $slug
	 * @param boolean $isSecure
	 * @param array $params 
	 */
	protected function createPage($slug, $isSecure = FALSE, $params = array())
	{
		$page = new stdClass();
					
		// is secure
		if($isSecure){ $page->uri = 'https://'; } else { $page->uri = 'http://'; }
		
		// construct uri
		$page->uri .= $this->domain . $slug;
		
		// set the priority if it is available
		if(is_numeric($this->settings[$params['priority_setting_name']]) and $this->settings[$params['priority_setting_name']] >= 0 and $this->settings[$params['priority_setting_name']] <= 1)
		{
			$page->priority = $this->settings[$params['priority_setting_name']];
		}
		else
		{
			$page->priority = $this->defaultPriority;
		}
		
		// @TODO Insert datetime for modified
		if(isset($params['lastmod']) and is_numeric($params['lastmod']))
		{
			try 
			{
				$page->updated = date('c', $params['lastmod']);
			}
			catch (Exception $e)
			{
				$page->updated = date('c');
			}
		}
		else
		{
			$page->updated = date('c');
		}

		$this->sitemapPages[] = $page;
	}
}
