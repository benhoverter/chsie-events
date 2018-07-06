/*

JavaScript for CHSIE Event Registration Plugin Admin Metabox and Tribe RSVP Metabox

Version: 1.0.0

Author: Ben Hoverter
Author URI: http://benhoverter.com

*/
( function($) {
    'use strict';

    $(document).ready( function() {

        if ( $( '#event-materials-table' ).length === 0 ) {
            return;
        }

        console.log( "chsie-events-admin.js is enqueued.")

      // Set all variables to be used in scope
        var frame,
            metaBox = $('#event-materials.postbox'), // Your meta box id here
            addMaterialsButton = metaBox.find('#event-materials-upload a.button'),

            materialsTbody = $( '#event-materials-table > tbody' ),

            defaultMaterialsDescription = "Enter text here"; // Change in cer_functions.php too!

            // Remaining vars for each row are dynamically generated below.

        // Inital PHP-generated materials selections check:
        var $rowsToReorder = $( "#event-materials-table .event-materials-row" );   // Count the current rows.

        if ( $rowsToReorder.length === 0 ) { // If no materials are displayed, hide the column headings.
            $("#materials-headings").hide();

        } else {  // If there are materials, hide the "no materials" notice and disable the dummy input.
            $("#no-materials-selected").hide();
            $( "#materials-info-empty" ).prop("disabled" , true );
        }


        // Function to Attach Event Handler for Delete link in meta box.  Called once for PHP generated selections, once for JS selections.
        function attachDeleteFxn( $delButton ) {

            $delButton.on( "click" ,  function( event ) {

                event.preventDefault();

                var $thisRow = $delButton.parents("tr").first();

                var thisTitle = $thisRow.find("td.event-materials-title input").val();

                var deleteConfirm = confirm( "Are you sure you want to delete:  " + thisTitle + "  ?");

                if ( deleteConfirm === true ) {
                    $thisRow.remove();

                    // Count the current rows, then reassign indices in [name] and CSS row ids.
                    var $rowsToReorder = $( "#event-materials-table .event-materials-row" );

                    // Gets the WP Editor content to check for the shortcode.
                    if ( $( "#wp-content-wrap" ).hasClass( "tmce-active" ) ) {// If in Visual Editor.

                        var $editorBody = $( "iframe#content_ifr").contents().find("body");

                        var shortcodeHTML = "<p>[event-materials]</p>";

                    } else if ( $( "#wp-content-wrap" ).hasClass( "html-active" ) ) { // If in Text Editor.

                        var $editorBody = $( "#wp-content-editor-container #content");

                        var shortcodeHTML = "&lt;p&gt;[event-materials]&lt;/p&gt;";

                    }

                    var editorHTML = $editorBody.html(); // Might return 'undefined'.

                    //    console.log( editorHTML );

                    var editorHasShortcode = editorHTML.indexOf( shortcodeHTML ); // Returns -1 if false!

                    // The "No Materials" condition:
                    if ( $rowsToReorder.length === 0 ) {

                        // If no materials are selected, show the message and enable the "empty" input for db save.
                        $("#no-materials-selected").show();
                        $("#materials-headings").hide();
                        $( "#materials-info-empty" ).prop("disabled" , false );

                        // Handle the shortcode deletion, if it exists.
                        if ( editorHasShortcode !== -1 ) {

                                console.log( "Shortcode found, but no materials.  Removing shortcode." );

                            var editorHTMLarray = editorHTML.split( shortcodeHTML );

                            editorHTML = editorHTMLarray.join(' ');

                            $editorBody.html( editorHTML );
                        }

                    } else {  // The "Has materials" condition:
                        // console.log( "$rowsToReorder = " + $rowsToReorder.length );

                        // Disable the 'empty' input and loop over the current rows to reorder their indices.
                        for ( var i = 0 ; i < $rowsToReorder.length ; i++ ) {

                            var $rowToReorder = $( $rowsToReorder[ i ] );   // <tr>

                            var $rowTitleInput = $rowToReorder.find(".event-materials-title > input");    // <td><input>
                            var $rowDescriptionInput = $rowToReorder.find(".event-materials-description > input");    // <td><input>
                            var $rowURLInput = $rowToReorder.find(".event-materials-url > input");    // <td><input>
                            var $rowIDInput =    $rowToReorder.find("input.event-materials-id");       // <input>,  child of <tr>

                            // Reassign row id:
                            $rowToReorder.attr("id" , "events-materials-row-" + i );

                            // Reassignment of [name] attribute indices for $_POST save array:
                            $rowTitleInput.attr( "name" , "event_materials[" + i + "][title]" );
                            $rowDescriptionInput.attr( "name" , "event_materials[" + i + "][description]" );
                            $rowURLInput.attr( "name" , "event_materials[" + i + "][url]" );
                            $rowIDInput.attr( "name" , "event_materials[" + i + "][id]" );

                        } // End [name] reassignment loop.


                        // Handle the shortcode insertion, if necessary.
                        if ( editorHTML === undefined || editorHasShortcode === -1 ) {

                            $editorBody.append( shortcodeHTML );
                        }

                    } // End else

                    // Compare sessionStorage to current inputs and bindSaveAjax.
                    compareMatsToSession( $inputs );

                } // End if (deleteConfirm).
            }) // End on ('click').
        }  // End attachDeleteFxn()


        // Selects all rows whose ids end in "delete" from PHP-generated rows.
        var $deleteButtons = $("#event-materials-table  td.event-materials-delete > a");

        for( var i = 0 ; i < $deleteButtons.length ; i++ ) {

            let $delButton = $( $deleteButtons[ i ] );

            attachDeleteFxn( $delButton );

        } // End deleteButtons for loop.


        // ADD MEDIA LINK
        addMaterialsButton.on( 'click', function( event ){

            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( frame ) {
                  frame.open();
                  return;
            }

            // Create a new media frame
            frame = wp.media({
                  title: 'Select Media for This Event (ctrl-click to select multiple)',
                  button: {
                    text: 'Use this media'
                  },
                  multiple: true  // Set to true to allow multiple files to be selected
            });

            // When an image is selected in the media frame...
            frame.on( 'select', function() {

                // Get media 'selected' details from the frame state: SINGLE SELECTION
                // var selected = frame.state().get('selection').first().toJSON() ;

                // For multi-select, we convert to an array, then iterate across it with toJSON().
                var selections = frame.state().get('selection').toArray() ;

                for ( i = 0 ; i < selections.length ; i++ ) {

                    // Get an index based on the number of current media items (for proper numbering of CSS IDs, etc.)
                    var n = $("#event-materials-table .event-materials-row").length ;

                    selected = selections[i].toJSON() ;

                    // Build the table row with the selected media's attributes and an accurate id.
                    materialsTbody.append("<tr id='event-materials-row-" + n + "' class='event-materials-row'></tr>");

                    var $thisRow = materialsTbody.find( "tr").last();

                    // Insert the Title cell.
                    $thisRow.append('<td class="event-materials-title"> <input  class="event_materials" name="event_materials[' + n + '][title]" type="text" value="' + selected.title + '"   /> </td>');

                    //Insert the Description cell.
                    $thisRow.append( '<td class="event-materials-description"> <input class="event_materials" name="event_materials[' + n + '][description]" type="textarea" autocomplete="off" maxlength="400" placeholder="' + defaultMaterialsDescription + '" value="" /> </td>' );


                    // Insert the URL cell with hidden input.
                    $thisRow.append('<td class="event-materials-url" > <a href="' + selected.url + '" target="_blank">URL </a> <input  class="event_materials" name="event_materials[' + n + '][url]" type="hidden" value="' + selected.url + '"   /> </td>');

                    // Insert the Delete cell.
                    $thisRow.append('<td class="event-materials-delete"><a class="button" href="#">Delete</a></td>');

                    // Attach the delete event handler.
                    let $delButton = $thisRow.find(".event-materials-delete > a");
                    attachDeleteFxn( $delButton );

                    // Insert the ID as a hidden input.
                    $thisRow.append('<input class="event-materials-id event_materials" name="event_materials[' + n + '][id]" type="hidden" value="' + selected.id + '"   />');

                } //End of for loop.

                // Bind input handler to trigger sessionStorage comparison and AJAX button binding.
                var $inputs = $( '#event-materials-table input' );
                bindKeyupHandler( $inputs );
                // TODO: trigger bindAjaxHandler, because things have changed.
                compareMatsToSession( $inputs );

                // Handle the shortcode insertion, if necessary:
                // Gets the WP Editor content to check for the shortcode.
                if ( $( "#wp-content-wrap" ).hasClass( "tmce-active" ) ) {// If in Visual Editor.

                    var $editorBody = $( "iframe#content_ifr").contents().find("body");

                    var shortcodeHTML = "<p>[event-materials]</p>";

                } else if ( $( "#wp-content-wrap" ).hasClass( "html-active" ) ) { // If in Text Editor.

                    var $editorBody = $( "#wp-content-editor-container #content");

                    var shortcodeHTML = "&lt;p&gt;[event-materials]&lt;/p&gt;";

                }

                var editorHTML = $editorBody.html(); // Might return 'undefined'.

                var editorHasShortcode = editorHTML.indexOf( shortcodeHTML ); // Returns -1 if false!

                if ( editorHTML === undefined || editorHasShortcode === -1 ) {

                    $editorBody.append( shortcodeHTML );
                }

                // Hide and disable the "No selections" notice and dummy input.
                $("#no-materials-selected").hide();
                $("#materials-headings").show();
                $( "#materials-info-empty" ).prop("disabled" , true );

            });

            // Finally, open the modal on click
            frame.open();
        });


        // DRAG AND DROP FUNCTIONALITY

        // Enables JQ DnD functionality with the "sortstop" callback function initialized.
        $( "#event-materials-table tbody" ).sortable( {
            stop: function( event, ui ) {}
        } );

        $( "#event-materials-table tbody" ).on( "sortstop" , function( event, ui ) {

                var $rows = $( this ).find( "tr" );

                var i = 0;

                $rows.each( function() {
                    var $thisRow = $( this );

                    $thisRow.attr( "id" , "event-materials-row-" + i );

                    $thisRow.find( ".event-materials-title input" ).attr( "name" , "event_materials[" + i + "][title]");

                    $thisRow.find( ".event-materials-description input" ).attr( "name" , "event_materials[" + i + "][description]");

                    $thisRow.find( ".event-materials-url input" ).attr( "name" , "event_materials[" + i + "][url]");

                    $thisRow.find( "input.event-materials-id" ).attr( "name" , "event_materials[" + i + "][id]");

                    i++ ;
                });

                // Bind input handler to trigger sessionStorage comparison and AJAX button binding.
                var $inputs = $( '#event-materials-table input' );
                bindKeyupHandler( $inputs );
                // TODO: trigger bindAjaxHandler, because things have changed.
                compareMatsToSession( $inputs );

        }); // End on sortstop.


        // Insert user instructions into the Tribe Tickets metabox.
        // Specific naming conventions are necessary for Waitlist parsing.
        doWaitlistFunctions();

        function doWaitlistFunctions() {

            var $ticketTable = $( '#tribetickets.postbox #tribe_ticket_list_table' );

            // TODO: Establish a new MutationObserver on $ticketTable.

            $( '#event-materials' ).append( '<div id="chsie-ticket-data" ></div>' ); // Just to give getTicketData something to grab.

            var ticketData = getTicketData( $( '#event-materials #chsie-ticket-data' ) );
            // TODO: Create #chsie-ticket-data from DB postmeta in chsie-events-admin.php.

            var $waitlistInputs = createWaitlistInputs( $ticketTable, ticketData );
            //console.log( "$waitlistInputs is ", $waitlistInputs );

            var didHandlers = bindWaitlistHandlers(); // T/F
            // console.log( "didHandlers = ", didHandlers );


            //var waitlistShowed = showWaitlistInputs( $waitlistInputs );

            function getTicketData( $dataNode ) {

                // Parse the ticket data into a JS object?
                var ticketData = {
                    0: 0
                };

                return ticketData;

            }


            function createWaitlistInputs( $table, dataObj ) {

                var $rows = $( $table ).find( 'tbody:last-of-type tr' );

                // Collect the titles of all rows that have a radio with value="No":
                var rowInfo = getRowInfo( $rows );
                //console.log( "rowInfo is " , rowInfo );

                // Insert new input and label nodes:
                var $newNodes = createNodes( $rows,  rowInfo );
                //console.log( "Created: " , $newNodes );


                // Select radio buttons based on data in ticketData:

                //console.log( rowInfo );

                // Iterate through rowInfo?  Or does the for loop need an if( chsie-row radio = no ) ?  Late.  Bed.


                function getRowInfo( $rows ) {
                    var rowInfo = [];

                    for (let i = 0; i < $rows.length; i++) {

                        var $row = $( $rows[i] );

                        var rowProductID = $row.attr( 'data-ticket-type-id' );

                        var rowName = $row.find( 'td.column-primary' ).text().trim();

                        rowInfo[i] = {
                            'ID' : rowProductID ,
                            'name' : rowName
                        };

                    }

                    return rowInfo;

                } // END OF: getRowInfo( $rows )


                function createNodes( $rows, rowInfo ) {

                    // Insert the input and label nodes with text and values drawn from rowInfo:
                    for (let i = 0; i < $rows.length; i++) {

                        var $row = $( $rows[i] );

                        var rowData = dataObj.i;

                        var rowProductID = $row.attr( 'data-ticket-type-id' );

                        $row.after( '<tr id="chsie-waitlist-' + i + '" class="chsie-waitlist-row" data-ticket-type-id="' + rowProductID + '"><td class="is-waitlist" colspan="1">&nbsp;</td><td class="waitlist-for" colspan="4">&nbsp;</td></tr>' );

                        var $newRow = $row.next( 'tr.chsie-waitlist-row' );

                        $newRow.find( 'td.is-waitlist' ).html( '<span class="radio-intro">Is this a waitlist ticket?</span><input type="radio" id="row-' + i + '-is-waitlist-yes" name="chsie_tickets[' + rowProductID + '][is_waitlist]" value="Yes"><label for="row-' + i + '-is-waitlist-yes">Yes</label><input type="radio" id="row-' + i + '-is-waitlist-no" name="chsie_tickets[' + rowProductID + '][is_waitlist]" value="No"><label for="row-' + i + '-is-waitlist-no">No</label>' );

                        $newRow.find( 'td.waitlist-for' ).html( '<span style="display: none;" class="radio-intro">Which ticket is this the waitlist for?</span><span style="display: none;" class="waitlist-verification">Please assign a waitlist for this ticket type.</span>' );

                        for ( let j = rowInfo.length - 1; j >= 0; j-- ) {

                            let thisID = rowInfo[j].ID;

                            let thisName = rowInfo[j].name;

                            $newRow.find( 'td.waitlist-for span.radio-intro' ).after( '<input style="display: none;" type="radio" id="row-' + i + '-waitlist-for-' + thisID + '" name="chsie_tickets[' + rowProductID + '][waitlist_for_id]" value="' + thisID + '"><label style="display: none;" for="row-' + i + '-waitlist-for-' + thisID + '">' + thisName + '</label>' );
                        }


                    }

                    return $( $table ).find( '.chsie-waitlist-row' );

                } // END OF: createNodes( $rows, rowInfo )

                return $newNodes;

            } // END OF: createWaitlistInputs( $table, dataObj )


            // Bind the click handlers and logic for the radio buttons:
            function bindWaitlistHandlers() {

                // Handler for .is-waitlist radio buttons:
                $( 'td.is-waitlist input[type="radio"]' ).click( function() {

                    var $this = $( this );
                    var $thisTD = $this.parents('td.is-waitlist').first();
                    //console.log( $thisTD );

                    var value = $this.val();
                    //console.log( "Value found was: ", value );

                    if( value === "Yes" ) {

                        var $nextTD = $thisTD.next( 'td.waitlist-for' );
                        console.log( $nextTD );

                        $nextTD.find( '.waitlist-verification' ).fadeOut( 'fast' );
                        setTimeout( function() {
                            $nextTD.find( '.waitlist-verification' ).html( 'Please assign a waitlist for this ticket type.' );
                            $nextTD.find('*').not( '.waitlist-verification' ).fadeIn( 'fast' );
                        }, 200 );

                    } else {

                        var $nextTD = $thisTD.next( 'td.waitlist-for' );
                        console.log( $nextTD );

                        // TODO: Uncheck all radios.

                        $nextTD.find('*').fadeOut( 'fast' );
                        setTimeout( function() {
                            $nextTD.find( '.waitlist-verification' ).fadeIn( 'fast' );
                        }, 200 );

                    }

                });


                $( 'td.waitlist-for input[type="radio"]' ).click( function() {

                    var $this = $( this );
                    var thisName = $this.parents( 'tr.chsie-waitlist-row' ).first().prev( 'tr.Tribe__Tickets__RSVP' ).find( 'td.column-primary' ).text().trim();
                    //console.log( 'thisName is ' + thisName );

                    var thisID = $this.val();
                    //console.log( 'Waitlist for product ID #' + thisID + ' selected.' );

                    var thisRowID = $this.parents( 'tr.chsie-waitlist-row' ).attr( 'data-ticket-type-id' );

                    var $targetRow = $( 'tr.chsie-waitlist-row[data-ticket-type-id="' + thisID + '"]' );

                    $targetRow.find( 'td.waitlist-for span.waitlist-verification' ).html( 'This ticket\'s waitlist is: <em>' + thisName + ' (#' + thisRowID + ')' + '</em>' );



                });

                // Handler for .waitlist-for radio buttons goes here.

            }
        }


        /* Script to grab active ticket type product_ids and put them into hidden inputs.
         * This passes them to the $_POST var on page update, which allows us to run the
         * set_tribe_stock_to_capacity() method on the DB to manage Tribe's bad code.
         */
        var $evMatsTable = $( '#event-materials-table' );

        if ( $evMatsTable.length > 0 ) {

            // Set up the jQuery objects.
            var $tribeTicketsBody = $( '#tribe_ticket_list_table tbody:last-child' );
            var $tribeRows = $tribeTicketsBody.find( 'tr' );

            // Iterate, inserting an <input> to store the product_id of each ticket type.
            for ( let i = 0; i < $tribeRows.length; i++ ) {

                let $tribeRow = $tribeRows[i];

                //    console.log(  $tribeRow );

                let eventTicketType = ( $( $tribeRow ).attr( 'data-ticket-type-id' ) );

                $( '#poststuff' ).before( `<input id="event-ticket-type-${i}" name="event_ticket_types[${i}]" value=${eventTicketType} type="hidden" />`);

            }

        }

        // *********************  AJAX functions ************************* //

        //console.log( "AJAX scripts reached." );

        var $inputs = $( '#event-materials-table input' );
        setSessionMats( $inputs );
        bindKeyupHandler( $inputs );


        // Save all input vals to sessionStorage.
        function setSessionMats( $inputs ) {

            var sessionMats = $inputs.serialize();

            sessionStorage.setItem( 'sessionMats', sessionMats );
            //console.log( "sessionMats set to: " + sessionStorage.getItem( 'sessionMats' ) + "(setSessionMats)");

        }


        //Handler for the input keyup event.  Fades the Save button in.
        function bindKeyupHandler( $inputs ) {

            //var $inputs = $( '#event-materials-table input' );

            // Ensure a clean slate:
            $inputs.off( 'keyup', '.event_materials' );
            //console.log("Keyup unbound from " + $inputs + "(bindKeyupHandler)" );
            // $inputs.removeClass( 'listening' );

            var timerID = 0;

            // Bind the handler:
            $inputs.keyup( function( keycode ) {

                if ( keycode !== 9 ) {

                    window.clearTimeout( timerID );

                    //console.log( "timerID is " + timerID + " after clear.");

                    timerID = window.setTimeout( function() {
                        compareMatsToSession( $inputs )
                    }, 500 );

                    //console.log( "timerID is " + timerID + " after new setTimeout.");


                }

            } );
            //console.log("Keyup bound to " + $inputs + "(bindKeyupHandler)");

            //$inputs.addClass( 'listening' );

        }


        function compareMatsToSession( $inputs ) {

            var currentMats = $inputs.parents( 'table' ).first().find( 'input' );
            currentMats = currentMats.serialize();

            var sessionMats = sessionStorage.getItem( 'sessionMats' );

            var $button = $( $inputs ).parents( '.inside' ).first().find( '#event-materials-save a.button' );

            //console.log( "compareMatsToSession ran on " + $inputs );

            if ( currentMats === sessionMats ) {
                unbindSaveAjax( $button );
                //console.log("currentMats = sessionMats. Button unbound. (compareMatsToSession)");

            } else {
                bindSaveAjax( $button );
                //console.log("currentMats != sessionMats. Button bound. (compareMatsToSession)");
                //console.log( "currentMats = " + currentMats );
                //console.log( "sessionMats = " + sessionMats );

            }
        }


        // Handler-binder for Save button:
        function bindSaveAjax( $button ) {

            // Add 'ready' class to track whether the event has been bound.
            $button.addClass( 'ready' ).attr( 'href', '#' );

            // Ensure a clean slate:
            $button.off( 'click' );
            //console.log("Button unbound. (bindSaveAjax)");

            // Bind the handler:
            $button.click( function( event ) {

                event.preventDefault();

                var $table = $( this ).parents( '.inside' ).first().find( 'table' );

                // Run the Ajax call:
                updateEventMats( $table );

            });

            //console.log("Button bound (bindSaveAjax).");
        }


        function unbindSaveAjax( $button ) {
            // Unbind the Save button and remove the 'ready' class:
            $button.removeClass( 'ready' ).removeAttr('href').off( 'click' );
            //console.log( "Button unbound (unbindSaveAjax)" );
        }


        // Define the ajax function:
        function updateEventMats( $table ) {

            //console.log("AJAX call triggered! (updateEventMats)");

            var materials = $( $table ).find( 'input' ).serialize();
            //console.log( materials );

            $.ajax({
                method: 'POST',
                url: ajaxurl,
                cache: false,
                data:
                {
                        action: 'ce_admin', // The wp_ajax_{action} to hook the callback function.
                        ajax_nonce: ce_admin_ajax_data.ajax_nonce,
                        event_materials: materials  // Obj.
                },

                beforeSend: function() {
                    //console.log();
                    $( '#event-materials-save-result span' ).fadeOut( 'fast' );

                },

                success: function( result, status, jqXHR ) {

                    // Display the message from the server:
                    $('#event-materials-save-result span').html( result ).fadeIn( 'fast' );

                    // Unbind the Save button and remove the 'ready' class:
                    $( '#event-materials #event-materials-save a.button' ).removeClass( 'ready' ).removeAttr('href').off( 'click' );

                    // Replace the sessionStorage materials with the new saved values.
                    setSessionMats( $table );

                    // Trigger the post-load event for WP API:
                    $( document.body ).trigger( 'post-load' );

                },

                error: function(jqXHR, status, error) {

                    $( '#event-materials-save-result span' ).html( "Sorry, unable to save." ).fadeIn( 'fast' );

                    console.log( "jqXHR was: " , jqXHR );
                    console.log( "Status returned was: " , status ); // error
                    console.log( "Error thrown was: " , error );     // bad request
                }

            }); // END OF: $.ajax().

        }


        // Grabs the table input values and sticks them in an associative array
        //  whose indices match the names of the inputs:
        function getEventMats( $table ) {

            var event_materials = []; // Now an Array!

            var $rows = $table.find( 'tbody tr' );

            for ( var i = 0; i < $rows.length; i++ ) {

               event_materials[i] = []; // Now an Array!

               var $inputs = $( $rows[i] ).find( 'input' );

               for ( var j = 0; j < $inputs.length; j++ ) {

                   var $this = $( $inputs[j] );

                   // Get the names of the indices from the index name attr:
                   //  (to reduce chance of conflict)

                   var value = ( $this.val() ); // Escape/Encode this?

                   var name = $this.attr( 'name' ); // Escape/Encode this?
                   var endPos = name.indexOf( "]", 19 );
                   name = name.substring( 19, endPos ); // Escape/Encode this?

                   event_materials[i][name] = value; // Sets the object property by var name.

               }

            }
            //console.log( event_materials );

            return event_materials;

        } // END OF: function getEventMats( $table )


    });

})(jQuery);
