<?php
/**
 * Used to interact with the OpenStreemMap Nominatim service.
 * 
 * @author Daniel Milner
 * @version 1.0.0
 * @updated 2015-01-12
 */
class FireTree_Geocoder {

	private $nominatim_url = 'http://nominatim.openstreetmap.org/search/';
	private $transient_prefix;
	
	/**
     * Create a new instance
	 *
     * @param	array	$args	An array of the arguments.
     */
    function __construct( $args ) {
	
		$defaults = array(
			'transient_prefix'	=> 'firetree_',	// Prefix to save transients with.
		);
		
		$args = wp_parse_args( $args, $defaults );
		
        $this->transient_prefix		= $args['transient_prefix'];
		
    }
	
	/**
	 * GET coordinates from OpenStreetMap Nominatim service.
	 *
	 * @since 1.0.0
	 *
	 * @return	object	An object containing the data.
	 */
	public function get( $args ) {
	
		$defaults = array(
			'format'			=> 'json',	// html, xml, json, jsonv2
			'query'				=> '',		// Address to search for.
			'addressdetails'	=> 0, 		// Include a breakdown of the address into elements.
			'limit'				=> 1,		// Limit the number of returned results.
		);
		
		$args = wp_parse_args( $args, $defaults );
		
		try {
		
			$url = $this->nominatim_url . rawurlencode( $args['query'] );
			
			if ( $args['format'] ) { $url .= '?format=' . $args['format']; }
			if ( $args['addressdetails'] ) { $url .= '&addressdetails=' . $args['addressdetails']; }
			if ( $args['limit'] ) { $url .= '&limit=' . $args['limit']; }
			
			$saved_response = get_transient( $this->transient_prefix . md5( $url ) );
			if ( false === $saved_response ) {
			
				$response = wp_remote_get( $url );
						
				if ( ! is_wp_error( $response ) && wp_remote_retrieve_body( $response ) != '[]' ) {
				
					set_transient( $this->transient_prefix . md5( $url ), wp_remote_retrieve_body( $response ), YEAR_IN_SECONDS );
					
					// Return the data
					return json_decode( wp_remote_retrieve_body( $response ) );
				
				} else {
				
					// Return the data
					return false;
				
				}
			
			} else {
			
				// Return the data
				return json_decode( $saved_response );
			
			}
		
		} catch ( Exception $e ) {
			
			// Return the data
			return false;
			
		}
	
	}

}