<?php
/**
 * 
 * a actions.
 * @package    sbApostropheSitemapsPlugin
 * @subpackage sbGoogleSitemap
 * @author     Giles Smith <tech@superrb.com>
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
		sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
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
		
		// add events pages
		
		// add any custom plugin pages
		$this->createSitemapPagesFromPlugins($request);
		
		$this->outputPages = $this->sitemapPages;
	}
	
	protected function createSitemapPagesFromPlugins($request)
	{
		if(isset($this->settings['plugin_models']) and is_array($this->settings['plugin_models']))
		{
			foreach($this->settings['plugin_models'] as $model => $method)
			{
				$this->sitemapPages = array_merge($this->sitemapPages, call_user_func($model . 'Table::' . $method));
			}
		}
	}
	
	/**
	 * Generate the urls for the blog pages
	 */
	protected function createSitemapPagesFromBlog()
	{
		// if not required return
		if(!isset($this->settings['blog_pages']) or $this->settings['blog_pages'] != 'true'){ return; }
		
		// get all blog pages
		$this->blogPosts = aBlogPostTable::getInstance()->findByStatus('published');
		
		// get blog page priority
		if(isset($this->settings['blog_page_priority'])){ $pr = $this->settings['blog_page_priority']; } else { $pr = null; }
		if(isset($this->settings['blog_page_change_freq'])){ $ch = $this->settings['blog_page_change_freq']; } else { $ch = null; }
		
		foreach($this->blogPosts as $post)
		{
			$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for('@a_blog_post?year=' . $post->getYear() . '&month=' . $post->getMonth() . '&day=' . $post->getDay() . '&slug=' . $post->getSlug()), FALSE, $ch, $pr, strtotime($post->getUpdatedAt()));
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
					$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for('@a_page?slug=' . substr_replace($p['slug'], '', 0, 1)), $secure);
				}
			}
		}
	}
	
	/**
	 * Create the homepage
	 */
	protected function createHomepage()
	{
		$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, '/', FALSE, 'daily', 1, time());
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
		//$page = new stdClass();
					
		

		//$this->sitemapPages[] = $page;
	}
}
