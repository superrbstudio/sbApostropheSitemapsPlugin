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
    $this->getUser()->setFlash('aCacheInvalid', true);
		sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
		$this->settings = sfConfig::get('app_a_sbGoogleSitemap');
		$this->getResponse()->setContentType('application/xml');
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
    $this->createSitemapPagesFromEvents($request);

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
      try
      {
        $this->createSitemapPagesFromPages($page->getTreeInfo());
      } catch (Exception $e)
      {
        // something went wrong building the page tree for this page
      }
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
			$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for('a_blog_post', $post), $this->request->isSecure(), $ch, $pr, strtotime($post->getUpdatedAt()));
		}
	}
  
  /**
	 * Generate the urls for the blog pages
	 */
	protected function createSitemapPagesFromEvents()
	{
		// if not required return
		if(!isset($this->settings['events_pages']) or $this->settings['events_pages'] != 'true'){ return; }

		// get all blog pages
		$this->eventPosts = aEventTable::getInstance()->findByStatus('published');

		// get blog page priority
		if(isset($this->settings['events_page_priority'])){ $pr = $this->settings['events_page_priority']; } else { $pr = null; }
		if(isset($this->settings['events_page_change_freq'])){ $ch = $this->settings['events_page_change_freq']; } else { $ch = null; }

		foreach($this->eventPosts as $event)
		{
			$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for('a_event_post', $event), $this->request->isSecure(), $ch, $pr, strtotime($event->getUpdatedAt()));
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
			if($p['view_guest'] == 1)
			{
				$this->sitemapPages[] = new sbGoogleSitemapPage($this->domain, url_for($p['slug']), $this->request->isSecure());
			}
			if(isset($p['children']) and is_array($p['children']))
			{
				$this->createSitemapPagesFromPages($p['children']);
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
