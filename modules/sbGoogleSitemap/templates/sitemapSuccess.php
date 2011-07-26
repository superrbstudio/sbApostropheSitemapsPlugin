<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
  <?php foreach($outputPages as $page): ?>  
	<url>  
		<loc><?php echo $page->getLoc(); ?></loc>  
		<changefreq><?php echo $page->getChangeFreq(); ?></changefreq>
		<priority><?php echo $page->getPriority(); ?></priority> 
		<lastmod><?php echo $page->getLastmod(); ?></lastmod> 
	</url>  
  <?php endforeach ?>  
</urlset>  
