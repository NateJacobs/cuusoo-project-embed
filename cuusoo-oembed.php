<?php

/**
 *	Plugin Name:	Cuusoo Project Embed
 *	Plugin URI:		https://github.com/NateJacobs/cuusoo-project-embed
 *	Description:	Paste the URL to a LEGO® Cuusoo Project into your post or page and the project information will be automatically displayed.
 *	Version: 		1.0
 *	Date:			3/23/13
 *	Author:      	Nate Jacobs 
 *
 */
 
/** 
*	Cuusoo Oembed
*
*	Add oembed support for LEGO(r) Cuusoo projects
*	Thanks to Lee Willis for the tutorial and code base to work from
*	https://github.com/leewillis77/wp-wpdotorg-embed
*
*	Notes for later: http://lego.cuusoo.com/api/participations/get/{projectID}.json
*
*	@author		Nate Jacobs
*	@date		3/23/13
*	@since		1.0
*/
class CuusooOembed
{
	/** 
	*	Initialize
	*
	*	Hook into WordPress and prepare all the methods as necessary.
	*
	*	@author		Nate Jacobs
	*	@date		3/23/13
	*	@since		1.0
	*
	*	@param		
	*/
	public function __construct()
	{
		add_action( 'init', array ( $this, 'register_oembed' ) );
		add_action( 'init', array ( $this, 'maybe_handle_oembed' ) );
	}
	
	/** 
	*	Register Oembed
	*
	*	Register the two URLS that support will be created for
	*
	*	@author		Nate Jacobs
	*	@date		3/23/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function register_oembed()
	{
		$oembed_url = home_url();
		$key = $this->get_key();
		
		// Create our own oembed url
		$oembed_url = add_query_arg ( array ( 'cuusoo_oembed' => $key ), $oembed_url);
		
		// Add support for Cuusoo
		wp_oembed_add_provider( 'http://lego.cuusoo.com/ideas/view/*', $oembed_url ); 
	}
	
	/** 
	*	Get Key
	*
	*	Create a random key to prevent hijacking
	*
	*	@author		Nate Jacobs
	*	@date		3/23/13
	*	@since		1.0
	*
	*	@param		null
	*
	*	@return		string	$key
	*/
	private function get_key() 
	{

		$key = get_option ( 'cuusoo_oembed_key' );

		if ( !$key ) {
			$key = md5 ( time() . rand ( 0,65535 ) );
			add_option ( 'cuusoo_oembed_key', $key, '', 'yes' );
		}

		return $key;

	}
	
	/** 
	*	Maybe Handle Oembed
	*
	*	Test if the correct key is present in the URL passed
	*
	*	@author		Nate Jacobs
	*	@date		3/23/13
	*	@since		1.0
	*
	*	@param		null
	*/
	public function maybe_handle_oembed() 
	{
		// If the query argument is there hand
		if ( isset ( $_GET['cuusoo_oembed'] ) ) 
		{
			// Hand it off to the handle_oembed function
			return $this->handle_oembed();
		}
	}
	
	/** 
	*	Handle Oembed
	*
	*	Takes care of displaying the embeded iframe from Cuusoo.
	*
	*	@author		Nate Jacobs
	*	@date		3/23/13
	*	@since		1.0
	*
	*	@param		null
	*/	
	public function handle_oembed() 
	{  
		// Did we get here by mistake?
	    if ( ! isset ( $_GET['cuusoo_oembed'] ) ) 
	    {  
	    	// If so, get out of here
	        return;  
	    }  
	    // Check this request is valid  
	    if ( $_GET['cuusoo_oembed'] != $this->get_key() ) 
	    {  
	        header ( 'HTTP/1.0 403 Forbidden' );  
	        die ( 'Forbidden.' );  
	    }  

	    // Check we have the required information  
	    $url = isset ( $_REQUEST['url'] ) ? $_REQUEST['url'] : null;  
	    $format = isset ( $_REQUEST['format'] ) ? $_REQUEST['format'] : null;

	    if( !empty ( $format ) && $format != 'json' ) 
	    {
			header( 'HTTP/1.0 501 Not implemented' );
			die( 'Only json allowed' );
		}

	    // Check if URL passed contains the proper format
	    if( preg_match( '#https?://lego.cuusoo.com/ideas/view/([^/]*)/?$#i', $url, $matches ) )
	    {
	    	// Build the Oembed class
		    $response = new stdClass();  
			$response->type = 'rich';  
			$response->width = '10';  
			$response->height = '10';  
			$response->version = '1.0';  
			$response->title = 'Cuusoo Project- '.$matches[1];
			$response->html = '<div class="cuusoo-oembed-project">';
			$response->html .= "<iframe scrolling='no' marginwidth='0' marginheight='0' hspace='0' align='middle' frameborder='0' src='http://lego.cuusoo.com/embedded/idea/{$matches[1]}' height='115' width='100%'></iframe>";
			$response->html .= '</div>';
	
			header ( 'Content-Type: application/json' );  
			echo json_encode ( $response );  
			die();
	    }
	    else
	    {
		    header ( 'HTTP/1.0 404 Not Found' );
			die( 'Invalid oembed' );
	    }
    }
}

$cuusoo_oembed = new CuusooOembed();