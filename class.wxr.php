<?php
class WXR_export {
	/**
	 * WordPress WXR Base Class
	 *
	 * This class provides the methods and framework necessary to write a semantic, WordPress compatible
	 * export file (WXR). It should be used  in conjunction with your own class that extends WXR_export
	 *
	 * @package WXR_export
	 * @author Aaron Brazell <aaron@technosailor.com>
	 * @version 1.0-beta
	 **/
	
	// Publicly accessible
	var $debug = false;
	var $version = '1.0-beta';
	var $export_filename;
	
	// Private properties
	var $settings;	
	var $items;
	var $categories;
	var $tags;
	
	/**
	 * Returns a standard XML Doctype
	 *
	 * @access public
	 * @return string
	 **/
	function define_doctype()
	{
		return '<?xml version="1.0" encoding="UTF-8"?>' ."\n";
	}

	/**
	 * Returns an RSS declaration with default WordPress namespaces
	 * You can add other namespaces, however it is not recommended
	 * because the WordPress _importer_ does not support additional
	 * RSS tags at this point.
	 *
	 * To add other namespaces pass in an array with the namespace
	 * as the key and the URL as the value. Example:
	 *
	 *   -- $wxr->settings->namespace = array( 'abc' => 'http://abc.xyz/path/1.0/' );
	 *
	 * @access public 
	 * @return string
	 **/	
	function define_namespace()
	{
		$defns = ($this->settings->namespace ) ? $this->settings->namespace : array();
		$wp_namespaces = '<rss version="2.0"' . "\n";
		$wp_namespaces .= "\t" . 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
		$wp_namespaces .= "\t" . 'xmlns:wfw="http://wellformedweb.org/CommentAPI/"' . "\n";
		$wp_namespaces .= "\t" . 'xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
		$wp_namespaces .= "\t" . 'xmlns:wp="http://wordpress.org/export/1.0/"' . "\n";
		
		if( !empty( $defns) )
		{
			foreach( $defns as $ns => $url )
				$wp_namespaces .= "\t" . 'xmlns:' . $ns . '="' . $url . '"' . "\n";
		}
		$wp_namespaces .= ' />' . "\n";
		return $wp_namespaces;
	}
	
	/**
	 * Returns the top "channel" xml block of an RSS feed with blog data
	 * This method is accessed privately
	 *
	 * @access private
	 * @param string $setting
	 * @return string
	 **/
	function define_channel( $settings )
	{
		$channel = '<channel>' . "\n";
		$channel .= "\t" . '<title>' . $settings->feed_title . '</title>' ."\n";
		$channel .= "\t" . '<link>' . $settings->feed_link . '</link>' . "\n";
		$channel .=	"\t" . '<description>' . $settings->feed_description . '</description>' . "\n";
		$channel .= "\t" . '<pubDate>' . $settings->feed_pubdate . '</pubDate>' ."\n";
		$channel .= "\t" . '<generator>' . $settings->generator . '</generator>' . "\n";
		$channel .= "\t" . '<language>' . $settings->language . '</language>' . "\n";
		$channel .= "\t" . '<wp:wxr_version>' . $this->version . '</wp:wxr_version>' . "\n";
		$channel .= "\t" . '<wp:base_site_url>' . $settings->feed_blogurl . '</wp:base_site_url>' . "\n";
		$channel .= "\t" . '<wp:base_blog_url>' . $settings->feed_blogurl . '</wp:base_blog_url>' . "\n";
		$channel .= "\t" . '<dc:creator>' . $settings->feed_author . '</dc:creator>' . "\n";
		$channel .= ($this->categories) ? $this->define_categories( $this->categories, false ) : '';
		$channel .= ($this->tags) ? $this->define_tags( $settings->tags ) : '';
		$channel .= '</channel>' . "\n";
		return $channel;
	}
	
