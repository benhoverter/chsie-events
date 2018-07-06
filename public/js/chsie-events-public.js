/*

JavaScript for CHSIE Event Registration Plugin: Frontend Display (Shortcodes)

Version: 1.0.0

Author: Ben Hoverter
Author URI: http://benhoverter.com

*/
( function($) {
    //'use strict';

    $( document ).ready( function() {

        console.log( "chsie-events-public.js enqueued." );

        // ------------------ General function to make [Enter] on a focused element trigger a click. ACCESSIBILITY.

        function enterClick( e ) {
                if (e.which === 13) {
                    $( this ).click();
                }
        };

        // -------------------Tribe Event page modifications.

        // Move the Confirm RSVP button to the same row as the input fields.
        function moveRSVPconfirm() {

            if ( $( window ).width() > 595 ) {
                $( '#rsvp-now .tribe-events-tickets-rsvp .tribe-tickets-table tr.tribe-tickets-full-name-row' ).append( $( '#rsvp-now tr > td.add-to-cart' ) );

                $( '#rsvp-now tr > td.add-to-cart' ).attr( "rowspan", "2" ) ;

            } else {
                $( '#rsvp-now .tribe-events-tickets-rsvp tr.tribe-tickets-meta-row' ).next( 'tr' ).append( $( '#rsvp-now tr > td.add-to-cart' ) );

                $( '#rsvp-now tr > td.add-to-cart' ).attr( "rowspan", "1" ) ;
            }
        }

        moveRSVPconfirm();

        $( window ).resize( function() {
            moveRSVPconfirm();
        } );


        // Sets default username and email for event form in tandem with [event-registration] shortcode.
        var userName = $('#current-user').attr('data-user');
        var userMail = $('#current-user').attr('data-mail');
        var userID = $('#current-user').attr('data-id'); // Not currently used.

        $( "input#tribe-tickets-full-name" ).attr( "value", userName );
        $( "input#tribe-tickets-email" ).attr( "value", userMail );

        $( "#current-user" ).remove(); // Eliminates the data storage node for security.


        // Gets the current User Registration Info Page URL and sets the Tribe Events "View your RSVPs" link to it.
        var userRegPageURL = $( '#user-reg-page-url' ).attr( 'data-user-reg-page-url' );
        var userRegPageID = $( '#user-reg-page-url' ).attr( 'data-page-id' );

        var tribeRsvpDiv = $( '.tribe-link-view-attendee' );
        tribeRsvpDiv.find( 'a' ).attr( "href", userRegPageURL );

        $( "#user-reg-page-url" ).remove(); // Eliminates the data storage node.

        // Wrap the "Your Reservations" section in a div for formatting and move it above the calendar buttons.
        tribeRsvpDiv.wrap( "<div id='rsvp-wrapper' class='tribe-events-single-section tribe-events-event-meta primary tribe-clearfix'></div>");

        $( ".tribe-events-single-event-description.tribe-events-content" ).append( $( "#rsvp-wrapper" ) );


        // -------------------Handling for the Registration Form behaviors.
        $( "h2.tribe-events-tickets-title.tribe--rsvp" ).html( "Make a Reservation" );

        $( "table.tribe-events-tickets.tribe-events-tickets-rsvp tbody:first" ).before( "<thead><tr><th>Select<span id='radio-clear' tabindex='0'>Clear</span></th><th>Type</th><th>Available Spots</th><th>Description</th></tr></thead>" );

        $( "span#radio-clear" ).keyup( enterClick );

        $( "table.tribe-events-tickets.tribe-events-tickets-rsvp tbody tr" ).each( function() {
            var $this = $( this );

            // Insert the Radio buttons.
            var $tixQuantity = $this.find( "td.tribe-ticket.quantity");
            $tixQuantity.before( "<td><input class='ticket-radio' type='radio' name='event-registration'></input></td>" );

            // Create and fill a "tickets-remaining" <td> with the available quantity.
            var tixQuantityText = $tixQuantity.find( "span.tribe-tickets-remaining" ).html();
            $this.find( "td.tickets_description" ).before("<td class='tickets-remaining'></td>");
            $this.find( "td.tickets-remaining" ).html( tixQuantityText );

            // Add the "event-waitlist" class to any row with "waitlist" in its td.tickets_name.
            var tixNameText = $this.find( "td.tickets_name" ).html();

            if( tixNameText !== undefined ) {

                // Reservation or Waitlist?
                if ( tixNameText.search( /waitlist/i ) === -1 ) { // Case-insensitive.
                    $this.addClass( "reservation-ticket" );
                    //console.log(tixNameText);

                } else {
                    $this.addClass( "waitlist-ticket" );
                    //console.log(tixNameText);
                }

                // Student or Facilitator or Other?
                if ( tixNameText.search( /student/i ) !== -1 ) { // Case-insensitive.
                    $this.addClass( "student-ticket" );
                    //console.log(tixNameText);

                } else if ( tixNameText.search( /facilitator/i ) !== -1 ) {
                    $this.addClass( "facilitator-ticket" );
                    //console.log(tixNameText);

                } else {
                    $this.addClass( "other-ticket" );
                    //console.log(tixNameText);
                }

            }  // END  if (tixName !== undefined)

        });  // END tr.each()

        // Show RSVP success messages correctly.
        if ( $( "div.tribe-link-view-attendee" ).length === 0 ) { // If the RSVP success message shows...
            $( "table.tribe-events-tickets.tribe-events-tickets-rsvp, h2.tribe-events-tickets-title.tribe--rsvp" ).show();
        } else {
            $( "div.tribe-rsvp-messages" ).show;

            // var rsvpTitle = $( "h2.tribe-events-tickets-title.tribe--rsvp" ).html();
            $( "div.tribe-link-view-attendee" ).before( "<h3 id='rsvp-title'>Your Reservations</div>");
        };

        // Logic to show or hide rows depending on whether or not any spots are left.
        var $noStockRows = $( "table.tribe-events-tickets span.tickets_nostock").parents( "tr" );
    //        var $waitlistRows = $( "table.tribe-events-tickets tr.waitlist-ticket");

        $noStockRows.hide();

        // Simple logic for rows without stock.
        if ( $( "tr.facilitator-ticket span.tickets_nostock" ).length !== 0 ) {
        //    console.log( "facilitator nostock is " + $( "tr.facilitator-ticket span.tickets_nostock" ) );
            $( "tr.facilitator-ticket.waitlist-ticket").show();
        }

        if ( $( "tr.student-ticket span.tickets_nostock" ).length !== 0 ) {
        //    console.log( "student nostock is " + $( "tr.student-ticket span.tickets_nostock" ) );
            $( "tr.student-ticket.waitlist-ticket").show();
        }

        if ( $( "tr.other-ticket span.tickets_nostock" ).length !== 0 ) {
            $( "tr.other-ticket.waitlist-ticket").show();
        }


        $( "input.ticket-radio" ).click( function() {
            var $this = $( this );
            $this.parents( "table.tribe-events-tickets-rsvp" ).addClass( "tribe-tickets-has-rsvp" );
            $this.parents( "tbody" ).first().find( "input.tribe-ticket-quantity" ).attr( "value", "0" );
            $this.parent( "td" ).next( "td" ).find("input.tribe-ticket-quantity").attr( "value", "1" );
        });

        $( "#radio-clear" ).click( function(){
            var $radioTable = $( this ).parents( "table" ).first();
            $radioTable.find( "input.tribe-ticket-quantity" ).val( "0" );
            $radioTable.find( "input.ticket-radio" ).prop( "checked", false );
            $( "table.tribe-events-tickets-rsvp" ).removeClass( "tribe-tickets-has-rsvp" );
            $( "div.tribe-rsvp-message-error" ).hide();
        });
        // ------------------------ End Registation Form behaviors.

    });

})(jQuery);
