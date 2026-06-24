/* global jQuery */
( function ( $ ) {
	'use strict';

	$( function () {

		// Initialize WordPress color pickers.
		$( '.wppw-color-picker' ).wpColorPicker();

		// Font Awesome quick-pick tiles.
		$( document ).on( 'click', '.wppw-fa-tile', function () {
			var $tile  = $( this );
			var faClass = $tile.data( 'fa' );

			// Update hidden input and mark active tile.
			$( '#wppw_btn_icon_fa' ).val( faClass ).trigger( 'input' );
			$( '.wppw-fa-tile' ).removeClass( 'wppw-fa-tile--active' );
			$tile.addClass( 'wppw-fa-tile--active' );
		} );

		// Live preview when typing a class manually.
		$( document ).on( 'input', '#wppw_btn_icon_fa', function () {
			var val     = $( this ).val().trim();
			var $preview = $( '.wppw-fa-live-preview' );

			$preview.empty();
			if ( val ) {
				$preview.html( '<i class="' + val + '"></i>' );
			}

			// Sync active state in the quick-pick grid.
			$( '.wppw-fa-tile' ).each( function () {
				$( this ).toggleClass( 'wppw-fa-tile--active', $( this ).data( 'fa' ) === val );
			} );
		} );

	} );

}( jQuery ) );