	/**
	 * Returns the categories in an xml block
	 * This method is accessed privately
	 *
	 * @access private
	 * @param object $objectCategories
	 * @param boolean $item
	 * @return string
	 **/
	function define_categories( $objectCategories, $item = true )
	{
		$objectCategories = (array) $objectCategories;			
		if( $item )
		{
			$categorylist = '';
			foreach( $objectCategories as $category )
			{
				$categorylist .= "\t" . '<category>' ."\n";
				$categorylist .= "\t\t" . '<![CDATA[' . $category->cat_name . ']]>' ."\n";
				$categorylist .= "\t" . '</category>' ."\n";
				$categorylist .= "\t" . '<category domain="category" nicename="' .$category->cat_nicename . '">' ."\n";
				$categorylist .= "\t\t" . '<![CDATA[' . $category->cat_name . ']]> '."\n";
				$categorylist .= "\t" . '</category>' . "\n";
			}
		}
		else
		{
			foreach( $objectCategories as $category)
			{				
				$categorylist .= "\t" . '<wp:category>' . "\n";
				$categorylist .= "\t\t" . '<wp:category_nicename>' . $category->cat_nicename . '</wp:category_nicename>' . "\n";
				$categorylist .= "\t\t" . '<wp:category_parent>' . $category->category_parent . '</wp:category_parent>' . "\n";
				$categorylist .= "\t\t" . '<wp:cat_name><![CDATA[' . $category->cat_name . ']]></wp:cat_name>' . "\n";
				$categorylist .= "\t" . '</wp:category>' . "\n";
			}
		}
		return $categorylist;
	}

	/**
	 * Returns tags as an xml block
	 * This method is accessed privately
	 *
	 * @access private
	 * @param object $objectTags
	 * @param boolean $item
	 * @return string
	 **/
	function define_tags( $objectTags, $item = true)
	{
		$taglist = '';
		if( $item )
		{
			$taglist .= "\t" . '<category domain="tag">' ."\n";
			$taglist .= "\t\t" . '<![CDATA[' . $objectTags->tag_name . ']]>' . "\n";
			$taglist .= "\t" . '</category>' . "\n";
		}
		else
		{
			foreach( $objectTags as $tag )
			$taglist .= "\t" . '<wp:tag>' . "\n";
			$taglist .= "\t\t" . '<wp:tag_slug>' . $tag->slug . '</wp:tag_slug>' . "\n";
			$taglist .= "\t\t" . '<wp:tag_name><![CDATA[' . $tag->tag_name . ']]><wp:tag_name>' . "\n";
			$taglist .= "\t" . '</wp:tag>'; 
		}
	}

	/**
	 * Returns an item as an xml block
	 * This method is accessed privately
	 *
	 * @access private
	 * @param object $objectItems
	 * @return string
	 **/	
	function define_item( $objectItems )
	{
		foreach( $objectItems as $objectItem )
		{
			$objectItem = (object) $objectItem;
			$item = '<item>' . "\n";
			$item .= "\t" . '<title>' . $objectItem->title . '</title>' . "\n";
			$item .= "\t" . '<link>' . $objectItem->link .'</link>' . "\n";
			$item .= "\t" . '<pubDate>' . $objectItem->pubdate . '</pubDate>' . "\n";
			$item .= "\t" . '<dc:creator>' . $objectItem->author . '<dc:creator>' . "\n";
			$item .= (!empty($this->categories)) ? $this->define_categories( $objectItem->categories, true ) : '';
			$item .= (!empty($this->tags)) ? $this->define_post_tags( $objectItem->tags, true ) : '';
			$item .= "\t" . '<guid isPermalink="false">' . $objectItem->guid .'</guid>' . "\n";
			$item .= "\t" . '<description></description>' . "\n";
			$item .= "\t" . '<content:encoded><![CDATA[' . $objectItem->content . ']]></content:encoded>' . "\n";
			$item .= "\t" . '<wp:post_date>' . $objectItem->post_date . '</wp:post_date>' ."\n";
			$item .= "\t" . '<wp:post_date_gmt>' . $objectItem->post_date_gmt. '</wp:post_date_gmt>' . "\n";
			$item .= "\t" . '<wp:comment_status>' . $objectItem->comment_status . '</wp:comment_status>' . "\n";
			$item .= "\t" . '<wp:ping_status>' . $objectItem->ping_status . '</wp:ping_status>' . "\n";
			$item .= "\t" . '<wp:post_name>' . $objectItem->slug . '</wp:post_name>' . "\n";
			$item .= "\t" . '<wp:status>' . $objectItem->post_status . '</wp:status>' ."\n";
			$item .= "\t" . '<wp:post_type>' . $objectItem->post_type . '</wp:post_type>' . "\n";
			$item .= $this->define_comments( $objectItem->comments );
			$item .= '</item>' . "\n";
			$xml .= $item;
		}
		return $xml; 
	}

