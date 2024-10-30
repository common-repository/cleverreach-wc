/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

/**
 * Gets support console.
 */
function getSupportConsole() {
	const displayParamsUrl = document.getElementById( 'crDisplayParamsUrl' ).value;
	fetch( displayParamsUrl )
		.then( response => response.json() )
		.then( data => console.log( data ) );
}

/**
 * Post support console.
 *
 * @param data
 */
function postSupportConsole(data) {
	const updateParamsUrl = document.getElementById( 'crUpdateParamsUrl' ).value;
	fetch(
		updateParamsUrl,
		{
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify( data ),
		}
	)
		.then( response => response.json() )
		.then( data => console.log( data ) );
}
