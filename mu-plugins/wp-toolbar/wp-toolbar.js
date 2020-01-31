(function( $ ) {
	var $doc = $( document );
	$doc.ready( function(){
		var superadminbar = $( '#superadminbar' );

		/*
		 Adds ability to toggle all menus of super admin bar except the toggle button.
		 Useful for minimizing the space the super admin bar takes up without completely hiding it.
		 */
		superadminbar.on( 'click', '#wp-admin-bar-a11n-toggle', function(){
			var noticon     = $( this ).find( '.noticon' ),
				toggleState = 'closed';

			noticon.toggleClass( 'noticon-wordpress' ).toggleClass( 'noticon-minus' );
			superadminbar.toggleClass( 'toggle-closed' );

			if ( noticon.hasClass( 'noticon-minus' ) ) {
				toggleState = 'open';
			}

			$.ajax({
				url: a8cToolbar.ajaxurl,
				type: 'POST',
				data: {
					action:      'set_superadmin_toggle',
					nonce:       a8cToolbar.nonce,
					toggleState: toggleState
				},
				xhrFields: { withCredentials: true }
			});
		});

		/*
		 Allow the ability to toggle the entire super admin bar by using `Shift + w`
		 Should allow HEs to more easily take screenshots.
		 */
		$doc.on( 'keydown', function( e ){
			if ( ! $( e.target ).is( ':input' ) ) {
				if ( 87 == e.which && e.shiftKey ) {
					superadminbar.toggle();
				}
			}
		});
	});

})( jQuery );
