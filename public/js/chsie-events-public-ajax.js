/*

AJAX JavaScript for CHSIE Event Registration Plugin: Public Display

Version: 1.0.0

Author: Ben Hoverter
Author URI: http://benhoverter.com

*/
( function($) {

    'use strict';

    $( document ).ready( function() {
        console.log( "chsie-events-public-ajax.js enqueued." );

        // Fade in the UER table (base CSS set to opacity: 0, with transition.)
        $( '#uer-frame' ).css( 'opacity', '1' );

        // Bind the event handler to the delete button:
        bindDeleteAjax();

        // Rebind all handlers on Ajax finish:
        $( document.body ).on( 'post-load', function() {
            bindDeleteAjax();
        } );


        // Handler-binder for Delete button:
        function bindDeleteAjax() {
            $( '#uer-form a.delete-button' ).click( function( event ) {

                event.preventDefault();

                var $this = $( this );

                var $thisRow = $this.parents( 'tr' ).first();

                var thisEventName = $thisRow.find('td.rsvp-event a').html();

                var confirmed = confirm( "Are you sure you want to cancel your reservation for the " + thisEventName + "?\nYou will lose your spot at this event if you do." );

                if ( confirmed === true ) {

                    console.log( "Delete confirmed." );

                    var rsvpCode = $thisRow.find( 'td.rsvp-code' ).html();

                    // Run the Ajax call:
                    updateUER( rsvpCode );

                    console.log( "rsvpCode is " + rsvpCode + "." );

                }
            });
        }


        // Define the ajax function:
        function updateUER( rsvpCode ) {

            $.ajax({
                method: 'POST',
                url: ce_public_ajax_data.ajax_url, // Grab the url from the PHP ajax data object.
                data:
                {
                        action: 'ce_public',
                        ajax_nonce: ce_public_ajax_data.ajax_nonce,
                        update_ticket: rsvpCode, // No longer need old click() and hidden input.
                        uer_nonce: $( '#uer_nonce' ).val()
                },

                beforeSend: function() {

                    $( '#uer-frame' ).css( 'opacity', '0' );

                },

                success: function( html, status, jqXHR ) {

                    console.log( "ajax returned HTML of: " + html );
                    console.log( "ajax returned a status of: " + status + "." );
                    console.log( "ajax returned a jqXHR of: " + jqXHR + "." );

                    $( '#uer-frame' ).html( html ); // Use that return value as the HTML content

                    $( '#uer-frame' ).css( 'opacity', '1' );

                    $( document.body ).trigger( 'post-load' );

                }

            }); // END OF: $.ajax().

        }


    }); // END Of: document ready.
})(jQuery);
