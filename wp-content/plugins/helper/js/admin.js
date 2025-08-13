/**
 * Helper
 * Create a chatbot with AI features for your website.
 * Exclusively on https://1.envato.market/helper
 *
 * @encoding        UTF-8
 * @version         1.0.25
 * @copyright       (C) 2018 - 2023 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Cherviakov Vlad (vladchervjakov@gmail.com), Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmytro Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/
/**
 * Helper
 * Create a chatbot with OpenAI artificial intelligence features for your website.
 * Exclusively on https://1.envato.market/helper
 *
 * @encoding        UTF-8
 * @version         1.0.25
 * @copyright       (C) 2018-2024 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Envato License https://1.envato.market/KYbje
 * @contributors    Cherviakov Vlad (vladchervjakov@gmail.com), Nemirovskiy Vitaliy (nemirovskiyvitaliy@gmail.com), Dmytro Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

jQuery( function ( $ ) {

    "use strict";

    $( document ).ready( function () {
        const transitionDuration = 100;
        const savedPreviewStyles = {};
        let savedVoices = [];

        /**
         * Hide empty fields
         */
        function manageEmptyFields( action, fieldsClass, hideBefore, hideAfter, isTextarea = true, repeaterQuantity = 20 ) {

            const repeaterFields = $( `.${fieldsClass}` );

            repeaterFields.each( function() {

                let field = $( this ).val();

                if ( !isTextarea ) {
                    field = $( this ).children( 'input' ).val();
                }

                // Hide all groups with empty fields
                if ( field === '' ) {

                    // Hide field-group with empty field
                    if ( action === 'hide' ) {
                        $( this ).closest( 'tr' ).prevAll().slice( 0, +hideBefore ).hide( transitionDuration );
                        $( this ).closest( 'tr' ).nextAll().slice( 0, +hideAfter ).hide( transitionDuration );
                        $( this ).closest( 'tr' ).hide();

                        repeaterQuantity--;

                    }

                    // Show field-group with empty field
                    if ( action === 'show' ) {

                        $( this ).closest( 'tr' ).prevAll().slice( 0, +hideBefore ).show( transitionDuration );
                        $( this ).closest( 'tr' ).nextAll().slice( 0, +hideAfter ).show( transitionDuration );
                        $( this ).closest( 'tr' ).show();

                        repeaterQuantity++;

                        return false;

                    }

                }

            } );

        }

        /**
         * Calculate showing or hided fields
         * @param action
         * @param fieldsClass
         * @returns {number}
         */
        function calculateRepeaterFields( action, fieldsClass ) {

            const repeaterFields = $( `.${fieldsClass}` );
            let showingFields = 0;
            let hidedFields = 0;

            repeaterFields.each( function () {

                $( this ).closest( 'tr' ).css( 'display' ) !== 'none' ?
                    showingFields++ :
                    hidedFields++;

            } );

            if ( action === 'show' ) {

                return showingFields;

            } else if ( action === 'hide' ) {

                return hidedFields;

            } else {

                return 0;

            }

        }

        /**
         * Show or hide buttons if possible to show or hide something
         */
        function manageButtons( fieldsClass, removeButtonId, addButtonId, repeaterQuantity = 20 ) {

            const $addButton = $( `#${addButtonId}` );
            const $removeButton = $( `#${removeButtonId}` );

            // Hide button if nothing to show
            calculateRepeaterFields( 'show', fieldsClass ) >= repeaterQuantity ?
                $addButton.hide( 0 ) : $addButton.show( 0 );

            // Hide button if nothing to hide
            calculateRepeaterFields( 'hide', fieldsClass ) >= repeaterQuantity ?
                $removeButton.hide( 0 ) : $removeButton.show( 0 );

        }

        /**
         * Check repeater fields and show notices for no=empty fields
         */
        function notEmptyFieldNotice( fieldsClass, fieldName, isTextarea ) {

            const repeaterFields = $( `.${fieldsClass}` );
            let hideEmpty = false;
            let $targetField = false;

            repeaterFields.each( function() {

                let field = $( this ).val();

                if ( !isTextarea ) {
                    field = $( this ).find( 'input' ).val();
                }


                let isVisible = $( this ).parent().parent().parent().css( 'display' ) !== 'none';

                if ( !isTextarea ) {
                    isVisible = $( this ).parent().parent().css( 'display' ) !== 'none';
                }

                if ( field === '' && isVisible ) {

                    hideEmpty = true;

                }

                // Find last filled field
                if ( field !== '' && isVisible ) {

                    $targetField = $( this );

                    if ( !isTextarea ) {
                        $targetField = $( this ).find( 'input' );
                    }

                }


            } );

            if ( !hideEmpty ) {

                addNotice( fieldName,'warning', 5000 );

                $targetField.addClass( 'mdc-warning-field' );

                // Make filed focused and looks like focused
                $targetField.addClass( 'mdc-text-field--focused' );
                $targetField.focus();

                // Bind remove action to button
                $( `#mdp-repeater-field-notice-${fieldName} button` ).on( 'click', () => {

                    $targetField.val( '' );
                    $( '#submit' ).click();

                } );

            }

        }

        /** Add notice for repeater field
         *
         * @param fieldName
         * @param design
         * @param timeout
         */
        function addNotice( fieldName, design= 'info', timeout = 5000 ) {

            // Show only one notice
            if ( $( `#mdp-repeater-field-notice-${fieldName}` ).length > 0 ) { return; }

            $( '#wpbody-content form .wrap' ).append( `
<div id="mdp-repeater-field-notice-${fieldName}" class="mdc-snackbar mdc-${design} mdc-snackbar--open" data-timeout="${timeout}" data-mdc-index="1">
    <div class="mdc-snackbar__surface">
        <div class="mdc-snackbar__label" role="status" aria-live="polite">Clear the ${fieldName} field before deleting the field</div>
        <div class="mdc-snackbar__actions">
            <button class="mdc-button mdc-snackbar__action" type="button" title="Clear field">Clear ${fieldName}</button>
        </div>
    </div>   
</div>
            ` );

            setTimeout( () => {

                $( `#mdp-repeater-field-notice-${fieldName}` ).remove();
                $( '.mdc-warning-field' ).removeClass( 'mdc-warning-field' );

            }, timeout + 10 );

        }

        /**
         * Init repeater.
         */
        /**
         * Init translation buttons
         */
        function initRepeaterButtons( addButtonId, removeButtonId, fieldsClass, fieldName, hideBefore, hideAfter, isTextarea = true, repeaterQuantity = 20 ) {

            const $addButton = $( `#${addButtonId}` );
            $addButton.on( 'click', ( e ) => {

                e.preventDefault();
                manageEmptyFields( 'show', fieldsClass, hideBefore, hideAfter, isTextarea );
                manageButtons( fieldsClass, removeButtonId, addButtonId, repeaterQuantity );

            } );

            const $removeButton = $( `#${removeButtonId}` );
            $removeButton.detach();
            $addButton.parent().append( $removeButton );

            $removeButton.on( 'click', ( e ) => {

                e.preventDefault();
                notEmptyFieldNotice( fieldsClass, fieldName, isTextarea );
                manageEmptyFields( 'hide', fieldsClass, hideBefore, hideAfter, isTextarea );
                manageButtons( fieldsClass, removeButtonId, addButtonId, repeaterQuantity );

            } );

        }

        /**
         * Hide or close next tr after switch
         * @param $element
         * @param num
         * @param reverse
         */
        function switchSingle( $element, num, reverse = false ) {

            for ( let i = 0; i < num; i++ ) {

                if ( reverse ) {
                    $element.is( ':checked' ) ?
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).hide( 300 ) :
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).show( 300 );
                } else {
                    $element.is( ':checked' ) ?
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).show( 300 ) :
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).hide( 300 );
                }

                if ( $element.is( ':checked' ) ) {
                    $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).addClass( 'mdp-helper-active-field' )
                } else {
                    $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).removeClass( 'mdp-helper-active-field' )
                }

            }

        }

        /**
         * Init single switch
         * @param $element
         * @param num
         * @param refreshRepeaterFields
         * @param repeaterArgs
         * @param reverse
         * @param dependentSwitchOptions
         */
        function initSingleSwitch( $element, num = 1, refreshRepeaterFields = false, repeaterArgs = [], reverse = false, dependentSwitchOptions = {} ) {

            setTimeout( () => {
                switchSingle( $element, num, reverse );

                if ( Object.keys( dependentSwitchOptions ) ) {
                    const $dependentElement = $( `#${dependentSwitchOptions.id}` );
                    if ( $dependentElement.closest( 'tr' ).hasClass( 'mdp-helper-active-field' ) ) {
                        switchSingle( $dependentElement, dependentSwitchOptions.num, false);
                    }
                }

                repeaterArgs.forEach( repeaterArgsItem => {
                    manageEmptyFields( 'hide', repeaterArgsItem.fieldsClass, repeaterArgsItem.hideBefore, repeaterArgsItem.hideAfter, repeaterArgsItem.isTextarea );
                } );
            }, 100 );

            $element.on( 'change', () => {


                switchSingle( $element, num, reverse );

                if ( Object.keys( dependentSwitchOptions ) ) {
                    const $dependentElement = $( `#${dependentSwitchOptions.id}` );
                    if ( $dependentElement.closest( 'tr' ).hasClass( 'mdp-helper-active-field' ) ) {
                        switchSingle( $dependentElement, dependentSwitchOptions.num, false);
                    }
                }


                if ( refreshRepeaterFields && repeaterArgs.length > 0 ) {
                    repeaterArgs.forEach( repeaterArgsItem => {
                        manageEmptyFields( 'hide', repeaterArgsItem.fieldsClass, repeaterArgsItem.hideBefore, repeaterArgsItem.hideAfter, repeaterArgsItem.isTextarea );
                    } );
                }

            } );

        }

        function initDropZone( key_id, drop_zone_id, type, fileTypes, fileValidation ) {
            /** Show Error message under drop zone. */
            let $dropZone = $( `#${drop_zone_id}` );

            $dropZone.on( 'dragenter', function() {
                hideMessage();
                $( this ).addClass( 'mdp-hover' );
            } );

            $dropZone.on('dragleave', function() {
                $( this ).removeClass( 'mdp-hover' );
            } );

            /** Text Input to store key file. */
            let $key_input = $( `#${key_id}` );

            /** Setup Drag & Drop. */
            $dropZone.on( 'dragover', handleDragOver );

            /**
             * Read dragged file by JS.
             **/
            $dropZone.on( 'drop', function ( e ) {

                e.stopPropagation();
                e.preventDefault();

                // Show busy spinner.
                $( this ).removeClass( 'mdp-hover' );
                $dropZone.addClass( 'mdp-busy' );

                let file = e.originalEvent.dataTransfer.files[0]; // FileList object.

                /** Check is one valid JSON file. */
                if ( ! checkSoundFile( file ) ) {
                    $dropZone.removeClass( 'mdp-busy' );
                    return;
                }

                /** Read key file to input. */
                readFile( file )

            } );

            /**
             * Read key file to input.
             **/
            function readFile( file ) {

                let reader = new FileReader();
                const $submitButton = $( '#submit' );

                /** Closure to capture the file information. */
                reader.onload = ( function( theFile ) {

                    return function( e ) {

                        const fileName = type + '.' + theFile.name.split( '.' )[1];

                        $key_input.attr( 'value', fileName );

                        /** Hide error messages. */
                        hideMessage();

                        /** If we have valid sound. */
                        if ( $key_input.attr( 'value' ).length > 0 ) {

                            const blob = new Blob( [e.target.result], { type: theFile.type } );

                            const xhr = new XMLHttpRequest();
                            const formData = new FormData();
                            xhr.open( 'POST', mdpHelperUnity.ajaxURL, true );
                            formData.append( 'nonce', mdpHelperUnity.nonce );
                            formData.append( 'action', 'mdp_helper_save_file' );
                            formData.append( 'mdp_file_name', fileName );
                            formData.append( 'mdp_file', blob );
                            formData.append( 'mdp_file_type', type );
                            formData.append( 'mdp_file_validation_type', fileValidation );
                            xhr.onload = () => {
                                if ( xhr.status === 200 ) {
                                    $submitButton.click(); // Save settings.
                                } else {
                                    console.log( 'Error occurred while saving file!' )
                                }
                            };
                            xhr.onerror = () => {

                                // Error sending request
                                console.error('Error sending request!');

                            };
                            xhr.send( formData );


                        } else {

                            showErrorMessage( 'Error: Failed to read file. Please try again.' );

                            $dropZone.removeClass( 'mdp-busy' );

                        }

                    };

                } )( file );

                /** Read file as text. */
                reader.readAsArrayBuffer( file );

            }

            /**
             * Show upload form on click.
             **/
            let $file_input = $dropZone.next().next();
            $dropZone.on( 'click', function () {
                $file_input.click();
            } );

            $file_input.on( 'change', function ( e ) {

                $dropZone.addClass( 'mdp-busy' );

                let file = e.target.files[0];

                /** Check is one valid JSON file. */
                if ( ! checkSoundFile( file ) ) {
                    $dropZone.removeClass( 'mdp-busy' );
                    return;
                }

                /** Read key file to input. */
                readFile( file );

            } );

            function showErrorMessage( msg ) {

                let $msgBox = $dropZone.next();

                $msgBox.addClass( 'mdp-error' ).html( msg );

            }

            function handleDragOver( e ) {

                e.stopPropagation();
                e.preventDefault();

            }

            /** Hide message under drop zone. */
            function hideMessage() {

                let $msgBox = $dropZone.next();

                $msgBox.removeClass( 'mdp-error' ).html( '' );

            }

            /**
             * Check file is a single valid MP3 file.
             *
             * @param file - MP3 file to check.
             **/
            function checkSoundFile( file ) {

                /** Select only one file. */
                if ( null == file ) {

                    showErrorMessage( 'Error: Failed to read file. Please try again.' );

                    return false;

                }


                /** Check file types. */
                if ( !fileTypes.includes( file.type ) ) {
                    showErrorMessage( 'Error: File must have valid audio format.' );

                    return false;
                }

                return true;
            }

            /** Reset Key File. */
            $dropZone.next().find( '.mdp-reset-key-btn' ).on( 'click', function () {
                const xhr = new XMLHttpRequest();
                const formData = new FormData();
                xhr.open( 'POST', mdpHelperUnity.ajaxURL, true );
                formData.append( 'nonce', mdpHelperUnity.nonce );
                formData.append( 'action', 'mdp_helper_remove_file' );
                formData.append( 'mdp_file_type', type );
                formData.append( 'mdp_file_name', $key_input.attr( 'value' ) );

                xhr.onload = () => {
                    if ( xhr.status === 200 ) {
                        $key_input.val( '' );
                        $( '#submit' ).trigger( 'click' );
                    } else {
                        console.log( 'Error occurred while resetting file!' )
                    }
                };
                xhr.onerror = () => {
                    // Error sending request
                    console.error( 'Error sending request!' );

                };
                xhr.send( formData );

            } );

        }

        /** Single select */
        function resetFields( fieldsGroupClass, importFileField = '' ) {
            $( `.${fieldsGroupClass}` ).closest( 'tr' ).hide( 300 );
            if ( importFileField ) {
                $( `#${importFileField}` ).closest('tr').hide(300);
            }
        }


        /**
         * Init single select
         * @param $element
         * @param condition
         * @param num
         */
        function initSingleSelect( $element, condition, num = 1 ) {

            selectSingle( $element, num, condition );

            $element.on( 'change', () => {

                selectSingle( $element, num, condition );

            } );

        }

        /**
         * Hide or close next tr after select
         * @param $element
         * @param num
         * @param conditionValue
         */
        function selectSingle( $element, num, conditionValue ) {

            for ( let i = 0; i < num; i++ ) {

                if ( typeof conditionValue === 'object' ) {

                    let showElement = true
                    conditionValue.forEach( conditionValue => {

                        showElement = $element.val() !== conditionValue && showElement;

                    } );

                    showElement ?
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).show( 300 ) :
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).hide( 300 );

                } else {

                    $element.val() !== conditionValue ?
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).show( 300 ) :
                        $element.closest( 'tr' ).nextAll( 'tr' ).eq( i ).hide( 300 );

                }

            }

        }

        function showBasedOnSelect( $select, fieldsGroupClass, fieldId, onClickCallback = null ) {
            const $listItems = $select.closest( 'tr' ).find( '.mdc-list-item' );

            resetFields( fieldsGroupClass );

            $( `.${fieldId + $select.val() }` ).closest( 'tr' ).show( 300 );

            $listItems.on( 'click', ( e ) => {
                resetFields( fieldsGroupClass );
                $( `.${fieldId + e.target.dataset.value }` ).closest( 'tr' ).show( 300 );
                if ( onClickCallback ) {
                    onClickCallback();
                }
            } );

        }

        /** Show based on select in repeater */
        function showBasedOnSelectInRepeater( fieldName, fieldId, fieldGroup, repeaterSize, onClickCallback = null ) {
            for ( let i = 0; i < repeaterSize; i++ ) {
                showBasedOnSelect(
                    $( `#${fieldName}_${i}` ),
     `${fieldGroup}-${i}`,
             `${fieldId}-${i}-`,
                    onClickCallback
                );
            }

        }

        function showBasedOnChoices( selectId, fieldsGroupClass, fieldClass, importFileFieldId = '', importFileCondition = '' ) {
            const $choicesSelect = $( `#${selectId}` );
            const $importFileField = $( `#${importFileFieldId}` );

            resetFields( fieldsGroupClass, importFileFieldId );

            const choices = $choicesSelect.val();

            if ( !choices ) { return; }

            choices.forEach( choice => {
                $( `.${fieldClass + choice}` ).closest( 'tr' ).show( 300 );
                if ( choice === importFileCondition && importFileFieldId ) {
                    $importFileField.closest( 'tr' ).show( 300 );
                }
            } );

            $choicesSelect.on( 'change', function () {
              resetFields( fieldsGroupClass, importFileFieldId );
              const choices = $( this ).val();

              choices.forEach( choice => {
                $( `.${fieldClass + choice}` ).closest( 'tr' ).show( 300 );
                if ( choice === importFileCondition && importFileFieldId ) {
                    $importFileField.closest( 'tr' ).show( 300 );
                }
              } );

            } );

        }

        /**
         * Show avatar fields based on selected value.
         * @param type
         */
        function showAvatarFields( type ) {

            const triggerInput = $( `#mdp_helper_avatar_settings_${ type }_avatar` );
            if ( ! triggerInput.length ) { return; }

            const avatarImage = $( `#mdp_helper_avatar_settings_${ type }_avatar_image` ).closest( 'tr' );
            const avatarIcon = $( `#mdp_helper_avatar_settings_${ type }_avatar_icon` ).closest( 'tr' );
            const avatarColor = $( `#mdp_helper_avatar_settings_${ type }_avatar_color` ).closest( 'tr' );
            const avatarBackground = $( `#mdp_helper_avatar_settings_${ type }_avatar_background` ).closest( 'tr' );

            // Init
            avatarFields( avatarImage, avatarIcon, avatarColor, avatarBackground, triggerInput.val() );

            // On change
            triggerInput.on( 'change', function () {

                avatarFields( avatarImage, avatarIcon, avatarColor, avatarBackground, this.value );

            } );

        }

        /**
         * Show avatar fields based on selected value.
         * @param avatarImage
         * @param avatarIcon
         * @param avatarColor
         * @param avatarBackground
         * @param value
         */
        function avatarFields( avatarImage, avatarIcon, avatarColor, avatarBackground, value ) {

            switch ( value ) {

                case 'icon':

                    avatarImage.hide( 0 );
                    avatarIcon.show( 300 );
                    avatarColor.show( 300 );
                    avatarBackground.show( 300 );
                    break;

                case 'image':

                    avatarImage.show( 300 );
                    avatarIcon.hide( 0 );
                    avatarColor.hide( 300 );
                    avatarBackground.show( 300 );
                    break;

                case 'none':

                    avatarIcon.hide();
                    avatarImage.hide();
                    avatarColor.hide();
                    avatarBackground.hide();
                    break;

                default:

                    return;

            }

        }

        /**
         * Show box fields
         * @param $element
         * @param value
         */
        function showBoxShadowFields( $element, value ) {

            boxShadowFields( $element );

            $element.on( 'change', () => {

                boxShadowFields( $element );

            } );

        }

        /**
         * Show box shadow fields based on selected value.
         * @param $element
         */
        function boxShadowFields( $element ) {

            if ( $element.val() === 'none' ) {

                $element.closest( 'tr' ).next().hide();
                $element.closest( 'tr' ).next().next().hide();

            } else {

                $element.closest( 'tr' ).next().show( 300 );
                $element.closest( 'tr' ).next().next().show( 300 );

            }

        }

        /**
         * Float button tab
         */
        function uiFloatButtonTab() {

            // Master switch
            const $floatButtonSwitch = $( '#mdp_helper_float_button_settings_open_bot_with_button' );

            // Init
            if ( $floatButtonSwitch.prop('checked') ) {

                initSingleSwitch( $( '#mdp_helper_float_button_settings_open_bot_button_enable_icon' ), 4 );
                initSingleSwitch( $( '#mdp_helper_float_button_settings_open_bot_button_enable_caption' ), 4 );

            } else {

                switchSingle( $floatButtonSwitch, 27 );

            }

            // On change
            $floatButtonSwitch.on( 'change', () => {

                switchSingle( $floatButtonSwitch, 27 );

                if ( $floatButtonSwitch.prop('checked')  ) {

                    switchSingle( $floatButtonSwitch, 13 );

                    switchSingle( $( '#mdp_helper_float_button_settings_open_bot_button_enable_icon' ), 4 );
                    switchSingle( $( '#mdp_helper_float_button_settings_open_bot_button_enable_caption' ), 4 );

                }

            } );

        }

        /**
         * Send button tab
         */
        function uiSendButtonTab() {

            const $sendButtonSwitch = $( '#mdp_helper_form_settings_send_button_show' );

            if ( ! $sendButtonSwitch.prop('checked') ) {

                setTimeout( () => {

                    initSingleSwitch( $sendButtonSwitch, 16 );

                }, 100 );

            } else {

                initSingleSwitch( $sendButtonSwitch, 16 );

            }

        }

        /**
         * Upper line fields group
         */
        function uiSignature() {

            const $ulNameSwitcher = $( '#mdp_helper_message_settings_upper_line_name_enabled' );
            const $ulTimestampSwitcher = $( '#mdp_helper_message_settings_upper_line_timestamp_enabled' );
            const $ulButtonsSwitcher = $( '#mdp_helper_message_settings_upper_line_buttons_enabled' );

            initSingleSwitch( $ulNameSwitcher, 1 );
            initSingleSwitch( $ulTimestampSwitcher, 1 );
            initSingleSwitch( $ulButtonsSwitcher, 2 );

            // Init
            uiSignatureConditionFields( $ulNameSwitcher, $ulTimestampSwitcher, $ulButtonsSwitcher );

            // On change
            $( '#mdp_helper_message_settings_upper_line_name_enabled, #mdp_helper_message_settings_upper_line_timestamp_enabled, #mdp_helper_message_settings_upper_line_buttons_enabled' ).on( 'change', () => {

                uiSignatureConditionFields( $ulNameSwitcher, $ulTimestampSwitcher, $ulButtonsSwitcher );

            } );

        }

        /**
         * Show or hide upper line fields based on selected values.
         * @param $ulNameSwitcher
         * @param $ulTimestampSwitcher
         * @param $ulButtonsSwitcher
         */
        function uiSignatureConditionFields( $ulNameSwitcher, $ulTimestampSwitcher, $ulButtonsSwitcher ) {

            const $uiColor = $( '#mdp_helper_message_settings_upper_line_color' );
            const $uiFontSize = $( '#mdp_helper_message_settings_upper_line_font_size' );

            if ( ! $ulButtonsSwitcher.prop( 'checked' ) && ! $ulTimestampSwitcher.prop( 'checked' ) && ! $ulNameSwitcher.prop( 'checked' ) ) {

                $uiColor.closest( 'tr' ).hide( 0 );
                $uiFontSize.closest( 'tr' ).hide( 0 );

            } else {

                $uiColor.closest( 'tr' ).show( 300 );
                $uiFontSize.closest( 'tr' ).show( 300 );

            }

        }

        function getPosts( postTypes ) {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            const url = new URL( mdpHelperUnity.ajaxURL );
            url.searchParams.set( 'action', 'mdp_helper_get_posts' );
            url.searchParams.set( 'mdp_helper_post_types', postTypes );
            url.searchParams.set( 'mdp_helper_nonce', mdpHelperUnity.nonce  );

            return new Promise( ( resolve, reject ) => {
                xhr.open( 'GET', url, true );
                xhr.onload = () => resolve( xhr.responseText );
                xhr.onerror = () => reject( xhr.statusText );
                xhr.send( formData );
            } );
        }


        function getSelectedTtsVoices() {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();
            const url = new URL( mdpHelperUnity.ajaxURL );
            url.searchParams.set( 'action', 'mdp_helper_get_tts_voices' );
            url.searchParams.set( 'mdp_helper_nonce', mdpHelperUnity.nonce  );

            return new Promise( ( resolve, reject ) => {
                xhr.open( 'GET', url, true );
                xhr.onload = () => resolve( xhr.responseText );
                xhr.onerror = () => reject( xhr.statusText );
                xhr.send( formData );
            } );
        }

        function createChoicesOptions( postsData ) {
            let options = '';

            for ( let postId in postsData.posts ) {
                options += `<option ${postsData.selected_posts.includes( postId ) ? 'selected=""' : ''} value="${postId}">
                                ${postsData.posts[postId]}
                            </option>`;
            }

            return options;
        }

        function setPreviouslySelectedVoices( selectedVoices, voices ) {
            const selectedVoicesItems = selectedVoices.split( ',' );
            const resultVoices = [];
            selectedVoicesItems.forEach( selectedVoicesItem => {
                const voice = voices.find( ( voice ) => voice.name === selectedVoicesItem.trim() );
                if ( voice ) {
                    resultVoices.push( `${voice.name};${voice.lang}` );
                }
            } );

            return resultVoices;
        }

        function createTextToSpeechOptions( voices, selectedVoices ) {
            let selectedOptions = '';
            let options = '';

            selectedVoices = Array.isArray( selectedVoices ) ? selectedVoices : setPreviouslySelectedVoices( selectedVoices, voices );

            if ( selectedVoices.length && Array.isArray( selectedVoices ) ) {
                selectedVoices.forEach( selectedVoice => {


                    const voice = voices.find( ( voice ) => voice.name === selectedVoice.split( ';' )[0] );
                    let unlistedVoice = '';

                    /** Show voices previously selected in other browsers */
                    if ( !voice ) {
                        unlistedVoice = selectedVoice.split( ';' ).join( ', ' );
                    }

                    selectedOptions += voice ?
                                        `<option value="${voice.name};${voice.lang}" selected>
                                            ${voice.name}, ${voice.lang}
                                        </option>` :
                                        `<option value="${selectedVoice}" selected>
                                            ${unlistedVoice}
                                        </option>`;
                } );
            }

            voices.forEach( ( voice ) => {
                if ( selectedVoices.includes( `${voice.name};${voice.lang}` ) ) { return; }
                options += `<option value="${voice.name};${voice.lang}">
                                ${voice.name}, ${voice.lang}
                            </option>`;
            } );

            return selectedOptions + options;
        }

        function createNewSelect ( tabName, fieldId, fieldName, options, all ) {
            if ( all ) { options['all'] = 'All'; }

            return `<select 
                    id="${fieldId}" 
                    name="${tabName}[${fieldName}][]" 
                    multiple
                    data-placeholder
                    class="mdp-chosen chosen-select"
                    style="display: none;">${options}</select>`;
        }

        function updatePostIdControl( selectId, postsContainerId, choicesContainerId, tabName, postFieldName, includeAll = false ) {
            const $choicesSelect = $( `#${selectId}` );

            $choicesSelect.on( 'change', async function () {
                const posts = await getPosts( $choicesSelect.val() );
                const postsData = JSON.parse( posts ).data;

                const $postsIdsSelect = $( `#${postsContainerId}` );
                const $choicesContainer = $( `#${choicesContainerId}` );

                const $container = $postsIdsSelect.parent();

                /** Remove old chosen containers and select */
                $postsIdsSelect.remove();
                $choicesContainer.remove();

                $container.append( createNewSelect(
                    tabName,
                    postsContainerId,
                    postFieldName,
                    createChoicesOptions( postsData ),
                    includeAll
                ) );


                /** Init new chosen */
                const updatedPostIdsSelect = $container.find( `#${postsContainerId}` );

                updatedPostIdsSelect.chosen( {
                    width: '100%',
                    search_contains: true,
                    disable_search_threshold: 7,
                    inherit_select_classes: true,
                    no_results_text: 'Oops, nothing found',
                    allow_single_deselect: true,
                } );

            } );
        }

        async function initVoicesChosen( $voicesSelect ) {
            const $chosenContainer = $( '#mdp_helper_behavior_settings_bot_tts_voice_chosen' );
            const voices = speechSynthesis.getVoices();
            const selectedVoicesJSON = await getSelectedTtsVoices();
            const selectedVoices = JSON.parse( selectedVoicesJSON );

            savedVoices = selectedVoices.data;

            const $container = $voicesSelect.parent();

            /** Remove old chosen containers and select */
            $chosenContainer.remove();
            $voicesSelect.remove();

            $container.append( createNewSelect(
                'mdp_helper_behavior_settings',
                'mdp_helper_behavior_settings_bot_tts_voice',
                'bot_tts_voice',
                createTextToSpeechOptions( voices, selectedVoices.data ),
                false
            ) );

            /** Init new chosen */
            const updatedVoicesSelect = $container.find( '#mdp_helper_behavior_settings_bot_tts_voice' );

            updatedVoicesSelect.chosen( {
                width: '100%',
                search_contains: true,
                disable_search_threshold: 7,
                inherit_select_classes: true,
                no_results_text: 'Oops, nothing found',
                allow_single_deselect: true,
            } );

            updatedVoicesSelect.on('change', function(event, params) {
                const $options = updatedVoicesSelect.find( 'option' );

                $options.each( function () {

                    // Run on for correct option
                    if ( params.selected && params.selected === $( this ).val() ) {
                        $( this ).attr( "selected", "" );
                        let $lastOption = $( this );
                        $( this ).remove();
                        updatedVoicesSelect.append( $lastOption );
                    }

                } );

                updatedVoicesSelect.trigger( "chosen:updated" );
            });
        }

        function setTextToSpeechVoices() {
            const $voicesSelect = $( '#mdp_helper_behavior_settings_bot_tts_voice' );

            if ( speechSynthesis.getVoices().length ) {
                initVoicesChosen( $voicesSelect );
            } else {
                window.speechSynthesis.addEventListener( "voiceschanged", async () => {
                    await initVoicesChosen( $voicesSelect );
                } );
            }

        }

        function initMeasures() {
            const $sliderInput = $( this ).find( '.mdc-slider input' );
            const $sliderThumb = $( this ).find( '.mdc-slider__thumb-container' );
            const $sliderTrack = $( this ).find( '.mdc-slider__track' );
            const $numberInput = $( this ).find( '.mdc-text-field input' );
            const $unitInput = $( this ).find( '.mdc-select input' );
            const $helperValue = $( this ).find( '.mdc-text-field-helper-line strong' );
            const $helperUnit = $( this ).find( '.mdc-text-field-helper-line span' );

            const sliderMin = parseInt( $( this ).find( '.mdc-slider' ).attr( 'aria-valuemin' ) );
            const sliderMax = parseInt( $( this ).find( '.mdc-slider' ).attr( 'aria-valuemax' ) );

            // Set text input to number type
            $numberInput.attr( 'type', 'number' );

            // Listen for slider change
            $sliderInput.on( 'change', function () {

                $numberInput.val( this.value );

            } );

            // Listen for number change
            $numberInput.on( 'change', function () {

                $sliderInput.val( this.value );
                $helperValue.html( this.value );

                if ( this.value >= sliderMax ) {

                    $sliderThumb.css( 'transform', 'translateX(300px) translateX(-50%)' );
                    $sliderTrack.css( 'transform', 'scaleX(1)' );

                } else if ( this.value <= sliderMin ) {

                    $sliderThumb.css( 'transform', 'translateX(0px) translateX(-50%)' );
                    $sliderTrack.css( 'transform', 'scaleX(0)' );

                } else {

                    const sliderRange = sliderMax - sliderMin;
                    let thumbPosition;

                    parseInt( this.value ) < 0 ?
                        thumbPosition = ( Math.abs( sliderMin ) - Math.abs( parseInt( this.value ) ) ) / sliderRange :
                        thumbPosition = ( Math.abs( sliderMin ) + parseInt( this.value ) ) / sliderRange;


                    $sliderThumb.css( 'transform', `translateX(${ 300 * thumbPosition }px) translateX(-50%)` );
                    $sliderTrack.css( 'transform', `scaleX(${ thumbPosition })` );

                }

            } );

            // Lister for unit change
            $unitInput.on( 'change', function () {

                $helperUnit.html( ' ' + this.value );

            } );

        }

        function handleSwitchGeneralMessages() {
            for ( let i = 0; i <= 20; i++ ) {
                initSingleSwitch( $( `#mdp_helper_general_settings_general_select_manually_posts_${i}` ), 1 );
            }
        }

        function getSettingName( fieldName ) {
            const regex = /\[(.*?)\]/;
            const match = fieldName.match(regex);
            return match ? match[1] : null;
        }

        function formatFieldName( settingName ) {
            const replaceValues = [ '_top', '_left', '_bottom', '_right', '_unit' ];
            replaceValues.forEach( replaceValue => {
                settingName = settingName.replace( replaceValue, '' );
            } );

            return settingName;
        }

        function getControlPartSettingValue( $controlWrapper, fieldName, part ) {
            const initialSettingName = getSettingName( fieldName );
            const settingName = `${initialSettingName}_${part}`;
            const unitFieldName = fieldName.replace( /\[.*?\]/, "[" + settingName + "]" );
            const $field = $controlWrapper.find( `input[name="${unitFieldName}"]` );
            return $field.val();
        }

        function updateBoxShadowCss( fieldName ) {
            let formattedName = fieldName;
            const replaceValues = [ '_color', '_offset_top', '_offset_left', '_offset_bottom', '_offset_right', '_unit' ];
            replaceValues.forEach( replaceValue => {
                formattedName = formattedName.replace( replaceValue, '' );
            } );
            const settingName = getSettingName( formattedName );
            const boxShadowType = $( `input[name="${formattedName}"]` ).val();
            const offsetFieldName = fieldName.replace( /\[.*?\]/, "[" + settingName + '_offset' + "]" );
            const offsetTopName = fieldName.replace( /\[.*?\]/, "[" + settingName + '_offset_top' + "]" );
            const colorFieldName  = fieldName.replace( /\[.*?\]/, "[" + settingName + '_color' + "]" );
            const $offsetField = $( `input[name="${offsetTopName}"]` );
            const $colorField = $( `input[name="${colorFieldName}"]` );

            const offsetValue = getSidesCss(
                $offsetField.closest( '.mdp-controls-sides' ),
                offsetFieldName
            );
            const colorValue = $colorField.val();
            let value = offsetValue + ' ' + colorValue;
            if ( boxShadowType === 'inside' ) {
                value = 'inset ' + offsetValue + ' ' + colorValue
            } else if ( boxShadowType === 'none' ) {
                value = 'none';
            }
            updateSetting( formattedName, value);
        }

        function updateAnimationsCss( fieldName ) {
            let formattedName = '';
            const replaceValues = [ '_duration', '_delay', ];
            replaceValues.forEach( replaceValue => { formattedName = fieldName.replace( replaceValue, '' ) } );
            const settingName = getSettingName( formattedName );
            const durationFieldName = fieldName.replace( /\[.*?\]/, "[" + settingName + '_duration' + "]" );
            const delayFieldName = fieldName.replace( /\[.*?\]/, "[" + settingName + '_delay' + "]" );

            const duration = document.querySelector( `input[name="${durationFieldName}"]` ).value;
            const delay = document.querySelector( `input[name="${delayFieldName}"]` ).value;
            const animation = document.querySelector( `input[name="${formattedName}"]` ).value;

            const value = `${duration}s ease ${delay}s 1 normal both running ${animation}`;

            updateSetting( formattedName, value );

        }

        function livePreviewEvents() {
            const $settingsFields = $( 'input[name^="mdp_"], select[name^="mdp_"], textarea[name^="mdp_"]' );
            const $editors = $( '.wp-editor-container' );

            $editors.each( function () {
                const $textarea = $( this ).find( 'textarea' );
                const editorId = $textarea.attr( 'id' );
                const fieldName = $textarea.attr( 'name' );
                const editor = tinymce.get( editorId );
                editor.on( 'keyup', () => {
                    updateSetting( fieldName, editor.getContent() );
                } );
            } );

            $settingsFields.each( function () {
                $( this ).on( 'change', () => {
                    if ( $( this ).attr( 'name' ).includes( 'box_shadow' ) ) {
                        updateBoxShadowCss( $( this ).attr( 'name' ) );
                    } else if ( $( this ).closest( '.mdp-helper-animation-control' ).length ) {
                        updateAnimationsCss( $( this ).attr( 'name' ) );
                    } else if ( $( this ).closest( '.mdp-controls-sides' ).length ) {
                        const fieldName = formatFieldName( $( this ).attr( 'name' ) );
                        const value = getSidesCss( $( this ).closest( '.mdp-controls-sides' ), fieldName );
                        updateSetting( fieldName, value );
                    } else if ( $( this ).closest( '.mdp-helper-measures' ).length ) {
                        updateMeasuresCss( $( this ).closest( '.mdp-helper-measures' ), $( this ) );
                    } else if ( $( this ).closest( '.mdc-slider' ).length ) {
                        updateSetting( $( this ).attr( 'name' ), $( this ).val() + 'px' );
                    } else if ( $( this ).closest( '.mdc-switch' ).length ) {
                        const $checked = $( this.closest( '.mdc-switch--checked' ) );
                        const value = $checked.length ? 'on' : 'off';
                        updateSetting( $( this ).attr( 'name' ), value );
                    } else {
                        updateSetting( $( this ).attr( 'name' ), $( this ).val() );
                    }
                } );
            } );
        }


        function update_preview_option( settingName, settingValue ) {
            const xhr = new XMLHttpRequest();
            const formData = new FormData();

            formData.append( 'mdp_helper_nonce', mdpHelperUnity.nonce );
            formData.append( 'action', 'mdp_helper_update_preview_option' );
            formData.append( 'mdp_helper_preview_setting_name', settingName );
            formData.append( 'mdp_helper_preview_setting_value', JSON.stringify( settingValue ) );

            return new Promise( ( resolve, reject ) => {
                xhr.open( 'POST', mdpHelperUnity.ajaxURL, true );
                xhr.onload = () => resolve( xhr.responseText );
                xhr.onerror = () => reject( xhr.statusText );
                xhr.send( formData );
            } );
        }

        function getSidesCss( $sidesWrapper, fieldName ) {
            const top = getControlPartSettingValue( $sidesWrapper, fieldName, 'top' );
            const left = getControlPartSettingValue( $sidesWrapper, fieldName, 'left' );
            const bottom = getControlPartSettingValue( $sidesWrapper, fieldName, 'bottom' );
            const right = getControlPartSettingValue( $sidesWrapper, fieldName, 'right' );
            const unit = getControlPartSettingValue( $sidesWrapper, fieldName, 'unit' ) ?? '';
            return  top + unit + ' ' + right + unit + ' ' + bottom + unit + ' ' + left + unit;
        }

        function updateMeasuresCss( $measuresWrapper, $field ) {
            const unit = getControlPartSettingValue( $measuresWrapper, $field.attr( "name" ), 'unit' );
            const settingName = $field.attr( "name" ).replace( '_unit', '' );
            const value = $measuresWrapper.find( `input[name="${settingName}"]` ).val();
            updateSetting( settingName, value + unit );
        }

        async function updateSetting( name, value ) {
            const $previewIframe = document.querySelector( '#mdp-helper-live-preview' );
            const iframeDocument = $previewIframe.contentDocument || $previewIframe.contentWindow.document;
            const previewStylesVars = getComputedStyle( iframeDocument.documentElement );
            const settingName = getSettingName( name );
            const formattedName = settingName
                .replace( 'bot_container', 'chatbot_container' )
                .replace( 'open_bot', 'open'  )
                .replace( 'upper_line', 'signature' )
                .replace( 'bot_sst_size', 'recognition-icon-size' )
                .replace( 'bot_stt_color', 'recognition-icon-color' );
            const varName = '--helper-' + formattedName.replace( /_/g, "-" );
            if ( previewStylesVars.getPropertyValue( varName ) ) {
                iframeDocument.documentElement.style.setProperty( varName, value );
                savedPreviewStyles[varName] = value;
            } else {
                await update_preview_option( settingName, value );
                $previewIframe.contentWindow.location.href = $previewIframe.src + '&rand=' + new Date().getTime();
            }
        }

        function livePreviewPopup() {
            const $previewPopupBtn = document.querySelector( '.mdp-helper-live-preview-open-button' );
            const $previewPopup = document.querySelector( '.mdp-helper-live-preview' );
            const $closeButton = document.querySelector( '.mdp-helper-preview-close-button' );
            $previewPopupBtn.addEventListener( 'click', ( e ) => {
                e.preventDefault();
                $previewPopup.style.display = 'block';
                $previewPopupBtn.style.display = 'none';
                $closeButton.style.display = 'flex'
            } );

            $closeButton.addEventListener( 'click', () => {
                $previewPopup.style.display = 'none';
                $previewPopupBtn.style.display = 'flex';
                $closeButton.style.display = 'none'
            } );
        }

        function setSavedStylesSettings( iframeDocument ) {
            $.each( savedPreviewStyles, function ( key, value ) {
                iframeDocument.documentElement.style.setProperty( key, value );
            } );
        }

        /** Init hide elements */
        function hideConditionElements() {
            const $elementsToHide = $( '.mdp-helper-hide' );
            $elementsToHide.each(function () {
                $( this ).closest( 'tr' ).hide();
            });
        }

        /**
         * Init meta-boxes user interface
         */
         function initUI() {

             /** Hide elements with mdp-helper-hide class */
             hideConditionElements();

             /** Set text to speech voices choosen options */
             setTextToSpeechVoices();

            const $previewIframe = document.querySelector( '#mdp-helper-live-preview' );
            if ( $previewIframe ) {
                $previewIframe.addEventListener(
                    'load', () => {
                        const iframeDocument = $previewIframe.contentDocument || $previewIframe.contentWindow.document;
                        livePreviewEvents();
                        // Set saved before styles settings
                        setSavedStylesSettings( iframeDocument );
                    }
                );
                livePreviewPopup();
            }


            /** Measures control */
            $( '.mdp-helper-measures' ).each( initMeasures );

            /** Updates post id control on post types control change */
            updatePostIdControl(
                'mdp_helper_ai_settings_open_ai_post_types',
         'mdp_helper_ai_settings_open_ai_post_id',
       'mdp_helper_ai_settings_open_ai_post_id_chosen',
              'mdp_helper_ai_settings',
         'open_ai_post_id'
            );

            for ( let i = 0; i <= 20; i++ ) {
                updatePostIdControl(
                    `mdp_helper_general_settings_general_message_condition_post_types_${i}`,
              `mdp_helper_general_settings_general_message_condition_posts_${i}`,
            `mdp_helper_general_settings_general_message_condition_posts_${i}_chosen`,
                    'mdp_helper_general_settings',
                `general_message_condition_posts_${i}`
                );
            }

            /** Show controls based on selected prompt type */
            showBasedOnChoices(
                'mdp_helper_ai_settings_open_ai_prompt_type',
          'mdp-helper-open-ai-fields',
               'mdp-helper-prompt-',
          'mdp-open_ai_pdf_file-drop-zone',
        'pdf_file'
            );

            /** Show acceptance checkbox controls based on select */
            initSingleSelect( $( '#mdp_helper_collect_data_settings_show_acceptance_checkbox' ), [ 'none' ], 2 );


            /** Show animations controls based on select */
            showBasedOnSelect( $( '#mdp_helper_popup_settings_bot_container_animation' ), 'mdp-helper-popup-animation', 'mdp-helper-tab-animation-type-' );
            showBasedOnSelect( $( '#mdp_helper_message_settings_bot_message_animation' ), 'mdp-helper-bot-message-animation', 'mdp-helper-bot-message-animation-type-' );
            showBasedOnSelect( $( '#mdp_helper_message_settings_user_message_animation' ), 'mdp-helper-user-message-animation', 'mdp-helper-user-message-animation-type-' );

            /** Show based on select */
            showBasedOnSelectInRepeater( 'mdp_helper_collect_data_settings_collect_data_field_validation', 'mdp-helper-validation', 'mdp-helper-collect-data-fields', 20 );
            showBasedOnSelectInRepeater( 'mdp_helper_ai_personalities_settings_ai_type', 'mdp-helper-ai-bot-personality', 'mdp-helper-ai-bot-personalities-fields', 10 );

            /** Show controls based on selected bot type */
            showAvatarFields( 'bot' );
            showAvatarFields( 'user' );

            /** General messages */
            handleSwitchGeneralMessages();
            showBasedOnSelectInRepeater( 'mdp_helper_general_settings_general_message_condition', 'mdp-helper-message-conditions', 'mdp-helper-message-conditions-fields', 20, handleSwitchGeneralMessages );



            /** More help settings */
            initSingleSwitch( $( '#mdp_helper_general_settings_enable_is_relevant' ), 191, true,
                [
                    {
                        fieldsClass: 'mdp-helper-general_exit-message',
                        hideBefore: 1,
                        hideAfter: 1,
                        isTextarea: true
                    },
                    {
                        fieldsClass: 'mdp-helper-general_try_again-message',
                        hideBefore: 1,
                        hideAfter: 1,
                        isTextarea: true
                    },
                    {
                        fieldsClass: 'mdp-helper-general_more_help_initial-message',
                        hideBefore: 1,
                        hideAfter: 1,
                        isTextarea: true
                    }
                ] );

            /** Chat header content */
            initSingleSwitch( $( '#mdp_helper_header_settings_chat_header_heading' ), 3 );
            initSingleSwitch( $( '#mdp_helper_header_settings_close_button_show' ), 6 );

            /** Collect data */
            initSingleSwitch( $( '#mdp_helper_collect_data_settings_send_user_data_email' ), 1 );

            /** Chat footer content */
            initSingleSwitch( $( '#mdp_helper_footer_settings_chat_footer_text_show' ), 3 );

            /** Bot menu button */
            initSingleSwitch( $( '#mdp_helper_faq_settings_show_faq_category_icon' ), 1 );

            /** Open button */
            uiFloatButtonTab();

            /** Send button */
            uiSendButtonTab();

            /** Behaviour */
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_stt' ), 4 );
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_tts' ), 2 );
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_typing_animation' ), 1 );
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_respond_delay_enabled' ), 3 );
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_memory_enabled' ), 1 );

            /** Signature */
            uiSignature();

            /** Toolbar */
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_close_button_show' ), 6 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_bot_commands_enable' ), 2 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_mute_button_enable' ), 2 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_mail_button_enable' ), 3 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_call_button_enable' ), 3 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_messenger_button_enable' ), 3 );
            initSingleSwitch( $( '#mdp_helper_toolbar_settings_social_button_enable' ), 3 );

            const uiSelectsValueHolder = $( `.mdc-select input[type="hidden"]` );
            /** Show box shadow fields */
            uiSelectsValueHolder.each( function () {

                // If id ends with _border_style
                if ( $( this ).attr( 'id' ).endsWith( '_border_style' ) ) {

                    showBoxShadowFields( $( this ) );

                }

            } );

            /** Box shadow fields */
            uiSelectsValueHolder.each( function () {

                // If id ends with _border_style
                if ( $( this ).attr( 'id' ).endsWith( '_box_shadow' ) ) {

                    showBoxShadowFields( $( this ) );

                }

            } );

            /** Init send message sound file drop zone */
            initDropZone(
                'mdp-helper-settings-dnd-send_message_sound',
           'mdp-send_message_sound-drop-zone',
                 'send_message_sound',
              ['audio/mpeg', 'audio/wav', 'audio/ogg'],
           'sound'
            );

            /** Init receive message sound file drop zone */
            initDropZone(
                'mdp-helper-settings-dnd-receive_message_sound',
           'mdp-receive_message_sound-drop-zone',
                  'receive_message_sound',
              ['audio/mpeg', 'audio/wav', 'audio/ogg'],
                       'sound'
            );

            /** Init PDF file drop zone */
            initDropZone(
                'mdp-helper-settings-dnd-open_ai_pdf_file',
           'mdp-open_ai_pdf_file-drop-zone',
                  'open_ai_pdf_file',
              ['application/pdf'],
            'pdf'
            );

            /** Show controls depending switch value */
            initSingleSwitch( $( '#mdp_helper_design_settings_bot_typing_animation' ), 1 );
            initSingleSwitch( $( '#mdp_helper_popup_settings_bot_container_enable_auto_open' ), 1 );
            initSingleSwitch( $( '#mdp_helper_message_settings_upper_line_copy_button_enabled' ), 1 );
            initSingleSwitch( $( '#mdp_helper_general_settings_bot_logs_auto_delete' ), 1 );
            initSingleSwitch( $( '#mdp_helper_general_settings_bot_logs' ), 2, false, [], false, {
                id: 'mdp_helper_general_settings_bot_logs_auto_delete',
                num: 1
            } );
            initSingleSwitch( $( '#mdp_helper_open_bot_button_settings_open_bot_with_button' ), 15 );
            initSingleSwitch( $( '#mdp_helper_faq_settings_faq_pagination' ), 1 );
            initSingleSwitch( $( '#mdp_helper_collect_data_settings_enable_google_analytics' ), 2 );
            initSingleSwitch( $( '#mdp_helper_ai_settings_open_ai_set_user_requests_limit' ), 3 );
            initSingleSwitch( $( '#mdp_helper_ai_settings_open_ai_add_additional_keys' ), 11, true, [ {
                fieldsClass: 'mdp-open-ai-key',
                hideBefore: 0,
                hideAfter: 0,
                isTextarea: false
            } ] );

            /** Initial message repeater */
            initRepeaterButtons( 'mdp-add-message-general', 'mdp-remove-message-general', 'mdp-helper-general-message', 'message', 1, 5 );
            manageEmptyFields( 'hide', 'mdp-helper-general-message', 1 , 5 );
            manageButtons( 'mdp-helper-general-message', 'mdp-remove-message-general', 'mdp-add-message-general' );

            /** Exit message repeater */
            initRepeaterButtons( 'mdp-add-message-general_exit', 'mdp-remove-message-general_exit', 'mdp-helper-general_exit-message', 'message', 1, 1 );
            manageEmptyFields( 'hide', 'mdp-helper-general_exit-message', 1 , 1 );
            manageButtons( 'mdp-helper-general_exit-message', 'mdp-remove-message-general_exit', 'mdp-add-message-general_exit' );

            /** Try again message repeater */
            initRepeaterButtons( 'mdp-add-message-general_try_again', 'mdp-remove-message-general_try_again', 'mdp-helper-general_try_again-message', 'message', 1, 1 );
            manageEmptyFields( 'hide', 'mdp-helper-general_try_again-message', 1 , 1 );
            manageButtons( 'mdp-helper-general_try_again-message', 'mdp-remove-message-general_try_again', 'mdp-add-message-general_try_again' );

            /** Try again message repeater */
            initRepeaterButtons( 'mdp-add-message-general_more_help_initial', 'mdp-remove-message-general_more_help_initial', 'mdp-helper-general_more_help_initial-message', 'message', 1, 1 );
            manageEmptyFields( 'hide', 'mdp-helper-general_more_help_initial-message', 1 , 1 );
            manageButtons( 'mdp-helper-general_more_help_initial-message', 'mdp-remove-message-general_more_help_initial', 'mdp-add-message-general_more_help_initial' );

            /** FAQ questions repeaters */
            initRepeaterButtons( 'mdp-add-faq', 'mdp-remove-faq', 'mdp-helper-faq-question', 'question', 1, 2, true, 70 );
            manageEmptyFields( 'hide', 'mdp-helper-faq-question', 1 , 2, true, 70 );
            manageButtons( 'mdp-helper-faq-question', 'mdp-remove-faq', 'mdp-add-faq', 70 );

            initRepeaterButtons( 'mdp-add-message-faq', 'mdp-remove-message-faq', 'mdp-helper-faq-message', 'message', 1, 1 );
            manageEmptyFields( 'hide', 'mdp-helper-faq-message', 1, 1 );
            manageButtons( 'mdp-helper-faq-message', 'mdp-remove-message-faq', 'mdp-add-message-faq' );

            /** Collect user data success/error messages repeaters */
            initRepeaterButtons( 'mdp-add-message-collect_data', 'mdp-remove-message-collect_data', 'mdp-helper-collect_data-message', 'message', 1,1 );
            manageEmptyFields( 'hide', 'mdp-helper-collect_data-message', 1, 1 );
            manageButtons( 'mdp-helper-collect_data-message', 'mdp-remove-message-collect_data', 'mdp-add-message-collect_data' );


            /** Collect user data repeaters */
            initRepeaterButtons( 'mdp-add-collect-data', 'mdp-remove-collect-data', 'mdp-helper-collect-data-bot-message', 'message', 1,6, false );
            manageEmptyFields( 'hide', 'mdp-helper-collect-data-bot-message', 1, 6, false );
            manageButtons( 'mdp-helper-collect-data-bot-message', 'mdp-remove-collect-data', 'mdp-add-collect-data' );

            /** Api keys repeater */
            initRepeaterButtons( 'mdp-add-ai-key', 'mdp-remove-ai-key', 'mdp-open-ai-key', 'key', 0, 0, false );
            manageEmptyFields( 'hide', 'mdp-open-ai-key', 0, 0, false );
            manageButtons( 'mdp-open-ai-key', 'mdp-remove-ai-key', 'mdp-add-ai-key', 10 );

            /** Bot personalities repeater */
            initRepeaterButtons( 'mdp-add-bot-personality', 'mdp-remove-bot-personality', 'mdp-bot-personality-name', 'name', 0, 3, false, 10 );
            manageEmptyFields( 'hide', 'mdp-bot-personality-name', 0, 3, false, 10 );
            manageButtons( 'mdp-bot-personality-name', 'mdp-remove-bot-personality', 'mdp-add-bot-personality', 10 );

            /** Bot personalities repeater */
            initRepeaterButtons( 'mdp-add-plan-restrictions', 'mdp-remove-plan-restrictions', 'mdp-membership-error-message', 'message', 1, 5, false, 10 );
            manageEmptyFields( 'hide', 'mdp-membership-error-message', 1, 5, false, 10 );
            manageButtons( 'mdp-membership-error-message', 'mdp-remove-plan-restrictions', 'mdp-add-plan-restrictions', 10 );

            /** Init show based on select for personalities repeater */
            const $addPersonalityButton = $( '#mdp-add-bot-personality' );
            $addPersonalityButton.on( 'click', () => {
                showBasedOnSelectInRepeater( 'mdp_helper_ai_personalities_settings_ai_type', 'mdp-helper-ai-bot-personality', 'mdp-helper-ai-bot-personalities-fields', 10 );
            } );

            /** Init show based on select for user data repeater an add message button click */
            const $addButton = $( '#mdp-add-collect-data' );
            $addButton.on( 'click', () => {
                showBasedOnSelectInRepeater( 'mdp_helper_collect_data_settings_collect_data_field_validation', 'mdp-helper-validation', 'mdp-helper-collect-data-fields', 10 );
            } );

            /** Init show based on select for user data repeater an add message button click */
            const $addGeneralMessageButton = $( '#mdp-add-message-general' );
            $addGeneralMessageButton.on( 'click', () => {
                handleSwitchGeneralMessages();
                showBasedOnSelectInRepeater( 'mdp_helper_general_settings_general_message_condition', 'mdp-helper-message-conditions', 'mdp-helper-message-conditions-fields', 20 );
            } );


            /** Get user emails success/error messages repeaters */
            initRepeaterButtons( 'mdp-add-message-get_emails', 'mdp-remove-message-get_emails', 'mdp-helper-get_emails-message', 'message', 1,1 );
            manageEmptyFields( 'hide', 'mdp-helper-get_emails-message', 1, 1 );
            manageButtons( 'mdp-helper-get_emails-message', 'mdp-remove-message-get_emails', 'mdp-add-message-get_emails' );

            /** Get user emails repeaters */
            initRepeaterButtons( 'mdp-add-get-emails', 'mdp-remove-get-emails', 'mdp-helper-get-emails-bot-message', 'message', 1,1, false );
            manageEmptyFields( 'hide', 'mdp-helper-get-emails-bot-message', 1, 1, false );
            manageButtons( 'mdp-helper-get-emails-bot-message', 'mdp-remove-get-emails', 'mdp-add-get-emails' );

            /** Bot tts voices */
            initSingleSwitch( $( '#mdp_helper_behavior_settings_bot_tts_multilingual' ), 1, false, [], true );

            /** Remove empty rows in settings table */
            $( '.mdp-tab-content table.form-table td' ).each( function () {

                if ( $( this ).children().length === 0 ) {
                    $( this ).parent().remove();
                }

            } );

        }

        initUI();



    } );

} );