	/**
	 * Returns comments as an xml block
	 * This method is accessed privately
	 *
	 * @access private
	 * @param object $objectComments
	 * @return string
	 **/	
	function define_comments( $objectComments )
	{
		$object = (array) $objectComments;
		if( array_key_exists( 'scalar', $object ) )
			return false;

		foreach( $object as $objectComment )
		{
			$objectComment = (object) $objectComment; 
			$comment = "\t" . '<wp:comment>' . "\n";
			$comment .=	"\t\t" . '<wp:comment_id>' . $objectComment->comment_id . '</wp:comment_id>' . "\n";
			$comment .= "\t\t" . '<wp:comment_author>' . $objectComment->comment_author . '</wp:comment_author>' . "\n";
			$comment .= "\t\t" . '<wp:comment_author_email>' . $objectComment->comment_author_email . '</wp:comment_author_email>' . "\n";
			$comment .= "\t\t" . '<wp:comment_author_url>' . $objectComment->comment_author_url . '</wp:comment_author_url>' . "\n";
			$comment .= "\t\t" . '<wp:comment_author_IP>' . $objectComment->comment_author_IP . '</wp:comment_author_IP>' . "\n";
			$comment .= "\t\t" . '<wp:comment_date>' . $objectComment->comment_date . '</wp:comment_date>' . "\n";
			$comment .= "\t\t" . '<wp:comment_date_gmt>' . $objectComment->comment_date_gmt . '</wp:comment_date_gmt>' . "\n";
			$comment .= "\t\t" . '<wp:comment_content>' . $objectComment->comment_content . '</wp:comment_content>' . "\n";
			$comment .= "\t\t" . '<wp:comment_approved>' . $objectComment->comment_approved . '</wp:comment_approved>' . "\n";
			$comment .= "\t\t" . '<wp:comment_type>' . $objectComment->comment_type . '</wp:comment_type>' . "\n";
			$comment .= "\t\t" . '<wp:comment_parent>' . $objectComment->comment_parent . '</wp:comment_parent>' ."\n";
			$comment .= "\t" . '</wp:comment>' . "\n";
			$xml .= $comment;
		}
		return $xml;
	}
	
	/**
	 * Returns an RSS end tag
	 * This method is accessed privately
	 *
	 * @access private
	 * @return string
	 **/
	function define_endrss()
	{
		return '</rss>';
	}

	/**
	 * Renders the XML and sends to browser as download
	 * This method "fires" the XML creation process.
	 * Depending on whether $this->debug is set to true
	 * false, the output of this method will be output to
	 * the screen (debug) or into a downloadable WXR export
	 * file.
	 *
	 * @access public
	 * @return string
	 **/
	function do_xml()
	{
		$xml = $this->define_namespace();
		$xml .= $this->define_channel( $this->settings );
		$xml .= $this->define_item( $this->items );
		$xml .=$this->define_endrss();
		
		if( $this->debug )
		{
			header('Content-type: text/plain');
			echo $xml;
			exit;
		}
		else
		{
			header('Content-Description: File Transfer');
			header("Content-Disposition: attachment; filename=$this->export_filename");
			header('Content-type: text/xml; charset=UTF-8');
			echo $this->define_doctype();
			echo $xml;
			exit;
		}
	}
}

?>