<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PluginsbGoogleSitemapActions
 *
 * @author pureroon
 */
abstract class PluginsbGoogleSitemapActions extends BaseaActions
{
	public $sitemapPages = array();
	protected $defaultPriority = 0.8;
	protected $settings = null;
	protected $domain = null;
	protected $request = null;

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
		$this->request = $request;

		// set the domain
		$this->domain = $request->getHost();

		// add the homepage to the sitemap
		$this->createHomepage();

		// add all apostrophe pages
		$this->createSitemapPagesFromApostrophe();

		// add blog pages
		$this->createSitemapPagesFromBlog($request);

		// add events pages

		// add any custom plugin pages
		$this->createSitemapPagesFromPlugins($request);

		$this->outputPages = $this->sitemapPages;
	}
	
	protected function createSitemapPagesFromApostrophe()
	{
		// add all pages in the main root
		$root = aPageTable::retrieveBySlug('/');
		$this->createSitemapPagesFromPages($root->getTreeInfo());
		
		// find unpublished pages and add their children if they exist
		$pages = Doctrine_Core::getTable('aPage')->findByArchived(1);
		
		foreach($pages as $page)
		{
			$this->createSitemapPagesFromPages($page->getTreeInfo());
		}
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
			$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for('@a_blog_post?year=' . $post->getYear() . '&month=' . $post->getMonth() . '&day=' . $post->getDay() . '&slug=' . $post->getSlug()), $this->request->isSecure(), $ch, $pr, strtotime($post->getUpdatedAt()));
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
					$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for($p['slug']), $this->request->isSecure());
				}
			}
		}
	}

	/**
	 * Create the homepage
	 */
	protected function createHomepage()
	{
		$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, '/', $this->request->isSecure(), 'daily', 1, time());
	}
}

?>
