/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./wp-content/plugins/helper/source/js/modules/_assistants.js":
/*!********************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_assistants.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkAssistantThread: () => (/* binding */ checkAssistantThread)
/* harmony export */ });
let sessionId = sessionStorage.getItem( 'mdpHelperBotSessionId' );
let localStorageId = localStorage.getItem( 'mdpHelperBotSessionId' );

function setThreadSessionId( hash ) {
    sessionId = hash;
    localStorageId = hash;
    sessionStorage.setItem( 'mdpHelperBotSessionId', hash );
    localStorage.setItem( 'mdpHelperBotSessionId', hash );
}

async function createNewThreadRequest( session_id ) {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
    formData.append( 'action', 'mdp_helper_create_thread' );
    formData.append( 'mdp_helper_session_id', session_id )

    return new Promise( ( resolve, reject ) => {
        xhr.open( 'POST', mdpHelper.endpoint, true );
        xhr.onload = () => resolve( xhr.responseText );
        xhr.onerror = () => reject( xhr.statusText );
        xhr.send( formData );
    } );
}

async function deleteUnusedThreadRequest( unusedSessionId ) {
    if ( !unusedSessionId ) { return; }

    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
    formData.append( 'action', 'mdp_helper_delete_thread' );
    formData.append( 'mdp_helper_session_id', unusedSessionId )

    return new Promise( ( resolve, reject ) => {
        xhr.open( 'POST', mdpHelper.endpoint, true );
        xhr.onload = () => resolve( xhr.responseText );
        xhr.onerror = () => reject( xhr.statusText );
        xhr.send( formData );
    } );
}

async function checkAssistantThread( hash ) {
    if ( !sessionId && !localStorageId ) {
        setThreadSessionId( hash );
        const resultJSON = await createNewThreadRequest( hash );
        const created = JSON.parse( resultJSON ).data.created;

        // Remove sessionId and localStorageId if thread was not created
        if ( !created ) {
            localStorage.removeItem( 'mdpHelperBotSessionId' );
            sessionStorage.removeItem( 'mdpHelperBotSessionId' );
        }
    } else if ( sessionId !== localStorageId ) {
        // Delete unused thread
        await deleteUnusedThreadRequest( localStorageId );
        setThreadSessionId( hash );
        await createNewThreadRequest( hash );
    } else {

    }
}

/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_avatar.js":
/*!****************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_avatar.js ***!
  \****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createAvatar: () => (/* binding */ createAvatar)
/* harmony export */ });
// noinspection DuplicatedCode

/**
 * @param window.mdpHelper
 */

/**
 * Creates avatar markup
 * @param isVisitor
 * @returns {*}
 */
function createAvatar( isVisitor ) {

    const { avatar } = window.mdpHelper;

    if ( isVisitor ) {

        if ( avatar.user.type === 'none' ) { return; }

        const avatarMarkup = document.createElement( 'div' );
        avatarMarkup.classList.add( `mdp-helper-visitor-avatar`, 'mdp-helper-avatar' );
        avatarMarkup.innerHTML = avatar.user.content !== '' ? avatar.user.content :
            avatar.user.url ? `<img src="${ avatar.user.url }" alt="${ avatar.user.alt }">` : '';

        return avatarMarkup;

    } else {

        if ( avatar.bot.type === 'none' ) { return; }

        const avatarMarkup = document.createElement( 'div' );
        avatarMarkup.classList.add( `mdp-helper-bot-avatar`, 'mdp-helper-avatar' );
        avatarMarkup.innerHTML = avatar.bot.content !== '' ? avatar.bot.content :
            avatar.bot.url ? `<img src="${ avatar.bot.url }" alt="${ avatar.bot.alt }">` : '';

        return avatarMarkup;

    }

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_bot-menu.js":
/*!******************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_bot-menu.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createBotMenuButton: () => (/* binding */ createBotMenuButton)
/* harmony export */ });
/**
 * Creates button markup for chat menu
 * @param type
 * @param title
 * @returns {*}
 */
function createBotMenuButton( type, title = '' ) {

    const button = document.createElement( 'button' );
    button.classList.add( 'mdp-bot-menu-button' );
    button.setAttribute( 'data-button-type', type );
    button.title = title;
    button.innerHTML = title;

    return button;

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_form-validator.js":
/*!************************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_form-validator.js ***!
  \************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   customValidation: () => (/* binding */ customValidation),
/* harmony export */   numberValidation: () => (/* binding */ numberValidation),
/* harmony export */   validateEmail: () => (/* binding */ validateEmail)
/* harmony export */ });
/**
 * Validates email on user input
 * @param email
 * @returns {boolean}
 */
function validateEmail( email ) {

    const re = /\S+@\S+\.\S+/;
    return re.test( email );

}

/**
 * Validates user input based on custom regex
 * @param message
 * @param pattern
 * @returns {boolean}
 */
function customValidation( message, pattern ) {

    const re = new RegExp( pattern );
    return re.test( message );

}

/**
 * Accepts only numbers
 * @param message
 * @param max
 * @param min
 * @returns {boolean}
 */
function numberValidation( message, max, min ) {

    const re = /^[0-9]+$/;

    if ( re.test( max ) && re.test( message ) ) {
        return +message <= +max
    }

    if ( re.test( min ) && re.test( message ) ) {
        return +message >= +min;
    }

    return re.test( message );

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_local-storage.js":
/*!***********************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_local-storage.js ***!
  \***********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearLocalStorage: () => (/* binding */ clearLocalStorage),
/* harmony export */   setLocalSessionStorage: () => (/* binding */ setLocalSessionStorage)
/* harmony export */ });
/**
 * Clear timeout after x hours
 * @param hours
 */
function clearLocalStorage( hours ) {
    const now = new Date().getTime();
    const setupTime = localStorage.getItem( 'mdpSetupQuestionTime' );

    if ( !setupTime ) { return; }

    if ( now - setupTime > hours * 60 * 60 * 1000 ) {
        localStorage.removeItem( 'mdpUserQuestion' );
        localStorage.removeItem( 'mdpBotAnswer' );
    }
}

/**
 * Set last question and answer to it to local storage and session storage
 * @param question
 * @param answer
 */
function setLocalSessionStorage( question, answer ) {
    const now = new Date().getTime();
    localStorage.setItem( 'mdpSetupQuestionTime', now + '' );


    /** Set user question */
    sessionStorage.setItem( 'mdpUserQuestion', question );
    localStorage.setItem( 'mdpUserQuestion', question );

    /** Set bot answer */
    sessionStorage.setItem( 'mdpBotAnswer', answer );
    localStorage.setItem( 'mdpBotAnswer', answer );
}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_logs.js":
/*!**************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_logs.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearLogString: () => (/* binding */ clearLogString),
/* harmony export */   initLogs: () => (/* binding */ initLogs),
/* harmony export */   updateLogs: () => (/* binding */ updateLogs)
/* harmony export */ });
const botLogs = mdpHelper.botLogs === 'on';
let sessionId = sessionStorage.getItem( 'mdpHelperSessionId' );
let localStorageId = localStorage.getItem( 'mdpHelperSessionId' );
let logId = localStorage.getItem( 'mdpHelperLogId' );
let logString = localStorage.getItem( 'mdpHelperLog' );

function setSessionId( hash ) {
    sessionId = hash;
    localStorageId = hash;
    sessionStorage.setItem( 'mdpHelperSessionId', hash );
    localStorage.setItem( 'mdpHelperSessionId', hash );
}

async function createLog( hash ) {
    setSessionId( hash );
    const logIdJson = await createNewLog();

    return JSON.parse( logIdJson ).data;
}

function createNewLog() {
    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
    formData.append( 'action', 'mdp_helper_create_new_log' );

    return new Promise( ( resolve, reject ) => {
        xhr.open( 'POST', mdpHelper.endpoint, true );
        xhr.onload = () => resolve( xhr.responseText );
        xhr.onerror = () => reject( xhr.statusText );
        xhr.send( formData );
    } );
}

function updateLogText( message ) {
    // Update log string
    if ( message ) {
        logString += message;
        localStorage.setItem( 'mdpHelperLog', logString );
    }
}

function updateLog() {

    // Exit if no log id
    if ( !logId || localStorageId !== sessionId ) { return; }

    const xhr = new XMLHttpRequest();
    const formData = new FormData();

    formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
    formData.append( 'action', 'mdp_helper_update_log' );
    formData.append( 'mdp_helper_log_id', logId );
    formData.append( 'mdp_helper_log_text', logString );

    return new Promise( ( resolve, reject ) => {
        xhr.open( 'POST', mdpHelper.endpoint, true );
        xhr.onload = () => resolve( xhr.responseText );
        xhr.onerror = () => reject( xhr.statusText );
        xhr.send( formData );
    } );
}

async function initLogs( hash ) {
    if ( !botLogs ) { return; }

    // Init bot logs string
    if ( botLogs ) {
        if ( !logString ) {
            localStorage.setItem( 'mdpHelperLog', '' );
        }
    }

    // Set logs attributes
    if ( !localStorageId && !sessionId ) {
        logId = await createLog( hash );
        localStorage.setItem( 'mdpHelperLogId', logId );
    } else if ( localStorageId !== sessionId ) {
        logId = await createLog( hash );
        localStorage.setItem( 'mdpHelperLogId', logId );
    }
}

function clearLogString() {
    if ( !botLogs ) { return; }

    if ( localStorageId !== sessionId ) {
        logString = '';
        localStorage.setItem( 'mdpHelperLog', logString );
        logId = '';
        localStorage.setItem( 'mdpHelperLogId', logId );
    }
}

async function updateLogs( isVisitor, messageText ) {

    if ( !botLogs ) { return; }

    if ( !isVisitor ) {
        updateLogText( `bot: ${messageText};` );
        await updateLog();
    } else {
        updateLogText( `user: ${messageText};` );
    }
}

/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_message-link.js":
/*!**********************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_message-link.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   renderLinkWidget: () => (/* binding */ renderLinkWidget)
/* harmony export */ });
/**
 * @param translations
 * @param translations.readMoreText
 */

/** Render widget link */
function renderLinkWidget( linkWidgetData, link ) {

    const { translations } = window.mdpHelper;

    const linkWidgetWrapper = document.createElement( 'div' );
    linkWidgetWrapper.classList.add( 'mdp-helper-link-widget-container' );
    linkWidgetWrapper.classList.add( 'mdp-helper-bot-message' );
    linkWidgetWrapper.classList.add( 'mdp-helper-message' );

    /** Create thumbnail container */
    if ( linkWidgetData.thumbnail ) {
        const postThumbnail = document.createElement( 'div' );
        postThumbnail.classList.add( 'mdp-helper-link-widget-post-thumbnail' );
        postThumbnail.innerHTML += JSON.parse( linkWidgetData.thumbnail );
        linkWidgetWrapper.appendChild( postThumbnail );
    }

    /** Create content container */
    const linkWidgetContent = document.createElement( 'div' );
    linkWidgetContent.classList.add( 'mdp-helper-link-widget-content' );

    /** Create post title */
    const postTitle = document.createElement( 'h6' );
    postTitle.classList.add( 'mdp-helper-link-widget-post-title' );
    postTitle.innerText = linkWidgetData.post_title;

    /** Create post excerpt */
    const postExcerpt = document.createElement( 'p' );
    postExcerpt.classList.add( 'mdp-helper-link-widget-post-excerpt' );
    postExcerpt.innerText = linkWidgetData.post_excerpt;

    /** Create read more link */
    const readMoreLink = document.createElement( 'a' );
    readMoreLink.classList.add( 'mdp-helper-link-widget-read-more' );
    readMoreLink.setAttribute( 'href', link );
    readMoreLink.target = '_blank';
    readMoreLink.innerText = translations.readMoreText;

    /** Append all widget blocks to chat box */

    linkWidgetContent.appendChild( postTitle );

    linkWidgetContent.appendChild( postExcerpt );

    linkWidgetContent.appendChild( readMoreLink );

    linkWidgetWrapper.appendChild( linkWidgetContent );

    return linkWidgetWrapper;

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_message-upper-line.js":
/*!****************************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_message-upper-line.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   messageUpperLine: () => (/* binding */ messageUpperLine)
/* harmony export */ });
/* harmony import */ var _webspeech__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_webspeech */ "./wp-content/plugins/helper/source/js/modules/_webspeech.js");
/**
 * @param messageSignature
 * @param messageSignature.nameEnabled
 * @param messageSignature.timestampEnabled
 * @param messageSignature.userName
 * @param messageSignature.botName
 * @param messageSignature.timestampFormat12
 */



/**
 * Add upper-line to message
 * @param isBotMessage
 * @param $chatBox
 * @param returnToMainMenuAction
 */
function messageUpperLine( isBotMessage, $chatBox, returnToMainMenuAction ) {

    const { messageSignature } = window.mdpHelper;
    if ( ! messageSignature ) { return; }

    if (
        ! messageSignature.nameEnabled &&
        ! messageSignature.timestampEnabled &&
        ! messageSignature.buttonsEnabled &&
        ! messageSignature.copyBotTextButtonEnabled ) { return; }

    const messageTypeClass = isBotMessage ? 'bot' : 'visitor';

    // Upper-line container
    const signatureLine = document.createElement( 'div' );
    signatureLine.classList.add( 'mdp-helper-message-signature' );
    signatureLine.classList.add( `mdp-helper-${ messageTypeClass }-signature` );

    // Name
    if ( messageSignature.nameEnabled ) {

        const nameSpan = document.createElement( 'span' );
        nameSpan.classList.add( 'mdp-helper-message-name' );
        nameSpan.innerHTML = isBotMessage ? messageSignature.botName : messageSignature.userName;

        signatureLine.appendChild( nameSpan );

    }

    // Timestamp
    if ( messageSignature.timestampEnabled ) {

        const timeSpan = document.createElement( 'span' );
        timeSpan.classList.add( 'mdp-helper-message-timestamp' );
        timeSpan.innerHTML = new Date().toLocaleTimeString(
            document.documentElement.lang,
            {
                hour12: messageSignature.timestampFormat12,
                hour: 'numeric',
                minute: 'numeric'
            }
        );

        signatureLine.appendChild( timeSpan );

    }

    // Return to main menu button
    if ( isBotMessage ) {
        const returnButton = document.createElement( 'button' );
        returnButton.classList.add( 'mdp-helper-main-menu-button' );

        returnButton.addEventListener( 'click', async () => {

            await returnToMainMenuAction();

            (0,_webspeech__WEBPACK_IMPORTED_MODULE_0__.stopSpeechSynthesis)();

        } );

        if ( window.mdpHelper.messageSignature.buttonsEnabled ) {

            returnButton.title = mdpHelper.translations.botCommands.return;
            returnButton.innerHTML = `${ mdpHelper.returnButtonIcon }<span class="mdp-helper-main-menu-button-caption">${ mdpHelper.translations.botCommands.return }</span>`;
            signatureLine.appendChild( returnButton );

        }

    }

    // Copy bot text button
    if ( isBotMessage ) {
        const copyTextButton = document.createElement( 'button' );
        copyTextButton.classList.add( 'mdp-helper-main-menu-button' );
        copyTextButton.addEventListener( 'click', ( event ) => {
            const $signatureContainer = event.target.closest( '.mdp-helper-message-signature' );
            const $copyTextCaption = event.target.classList.contains( 'mdp-helper-main-menu-button-caption' ) ?
                event.target :
                event.target.querySelector( '.mdp-helper-main-menu-button-caption' );
            const $botMessageContainer = $signatureContainer.nextSibling;
            const botMessage = $botMessageContainer.querySelector( '.mdp-helper-bot-message' ).innerText;
            navigator.clipboard.writeText( botMessage );
            $copyTextCaption.innerText = mdpHelper.translations.botCommands.successfullyCopied;

            setTimeout( () => {
                $copyTextCaption.innerText = mdpHelper.translations.botCommands.copy;
            }, 3000 )

        } );

        if ( window.mdpHelper.messageSignature.copyBotTextButtonEnabled ) {

            copyTextButton.title = mdpHelper.translations.botCommands.copy;
            copyTextButton.innerHTML = `${ mdpHelper.copyBotTextButtonIcon }<span class="mdp-helper-main-menu-button-caption">${ mdpHelper.translations.botCommands.copy }</span>`;
            signatureLine.appendChild( copyTextButton );

        }

    }

    $chatBox.appendChild( signatureLine );

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_toolbar.js":
/*!*****************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_toolbar.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   muteHelperWebspeech: () => (/* binding */ muteHelperWebspeech)
/* harmony export */ });
/* harmony import */ var _webspeech__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./_webspeech */ "./wp-content/plugins/helper/source/js/modules/_webspeech.js");


/**
 * Mute button
 */
function muteHelperWebspeech() {

    const attr = 'data-mute';
    const muteButton = document.querySelector( '.mdp-helper-mute-button' );
    if ( ! muteButton ) { return; }

    const { translations } = window.mdpHelper;
    const muteStatus = document.createElement( 'b' );

    // Set default value
    if ( ! muteButton.getAttribute( attr ) ) {

        muteButton.setAttribute( attr, 'false' );

        muteStatus.innerHTML = translations.on;
        muteButton.appendChild( muteStatus );

    }

    muteButton.addEventListener( 'click', () => {

        if ( JSON.parse( muteButton.getAttribute( attr ) ) ) {

            muteButton.setAttribute( attr, 'false' );
            muteStatus.innerHTML = translations.on;

        } else {

            muteButton.setAttribute( attr, 'true' );
            muteStatus.innerHTML = translations.off;

            (0,_webspeech__WEBPACK_IMPORTED_MODULE_0__.stopSpeechSynthesis)();

        }

    } );

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_utilities.js":
/*!*******************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_utilities.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getMessageDelay: () => (/* binding */ getMessageDelay),
/* harmony export */   manageSendForm: () => (/* binding */ manageSendForm)
/* harmony export */ });
/**
 * @param window.mdpHelper
 */

/**
 * Return message displaying delay
 * @param messageLength
 * @returns {*|number}
 */
function getMessageDelay( messageLength ) {

    const { botRespondDelay, botTypingAnimation, botTypingAnimationDelay, randomizeBotRespondDelay } = window.mdpHelper;

    const respondDelay = parseFloat( botRespondDelay );
    const typeAnimationDelay = botTypingAnimation === 'on' ? + botTypingAnimationDelay : 0;

    if ( randomizeBotRespondDelay ) {

        const randomDelay = Math.floor( Math.random() * respondDelay );
        return randomDelay + typeAnimationDelay * messageLength;

    } else {

        return respondDelay + typeAnimationDelay * messageLength;

    }

}

/**
 * Show or hide send form depending on the argument
 * @param show
 */
function manageSendForm( show ) {

    if ( typeof show === 'undefined' ) { return; }

    const $formContainer = document.querySelector( '.mdp-helper-chatbot-footer-form' );
    if ( ! $formContainer ) { return; }

    show ? $formContainer.classList.remove( 'mdp-helper-form-disabled' ) : $formContainer.classList.add( 'mdp-helper-form-disabled' );

}


/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_ux.js":
/*!************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_ux.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   buttonSquareRatio: () => (/* binding */ buttonSquareRatio),
/* harmony export */   disablePageScrollOnFullSize: () => (/* binding */ disablePageScrollOnFullSize),
/* harmony export */   disableSendButton: () => (/* binding */ disableSendButton),
/* harmony export */   scrollingEdges: () => (/* binding */ scrollingEdges),
/* harmony export */   setMaxHeightForPopup: () => (/* binding */ setMaxHeightForPopup)
/* harmony export */ });
/**
 * Add gradient to chatbot messages container if scrollable
 */
function scrollingEdges() {

    const scrollContainer = document.querySelector( '.mdp-helper-messages-wrapper' );
    if ( ! scrollContainer ) { return; }

    const scrollInner = document.querySelector( '.mdp-helper-chatbot-messages-container' );
    if ( ! scrollInner ) { return; }

    scrollContainer.addEventListener( 'scroll', () => {

        // Add right padding to scroll container
        if ( ! scrollContainer.classList.contains( 'mdp-helper-wrapper-scrolled' ) ) {
            scrollContainer.classList.add( 'mdp-helper-wrapper-scrolled' );
        }

        const chatBotMessages = document.querySelector( '.mdp-helper-chatbot-messages' );
        if ( ! chatBotMessages ) { return; }

        // Top gradient
        const containerTop = scrollContainer.getBoundingClientRect().top;
        containerTop > scrollInner.getBoundingClientRect().top ?
            chatBotMessages.classList.add( 'mdp-helper-wrapper-gradient-top' ):
            chatBotMessages.classList.remove( 'mdp-helper-wrapper-gradient-top' );

        // Bottom gradient
        const containerBottom = scrollContainer.getBoundingClientRect().bottom;
        containerBottom + 1 < scrollInner.getBoundingClientRect().bottom ?
            chatBotMessages.classList.add( 'mdp-helper-wrapper-gradient-bottom' ):
            chatBotMessages.classList.remove( 'mdp-helper-wrapper-gradient-bottom' );

    } );

}

/**
 * Disable send button if message input is empty
 */
function disableSendButton() {

    const messageInput = document.querySelector( '#mdp-helper-input-messages-field' );
    const sendButton = document.querySelector( '.mdp-helper-send-message-button' );
    if ( ! messageInput || ! sendButton ) { return; }

    disableButton( sendButton, ! messageInput.value.length > 0 );

    // Input from keyboard
    messageInput.addEventListener( 'input', () => {

        disableButton( sendButton, ! messageInput.value.length > 0 );

    } );

    // Input from voice recognition
    window.addEventListener( 'helper-recognition-end', () => {

        disableButton( sendButton, ! messageInput.value.length > 0 );

    } );

}

/**
 * Disable button if condition is true
 * @param button
 * @param condition
 */
function disableButton( button, condition ) {

    if ( ! button ) { return; }

    condition ?
        button.setAttribute( 'disabled', 'disabled' ):
        button.removeAttribute( 'disabled' );

}

/**
 * Keep button ratio
 */
function buttonSquareRatio() {

    const sendButton = document.querySelector( '.mdp-helper-send-message-button' );
    if ( ! sendButton ) { return; }

    if ( ! sendButton.querySelector( '.mdp-helper-send-message-button-caption' ) ) {

        sendButton.style.minWidth = sendButton.offsetHeight + 'px';

    }

}

/**
 * Disable page scrolling on full size opened popup
 */
function disablePageScrollOnFullSize( isFullSize ) {

    // Return if full size on mobile turned off
    if ( !isFullSize ) { return; }

    const $body = document.body;
    const $hiddenChat = document.querySelector( '.mdp-helper-hide-chat' );

    if ( $hiddenChat ) {
        $body.classList.remove( 'mdp-helper-full-size-mobile-body' );
    } else {
        $body.classList.add( 'mdp-helper-full-size-mobile-body' );
    }

}

function setMaxHeightForPopup( maxPopupHeight ) {
    if ( !maxPopupHeight ) { return; }

    const $floatButtonWrapper = document.querySelector( '.mdp-helper-open-button-wrapper' );
    const $chatBotBox = document.querySelector( '.mdp-helper-chatbot-box' );
    const computedBoxStyles = window.getComputedStyle($chatBotBox);
    const boxMarginTop = computedBoxStyles.marginTop;
    const boxMarginBottom = computedBoxStyles.marginBottom;
    const floatButtonHeight = !$floatButtonWrapper ? '0px' : `${$floatButtonWrapper.clientHeight}px`;

    $chatBotBox.style.maxHeight = `calc(100vh - (${boxMarginTop} + ${boxMarginBottom} + ${floatButtonHeight}))`;
}

/***/ }),

/***/ "./wp-content/plugins/helper/source/js/modules/_webspeech.js":
/*!*******************************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/modules/_webspeech.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initHelperWebspeech: () => (/* binding */ initHelperWebspeech),
/* harmony export */   initRecognition: () => (/* binding */ initRecognition),
/* harmony export */   stopSpeechSynthesis: () => (/* binding */ stopSpeechSynthesis)
/* harmony export */ });
// noinspection DuplicatedCode,JSUnresolvedReference

/**
 * @param translations.recognitionNotSupported
 * @param tts.multilingual
 */

/**
 * Add event listener for helper-speak event
 */
function initHelperWebspeech() {

    window.addEventListener(
        'helper-speak',
        helperWebspeech,
        false
    );

    window.speechSynthesis.onvoiceschanged = () => {

        window.addEventListener(
            'helper-speak',
            helperWebspeech,
            false
        );

    }

}

/**
 * Init recognition
 */
function initRecognition() {

    const { stt } = window.mdpHelper;
    if ( ! stt ) { return; }
    if ( ! stt.enabled ) { return; }

    const recButton = document.querySelector( '.mdp-helper-bot-button-recognize' );
    if ( ! recButton ) { return; }

    const recognition = setRecognitionProperties();
    if ( ! recognition ) { return; }

    recButton.addEventListener( 'click', () => {

        // Make button disabled to prevent multiple clicks
        if ( recButton.disabled ) {
            // noinspection JSUnresolvedReference
            recognition.stop(); // Stop recognition if it's in progress
            return;
        }
        recButton.disabled = true;

        // noinspection JSUnresolvedReference
        recButton.getAttribute( 'data-in-progress' ) === 'true' ?
            recognition.stop() : recognition.start();

    } );

    addRecognitionEvents( recognition, recButton );

}

/**
 * Add event listener for recognitions
 * @param recognition
 * @param recButton
 */
function addRecognitionEvents( recognition, recButton ) {

    // Start recognition event
    recognition.addEventListener( 'start', () => {

        const event = new CustomEvent( 'helper-recognition-start' );
        window.dispatchEvent( event );

        recButton.setAttribute( 'data-in-progress', 'true' );

    } );

    // Stop recognition event
    recognition.addEventListener( 'end', () => {

        const event = new CustomEvent( 'helper-recognition-end' );
        window.dispatchEvent( event );

        recButton.setAttribute( 'data-in-progress', 'false' );
        recButton.disabled = false;

    } );

    // Speech recognition event
    recognition.addEventListener( 'result', ( e ) => {

        const { resultIndex } = e;
        const { transcript } = e.results[ resultIndex ][ 0 ];

        const messageInput = document.querySelector( '#mdp-helper-input-messages-field' )
        messageInput.value = transcript.charAt( 0 ).toUpperCase() + transcript.slice( 1 );

        const event = new CustomEvent( 'helper-recognition', { detail: { message: transcript } } );
        window.dispatchEvent( event );

    } );

    // Error recognition event
    recognition.addEventListener( 'error', ( e ) => {

        const { translations } = window.mdpHelper;

        // noinspection JSUnresolvedReference
        console.warn( `${ translations.recognitionError }: ${ e.error }` );

        const event = new CustomEvent( 'helper-recognition-error', { detail: { message: e.error } } );
        window.dispatchEvent( event );

        recButton.setAttribute( 'data-in-progress', 'false' );
        recButton.disabled = false;

    } );

}

/**
 * Set recognition properties
 */
function setRecognitionProperties() {

    let recognition = null;

    if ( 'SpeechRecognition' in window ) {

        recognition = new SpeechRecognition();

    } else if ( 'webkitSpeechRecognition' in window ) {

        recognition = new webkitSpeechRecognition();

    } else {

        const { translations } = window.mdpHelper;
        console.warn( translations.recognitionNotSupported );

        const formContainer = document.querySelector( '.mdp-helper-form-with-recognize' );
        formContainer.classList.remove( 'mdp-helper-form-with-recognize')

        return recognition;

    }

    recognition.lang = document.documentElement.lang;
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    return recognition;

}

/**
 * Handle helper-speak event
 * @param e
 */
function helperWebspeech( e ) {

    if ( e.detail.message === undefined || e.detail.message === '' ) { return; }

    const muteButton = document.querySelector( '.mdp-helper-mute-button' );
    if ( muteButton && muteButton.getAttribute( 'data-mute' ) === 'true' ) { return; }

    const synth = window.speechSynthesis;
    if ( ! synth ) { return; }

    const { tts } = window.mdpHelper;
    const lang = document.documentElement.lang;

    let utterThis = new SpeechSynthesisUtterance();

    if ( tts.multilingual || tts.voice === '' ) {

        // No voice selected
        const exactMatch = synth.getVoices().find( ( voice ) => voice.lang === lang );
        if ( exactMatch ) {

            // Exactly the same to the page language
            utterThis.voice = exactMatch;
            utterThis.lang = document.documentElement.lang;

        } else {

            // Similar to the page language
            utterThis.voice = synth.getVoices().find( ( voice ) => voice.lang.startsWith( lang ) );
            utterThis.lang = utterThis.voice.lang;

        }

        // Fallback for the case when no voice is available for the page language
        if ( ! utterThis.voice ) {

            if ( tts.voice === '' ) {

                // Use default british english voice if
                utterThis.voice = synth.getVoices().find((voice) => voice.lang.startsWith('en-GB'));
                utterThis.lang = utterThis.voice.lang;

            } else {

                // Use first available voice from the list
                tts.voice.split( ',' ).forEach( ( voiceName ) => {

                    if ( utterThis.voice === null ){
                        utterThis.voice = synth.getVoices().find( ( voice ) => voice.name === voiceName.trim() );
                        utterThis.lang = utterThis.voice.lang;
                    }

                } );

            }

        }

    } else {

        // Voice selected
        tts.voice.split( ',' ).forEach( ( voiceName ) => {
            voiceName = voiceName.split( ';' )[0];

            if ( utterThis.voice === null ){
                const voice = synth.getVoices().find( ( voice ) => voice.name === voiceName.trim() );
                if ( voice ) {
                    utterThis.voice = voice;
                    utterThis.lang = utterThis.voice.lang;
                }
            }
        } );

    }

    utterThis.text = e.detail.message;

    synth.speak( utterThis );

}

/**
 * Stop speech synthesis
 */
function stopSpeechSynthesis() {

    const synth = window.speechSynthesis;
    if ( ! synth ) { return; }

    if ( synth.speaking ) {
        synth.cancel();
    }

}


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*******************************************************!*\
  !*** ./wp-content/plugins/helper/source/js/helper.js ***!
  \*******************************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modules_avatar__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./modules/_avatar */ "./wp-content/plugins/helper/source/js/modules/_avatar.js");
/* harmony import */ var _modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/_bot-menu */ "./wp-content/plugins/helper/source/js/modules/_bot-menu.js");
/* harmony import */ var _modules_utilities__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/_utilities */ "./wp-content/plugins/helper/source/js/modules/_utilities.js");
/* harmony import */ var _modules_webspeech__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./modules/_webspeech */ "./wp-content/plugins/helper/source/js/modules/_webspeech.js");
/* harmony import */ var _modules_toolbar__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./modules/_toolbar */ "./wp-content/plugins/helper/source/js/modules/_toolbar.js");
/* harmony import */ var _modules_ux__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./modules/_ux */ "./wp-content/plugins/helper/source/js/modules/_ux.js");
/* harmony import */ var _modules_message_upper_line__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./modules/_message-upper-line */ "./wp-content/plugins/helper/source/js/modules/_message-upper-line.js");
/* harmony import */ var _modules_local_storage__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./modules/_local-storage */ "./wp-content/plugins/helper/source/js/modules/_local-storage.js");
/* harmony import */ var _modules_message_link__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./modules/_message-link */ "./wp-content/plugins/helper/source/js/modules/_message-link.js");
/* harmony import */ var _modules_form_validator__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./modules/_form-validator */ "./wp-content/plugins/helper/source/js/modules/_form-validator.js");
/* harmony import */ var _modules_logs__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./modules/_logs */ "./wp-content/plugins/helper/source/js/modules/_logs.js");
/* harmony import */ var _modules_assistants__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./modules/_assistants */ "./wp-content/plugins/helper/source/js/modules/_assistants.js");
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
 * Helper main function
 */
const mdpHelperBot = async function () {
    const $botBox = document.querySelector( '.mdp-helper-box' );
    const $chatBox = document.querySelector( '.mdp-helper-chatbot-messages-container' );
    const $messagesWrapper = document.querySelector( '.mdp-helper-messages-wrapper' );
    const typeAnimationDelay = mdpHelper.botTypingAnimation === 'on' ? +mdpHelper.botTypingAnimationDelay : 0;
    const respondDelay = parseFloat( mdpHelper.botRespondDelay );
    const sendMessageAudio = mdpHelper.sendMessageAudio !== '' ? new Audio( mdpHelper.sendMessageAudio  ) : '';
    const receiveMessageAudio = mdpHelper.receiveMessageAudio !== '' ? new Audio( mdpHelper.receiveMessageAudio  ) : '';
    const enabledGoogleAnalytics = mdpHelper.enabledGoogleAnalytics === 'on';
    const limitUsersRequests = mdpHelper.limitUsersRequests === 'on';
    const enableMoreHelpMessage = mdpHelper.enableMoreHelpMessage;
    const acceptanceDisplaying = mdpHelper.acceptanceCheckBox;
    const botPersonalityType = mdpHelper.currentBotPersonalityType;
    const messageOnloadPreloader = mdpHelper.showMessagePreloader;
    const enabledBotRespondDelay = mdpHelper.botRespondDelayEnabled;
    const fullSizeOnMobile = mdpHelper.fullSizeOnMobile;
    const faqCategoryIcon = mdpHelper.faqCategoryIcon;
    const maxPopupHeight = mdpHelper.maxPopupHeight;
    const autoOpenPopup = mdpHelper.autoOpenPopup;
    const autoOpenPopupDelay = parseFloat(mdpHelper.autoOpenPopupDelay);
    const acceptanceWrapper = document.querySelector( '.mdp-helper-user-data-acceptance-wrapper' );
    const acceptanceCheckBox = document.querySelector( '.mdp-helper-user-data-acceptance' );
    let userAcceptedLocalstorage = localStorage.getItem( 'mdpAcceptedCollectData' );
    let notConfirmedAcceptanceMenu = false;
    let acceptanceConfirmed = false;
    let faqTimeout;

    const { translations, botFeatures, tts, stt } = window.mdpHelper;

    let userDataMessages = {};
    let botCollectDataMessages = {};
    let isDataCollecting = false;
    let isSendingEmail = false;
    let isBotCommandWaiting = false;
    let messagesCounter = 0;
    let menuCounter = 0;
    let currentMessageLength = 0;
    let faqCategory = '';
    let isValidated = true;
    let logsInited = false;

    let stopWritingAnimation = false;

    if ( !$botBox ) { return; }

    /** Init Text to Speech */
    if ( tts.enabled ) {
        (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.initHelperWebspeech)();
        (0,_modules_toolbar__WEBPACK_IMPORTED_MODULE_4__.muteHelperWebspeech)();
    }

    /** Init Speech to Text */
    if ( stt.enabled ) {
        (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.initRecognition)();
    }

    /** Check if log string needed to be cleared */
    (0,_modules_logs__WEBPACK_IMPORTED_MODULE_10__.clearLogString)();

    /** Display bot onload */
    $botBox.style.display = 'block';

    /** Clear local storage after X hours */
    (0,_modules_local_storage__WEBPACK_IMPORTED_MODULE_7__.clearLocalStorage)( +mdpHelper.localStorageHours );

    /** UX improvements */
    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.scrollingEdges)();
    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disableSendButton)();

    async function playMessageSound( sound ) {
        if ( sound !== '' ) {
            sound.pause();
            sound.currentTime = 0;
            await sound.play();
        }
    }

    /** Render last asked question and answer to it */
    function renderLastPostedMessage() {

        const { botMemory } = window.mdpHelper;
        if ( ! botMemory ) { return '' }

        const isSessionStorage = sessionStorage.getItem( 'mdpUserQuestion' ) && sessionStorage.getItem( 'mdpUserQuestion' ).length > 0;
        const isLocalStorage = localStorage.getItem( 'mdpUserQuestion' ) && localStorage.getItem( 'mdpUserQuestion' ).length > 0;

        /** Return if there is no last questions */
        if ( !isSessionStorage && !isLocalStorage  ) { return ''; }

        const userQuestion = isSessionStorage ? sessionStorage.getItem( 'mdpUserQuestion' ) : localStorage.getItem( 'mdpUserQuestion' );
        const botAnswer = isSessionStorage ? sessionStorage.getItem( 'mdpBotAnswer' ) : localStorage.getItem( 'mdpBotAnswer' );

        const botAnswerObj = JSON.parse( botAnswer );

        renderMessage( true, userQuestion, true );


        if ( botAnswerObj && botAnswerObj.link_widget_data ) {
            renderMessage( false, botAnswer, true, true, botAnswerObj.link_widget_data );
        } else {
            renderMessage( false, botAnswerObj.message, true );
        }
    }

    function scrollOnContentResize() { $messagesWrapper.scrollTop = $messagesWrapper.scrollHeight; }

    /** Scroll when new messages appears */
    new ResizeObserver( scrollOnContentResize ).observe( $chatBox );

    function typeWriterAnimation(message, $messageBox) {
        let i = 0,
            isTag,
            text;

        ( function type() {

            /** Exit if all characters were rendered */
            if ( i >= message.length ) { return; }

            if ( stopWritingAnimation ) { return; }

            text = message.slice( 0, ++i );
            if ( text > message ) return;

            $messageBox.innerHTML = text;

            let char = text.slice( -1 );
            if( char === '<' ) isTag = true;
            if( char === '>' ) isTag = false;

            if ( isTag ) return type();
            setTimeout( type, typeAnimationDelay );
        }() );
    }

    /** Creates bot initial menu */
    function botStartMenu() {
        const botMenu = document.createElement( 'div' );
        botMenu.classList.add( 'mdp-bot-menu' );
        const botFeatures = mdpHelper.botFeatures;
        botFeatures.forEach( feature => {
            switch ( feature ) {
                case 'faq':
                    botMenu.appendChild( (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'faq', mdpHelper.faqButtonName ) );
                    break;
                case 'collect_data':
                    botMenu.appendChild( (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'collectData', mdpHelper.collectDataButtonName ) );
                    break;
                case 'get_user_email':
                    botMenu.appendChild( (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'sendEmail', mdpHelper.sendEmailButton ) );
                    break;
            }
        } );

        return botMenu;
    }

    function createInfoCommandsMessage() {

        const botCommandsText = translations.botCommands;

        renderMessage( false,
            `<p>${botCommandsText.title}</p>` +
            `<p>1. <button class="mdp-helper-command" title="${botCommandsText.return}">/return</button> - ${botCommandsText.return}</p>` +
            `<p>2. <button class="mdp-helper-command" title="${botCommandsText.info}">/info</button> - ${botCommandsText.info}</p>`
        );

        // Lister for the click on command button after message is rendered
        window.addEventListener( 'helper-message-rendered', function( event ) {

            if ( ! event.detail.isBotCommands ) { return; }

            event.detail.element.addEventListener( 'click', async function( event ){

                document.querySelector( '#mdp-helper-input-messages-field' ).value = event.target.textContent;
                (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disableSendButton)();

            } );

        } );

        isBotCommandWaiting = true;

    }

    /** Bot menu logic */
    function botMenuActions() {
        setTimeout( () => {
            const $menuButtons = document.querySelectorAll( '.mdp-bot-menu-button' );
            $menuButtons.forEach( $menuButton => {
                $menuButton.addEventListener( 'click', event => {
                    switch ( event.target.dataset.buttonType ) {
                        case 'faq':
                            (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
                            removePreviousMenu();
                            createFaqMenu();
                            break;
                        case 'collectData':
                            (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
                            removePreviousMenu();
                            createCollectDataMenu();
                            break;
                        case 'sendEmail':
                            (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
                            removePreviousMenu();
                            createSendEmailMenu();
                            break;
                        case 'noMoreHelp':
                            createEndConversationMenu();
                            break;
                        case 'moreHelp':
                            createTryAgainConversation();
                            break;
                    }
                } );
            } );
        }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );
    }


    /** Chat Bot start menu */
    async function initStartMenu( initialMessage = true ) {

        isDataCollecting = false;
        notConfirmedAcceptanceMenu = false;
        isSendingEmail = false;
        messagesCounter = 0;

        /** Hide acceptance checkbox */
        if ( acceptanceWrapper ) { acceptanceWrapper.style.display = 'none'; }

        const botMessage = await getBotDialogMessage( '', 'general', true );
        const botMessageData = JSON.parse( botMessage );

        if ( initialMessage ) {
         await renderMessage( false, botMessageData.data );
        }

        // Show bot menu if enabled bot features requires it
        if ( botFeatures.includes( 'faq' ) || botFeatures.includes( 'collect_data' ) || botFeatures.includes( 'get_user_email' ) ) {

            setTimeout( () => {
                $chatBox.appendChild( botStartMenu() );
            }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );

        }

    }

    function initBotCommands() {
        const $botCommandsButton = document.querySelector( '.mdp-helper-bot-commands' );

        // Return if there is no bot commands button
        if ( !$botCommandsButton ) { return; }

        $botCommandsButton.addEventListener( 'click', () => {
            (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
            createInfoCommandsMessage();
        } );
    }

    async function openPopupClickHandler( $chatBotBox ) {
        (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
        $chatBotBox.classList.toggle( 'mdp-helper-hide-chat' );


        if ( menuCounter === 0 ) {
            renderLastPostedMessage();
            await initStartMenu();
            botMenuActions();
            sendMessage();
            menuCounter++;

        }

        (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.buttonSquareRatio)();
    }

    /** Open Chat Bot by pressing button */
    async function openChatBot() {

        const $chatBotBox = document.querySelector( '.mdp-helper-chatbot-box' );

        initBotCommands();

        if ( mdpHelper.openChatWithButton !== 'on' && mdpHelper.openPopupWithoutButton === 'on' ) {
            renderLastPostedMessage();
            await initStartMenu();
            botMenuActions();
            sendMessage();
            menuCounter++;
        } else {
            const $openButton = document.querySelector( '#mdp-helper-open-button' );
            const $closeButton = document.querySelector( '.mdp-helper-chatbot-close-button' );

            // Add close button event
            if ( $closeButton ) {
                $closeButton.addEventListener( 'click', () => {
                    (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
                    $chatBotBox.classList.add( 'mdp-helper-hide-chat' );
                    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disablePageScrollOnFullSize)( fullSizeOnMobile );
                } );
            }

            // Close on click outside chatbot
            document.addEventListener( 'click', e => {
                if ( !e.target.closest( '.mdp-helper-chatbot-box, .mdp-bot-menu, .mdp-bot-menu-button' ) && !e.target.closest( '#mdp-helper-open-button' ) ) {
                    (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
                    $chatBotBox.classList.add( 'mdp-helper-hide-chat' );
                    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disablePageScrollOnFullSize)( fullSizeOnMobile );
                }
            } );

            // Add open button event
            if ( $openButton ) {

                $openButton.addEventListener( 'click', async () => {
                    await openPopupClickHandler( $chatBotBox );
                    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disablePageScrollOnFullSize)( fullSizeOnMobile );
                } );
            }

            // Add trigger element event
            window.addEventListener( 'click', async ( event ) => {
                if ( event.target.classList.contains( 'mdp-helper-trigger' ) ) {
                    event.preventDefault();
                    await openPopupClickHandler( $chatBotBox );
                    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disablePageScrollOnFullSize)( fullSizeOnMobile );
                }
            });

            // Add auto open popup
            if ( autoOpenPopup ) {
                setTimeout( async () => {
                    await openPopupClickHandler( $chatBotBox );
                }, autoOpenPopupDelay * 1000 )
            }
        }

        // Always show send form is AI is enabled
        (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.manageSendForm)( botFeatures.includes( 'ai' ) );

    }

    /** Show preloader on message loading */
    function messageOnloadPreloaderEvents( xhr ) {
        if ( messageOnloadPreloader && !enabledBotRespondDelay ) {
            xhr.onloadstart = () => {
                setTimeout( () => {
                    renderMessagePreloader();
                }, 0 );
            };
            xhr.onloadend = () => { removeMessagePreloader(); }
        }
    }

    /** Sends user message and returns bot response */
    function getBotResponse( messageText ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();

        formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
        formData.append( 'action', 'mdp_helper_bot_response' );
        formData.append( 'visitor_message', messageText );
        formData.append( 'mdp_helper_hash', localStorage.getItem( 'mdpHelperHash' ) );
        formData.append( 'mdp_helper_session_id', localStorage.getItem( 'mdpHelperBotSessionId' ) );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'POST', mdpHelper.endpoint, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Get all faq questions */
    function getFaqQuestions( page ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const url = new URL( mdpHelper.endpoint );
        url.searchParams.set( 'action', 'mdp_helper_get_faq_questions' );
        url.searchParams.set( 'page', page );
        url.searchParams.set( 'mdp_helper_nonce', mdpHelper.nonce  );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'GET', url, true );
            xhr.onload = () => resolve( xhr.responseText );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Get faq questions by category */
    function getFaqQuestionsByCat( category, page ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const url = new URL( mdpHelper.endpoint );
        url.searchParams.set( 'action', 'mdp_helper_get_faq_questions_by_cat' );
        url.searchParams.set( 'mdp_helper_nonce', mdpHelper.nonce  );
        url.searchParams.set( 'category', category );
        url.searchParams.set( 'page', page );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'GET', url, true );
            xhr.onload = () => resolve( xhr.responseText );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Get answer on faq question */
    function getFaqQuestionAnswer( questionIndex ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const url = new URL( mdpHelper.endpoint );
        url.searchParams.set( 'action', 'mdp_helper_get_faq_response' );
        url.searchParams.set( 'mdp_helper_nonce', mdpHelper.nonce  );
        url.searchParams.set( 'mdp_helper_question_index', questionIndex  );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'GET', url, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Get bot message */
    function getBotDialogMessage( type, menu, conditions = false ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const url = new URL( mdpHelper.endpoint );
        url.searchParams.set( 'action', 'mdp_helper_get_dialog_message' );
        url.searchParams.set( 'mdp_helper_nonce', mdpHelper.nonce  );
        url.searchParams.set( 'mdp_helper_messages_type', type );
        url.searchParams.set( 'mdp_helper_message_menu', menu );

        if ( conditions ) {
            url.searchParams.set( 'mdp_helper_with_conditions', 'included' );
            url.searchParams.set( 'mdp_post_id', mdpHelper.currentPost );
        }

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'GET', url, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Get collect data menu messages */
    function getBotCollectDataMessages() {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();
        const url = new URL( mdpHelper.endpoint );
        url.searchParams.set( 'mdp_helper_nonce', mdpHelper.nonce  );
        url.searchParams.set( 'action', 'mdp_helper_get_collect_data_messages' );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'GET', url, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Collect user data  */
    function collectUserData( userData ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();

        formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
        formData.append( 'action', 'mdp_helper_collect_user_data' );
        formData.append( 'mdp_collected_data', JSON.stringify( userData ) );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'POST', mdpHelper.endpoint, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Send user email message */
    function sendUserEmail( userMessage ) {
        const xhr = new XMLHttpRequest();
        const formData = new FormData();

        formData.append( 'mdp_helper_nonce', mdpHelper.nonce );
        formData.append( 'action', 'mdp_helper_send_user_email' );
        formData.append( 'mdp_user_email', JSON.stringify( userMessage ) );

        return new Promise( ( resolve, reject ) => {
            xhr.open( 'POST', mdpHelper.endpoint, true );
            xhr.onload = () => resolve( xhr.responseText );
            messageOnloadPreloaderEvents( xhr );
            xhr.onerror = () => reject( xhr.statusText );
            xhr.send( formData );
        } );
    }

    /** Renders message preloader */
    function renderMessagePreloader() {
        const messageContainer = document.createElement( 'div' );
        messageContainer.classList.add( 'mdp-helper-bot-message-container', 'mdp-helper-message-container', 'mdp-helper-message-preloader' );
        const avatar = (0,_modules_avatar__WEBPACK_IMPORTED_MODULE_0__.createAvatar)( false );
        const message = document.createElement( 'div' );
        message.classList.add( 'mdp-helper-bot-message', 'mdp-helper-message', 'mdp-helper-message-preloader' );

        message.innerHTML = '<span></span><span></span><span></span>'

        if ( avatar ) {
            messageContainer.appendChild( avatar );
        }
        messageContainer.appendChild( message );

        $chatBox.appendChild( messageContainer );
    }



    function messageHasMedia( messageText ) {
        const container = document.createElement( 'div' );
        container.innerHTML = messageText;
        const $video = container.querySelector( 'video' );
        const $iframe = container.querySelector( 'iframe' );
        const $audio = container.querySelector( 'audio' );

        return !!($video || $iframe || $audio);



    }
    /** Append message to chat box */
    function appendMessage( messageContainer, message, avatar, messageText, messageTypeClass, renderWithoutTimeout, isLinkWidget = false, linkWidgetData = {} ) {

        if ( mdpHelper.botTypingAnimation === 'on' &&
            ( messageTypeClass === 'bot' && !renderWithoutTimeout ) &&
            ( messageTypeClass === 'bot' && !messageHasMedia( messageText ) )
        ) {
            stopWritingAnimation = false;
            typeWriterAnimation( messageText, message );
        } else {
            if ( messageTypeClass === 'visitor' ) {
                message.innerText = messageText;
            } else {
                message.innerHTML = messageText;
            }

        }

        // Date and time
        (0,_modules_message_upper_line__WEBPACK_IMPORTED_MODULE_6__.messageUpperLine)( messageTypeClass !== 'visitor', $chatBox, returnToMainMenuAction );

        // Avatar
        if ( avatar ) {
            messageContainer.appendChild( avatar );
        }

        // Message content
        isLinkWidget ?
            messageContainer.appendChild( (0,_modules_message_link__WEBPACK_IMPORTED_MODULE_8__.renderLinkWidget)( linkWidgetData, messageText ) ) :
            messageContainer.appendChild( message );

        $chatBox.appendChild( messageContainer );

    }

    /** Renders visitor message */
    async function renderMessage( isVisitor, messageText, renderWithoutTimeout = false, isLinkWidget = false, linkWidgetData = {} ) {

        const messageTypeClass = isVisitor ? 'visitor' : 'bot';
        const isMessagePreloaderOn = mdpHelper.botMessagePreloader === 'on';

        const messageContainer = document.createElement( 'div' );
        messageContainer.classList.add(
            `mdp-helper-${messageTypeClass}-message-container`,
            'mdp-helper-message-container'
        );

        const avatar = (0,_modules_avatar__WEBPACK_IMPORTED_MODULE_0__.createAvatar)( isVisitor );

        const message = document.createElement( 'div' );
        message.classList.add( `mdp-helper-${messageTypeClass}-message`, 'mdp-helper-message' );

        if ( messageText ) {

            if ( !isLinkWidget ) {
                currentMessageLength = messageText.length;
            }

            /** render preloader if delay is turned on */
            if ( ( !renderWithoutTimeout || !isVisitor ) && isMessagePreloaderOn && respondDelay > 0 ) {
                setTimeout( () => {
                    renderMessagePreloader();
                }, 0 );
            }

            setTimeout( async () => {

                const $messagePreloader = document.querySelector( '.mdp-helper-message-preloader' );

                appendMessage( messageContainer, message, avatar, messageText, messageTypeClass, renderWithoutTimeout, isLinkWidget, linkWidgetData );

                /** Remove message preloader */
                if ( ( !renderWithoutTimeout || !isVisitor ) && isMessagePreloaderOn && respondDelay > 0 ) {
                    $chatBox.removeChild( $messagePreloader );
                }

                /** Play message sound */
                isVisitor ? await playMessageSound( sendMessageAudio ) : await playMessageSound( receiveMessageAudio );

                /** TTS */
                if ( tts.enabled && !isVisitor ) {

                    // Remove all <a></a> tags inside wp-video-shortcode to prevent reading them
                    const regex = /<video[^>]*\sclass="wp-video-shortcode"[^>]*>[\s\S]*?<\/video>/g;

                    const messageToSpeak = isLinkWidget ?
                        `${ translations.learnMoreText } ${ linkWidgetData.post_title }` :
                        messageText
                            .replace( regex, ( match ) => {
                                return match.replace( /<a[^>]*>.*?<\/a>/g, '' );
                            } )
                            .replace( /<\/?[^>]+(>|$)/g, "" );
                    const eventHelperSpeak = new CustomEvent(
                        'helper-speak',
                        {
                            detail: {
                                message: messageToSpeak
                            }
                        }
                    );
                    window.dispatchEvent( eventHelperSpeak );
                }

                /** Manage response form */
                if ( ! isVisitor && ! botFeatures.includes( 'ai' ) ) {

                    (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.manageSendForm)( isSendingEmail || isDataCollecting || notConfirmedAcceptanceMenu || isBotCommandWaiting );

                }

                /** Dispatch render message event */
                const eventHelperMessageRendered = new CustomEvent(
                    'helper-message-rendered',
                    {
                        detail: {
                            element: messageContainer,
                            message: messageText,
                            isBot: !isVisitor,
                            isBotCommands: isBotCommandWaiting,
                            isDataCollecting: isDataCollecting,
                            notConfirmedAcceptanceMenu: notConfirmedAcceptanceMenu,
                            isSendingEmail: isSendingEmail,
                        }
                    }
                );
                window.dispatchEvent( eventHelperMessageRendered );

                // Update bot logs
                await (0,_modules_logs__WEBPACK_IMPORTED_MODULE_10__.updateLogs)( isVisitor, messageText );

                if ( isVisitor && !logsInited ) {

                    /** Set bot logs */
                    await (0,_modules_logs__WEBPACK_IMPORTED_MODULE_10__.initLogs)( generateHash( 10 ) );
                    logsInited = true;

                }

            }, isVisitor || renderWithoutTimeout ? 0 : respondDelay );

        }
    }

    /** Removes previous rendered menu */
    function removePreviousMenu() {
        const $previousQuestionsMenu = document.querySelectorAll( '.mdp-bot-menu' );

        if ( !$previousQuestionsMenu.length ) { return; }

        $chatBox.removeChild( $previousQuestionsMenu[$previousQuestionsMenu.length - 1] );
        (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
    }

    /** Creates main FAQ menu */
    async function createFaqQuestionsMenu( isMenu = false, page = 1 ) {
        faqCategory = '';
        const questions = await getFaqQuestions( page );
        const questionsData = JSON.parse( questions );
        const questionsWithoutCat = questionsData.data.questions_data.questions;
        const categories = questionsData.data.categories;

        /** Render FAQ questions */
        renderFaqQuestions( questionsWithoutCat, categories, isMenu, false, +questionsData.data.questions_data.pages_count, page );

        await answerActions( isMenu );

    }

    /** Creates button that returns to main FAQ menu from category */
    function createReturnToMainFaqBtn( questionsMenu ) {
        const returnButton = document.createElement( 'div' );
        returnButton.classList.add( 'mdp-bot-menu-button', 'mdp-bot-return-to-faq-questions' );
        returnButton.innerText = mdpHelper.translations.returnFaqButton;


        returnButton.addEventListener( 'click', async () => {
            removePreviousMenu();

            questionsMenu.removeChild( returnButton );
            await createFaqQuestionsMenu( true );
        } );

        questionsMenu.appendChild( returnButton );
    }

    /** Creates next and previous pagination buttons */
    function createNavPaginationButton( buttonsWrapper, type, currentPage, pages, isCategories ) {

        const navButton = document.createElement( 'button' );
        const paginationText = mdpHelper.translations.pagination;
        navButton.classList.add( 'mdp-faq-pagination-button', `mdp-faq-pagination-${type}-nav-button` );
        navButton.innerText = type === 'next' ? paginationText.next : paginationText.prev;

        navButton.addEventListener( 'click', async () => {
            if ( ( currentPage < pages && type === 'next' ) || ( currentPage > 1 && type === 'prev' ) ) {
                removePreviousMenu();

                if ( type === 'next' ) {
                    if ( !isCategories ) {
                        await createFaqQuestionsMenu( true, currentPage + 1 );
                    } else {
                        await createFaqCategoryMenu( faqCategory, currentPage + 1 );
                    }
                } else {
                    if ( !isCategories ) {
                        await createFaqQuestionsMenu( true, currentPage - 1 );
                    } else {
                        await createFaqCategoryMenu( faqCategory, currentPage - 1 );
                    }
                }

            }

        } );

        buttonsWrapper.appendChild( navButton );

    }

    /** Creates pagination for FAQ questions */
    function createPagination( questionsMenu, pages, isCategories, currentPage = 1 ) {
        const button = document.createElement( 'div' );
        button.classList.add( 'mdp-bot-menu-button', 'mdp-faq-pagination' );

        /** Create next pagination button */
        createNavPaginationButton( button, 'prev', currentPage, pages, isCategories );

        const pagesContainer = document.createElement( 'div' );
        pagesContainer.classList.add( 'mdp-faq-pagination-pages-container' );

        for ( let i = 1; i <= pages; i++ ) {
           const paginationBtn = document.createElement( 'button' );
           paginationBtn.classList.add( 'mdp-faq-pagination-button' );
           paginationBtn.innerText = i;

            if ( i === currentPage ) {
                paginationBtn.classList.add( 'mdp-faq-pagination-active' );
            }

           paginationBtn.addEventListener( 'click', async () => {
               removePreviousMenu();

               if ( !isCategories ) {
                await createFaqQuestionsMenu( true, i );
               } else {
                 await createFaqCategoryMenu( faqCategory, i );
               }

           } );
            pagesContainer.appendChild( paginationBtn );
        }

        button.appendChild( pagesContainer );

        createNavPaginationButton( button, 'next', currentPage, pages, isCategories );


        questionsMenu.appendChild( button );

    }

    /** Creates FAQ category menu */
    async function createFaqCategoryMenu( category, page = 1 ) {
        const questions = await getFaqQuestionsByCat( category, page );
        const questionsData = JSON.parse( questions );

        renderFaqQuestions( questionsData.data.questions, [], true, true, +questionsData.data.pages_count, page );

        await answerActions( true );
    }

    /** Renders all FAQ categories */
    function renderFaqCategories( categories, questionsMenu ) {
        categories.forEach( category => {
            const categoryButton = document.createElement( 'div' );
            categoryButton.innerHTML = `
                    <span>${category}</span>
                    ${faqCategoryIcon ? `<div class="mdp-bot-menu-icon">${faqCategoryIcon}</div>` : ''}
            `;
            categoryButton.classList.add( 'mdp-bot-menu-button', 'mdp-category-button' );
            categoryButton.setAttribute( 'data-category', category );
            questionsMenu.appendChild( categoryButton );
        } );
    }

    /** Renders FAQ menu questions */
     function renderFaqQuestions( questions, categories = [], isMenu = false, isCategories = false, pages = 0, currentPage = 1 ) {
        setTimeout(  () => {
            const questionsMenu = document.createElement( 'div' );
            questionsMenu.classList.add( 'mdp-bot-menu' );
            renderFaqCategories( categories, questionsMenu );
            questions.forEach( question => {
                const questionButton = document.createElement( 'button' );
                questionButton.innerHTML = question.question;
                questionButton.classList.add( 'mdp-bot-menu-button', 'mdp-question-button' );
                questionButton.setAttribute( 'data-response-index', question.index );
                questionsMenu.appendChild( questionButton );
            } );
            $chatBox.appendChild( questionsMenu );
            if ( mdpHelper.pagination === 'on' && pages > 1 ) {
                createPagination( questionsMenu, pages, isCategories, currentPage );
            }
            if ( isCategories ) { createReturnToMainFaqBtn( questionsMenu ); }
            backToMainMenuBtn( questionsMenu );
        }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) * ( isMenu ? 0 : 1 ) )
    }

    /** Renders answers on FAQ questions */
    async function answerActions( isMenu = false ) {
        setTimeout( () => {
            const $questionsButtons = document.querySelectorAll( `.mdp-question-button` );
            const $categoriesButtons = document.querySelectorAll( '.mdp-category-button' );
            $questionsButtons.forEach( $questionsButton => {
                $questionsButton.addEventListener( 'click', async event => {
                    removePreviousMenu();

                    renderMessage( true, event.target.innerText.trim() );
                    const questionAnswer = await getFaqQuestionAnswer( event.target.dataset.responseIndex );
                    const questionAnswerData = JSON.parse( questionAnswer );
                    if ( questionAnswerData.data.link_widget_data ) {
                        renderMessage( false, questionAnswerData.data.answer, false, true, questionAnswerData.data.link_widget_data );

                        /** Set answer JSON to store in database */
                        const answerJson = JSON.stringify( {
                            message: questionAnswerData.data.answer,
                            link_widget_data: questionAnswerData.data.link_widget_data
                        } );

                        /** Save to session storage last question and answer to it */
                        (0,_modules_local_storage__WEBPACK_IMPORTED_MODULE_7__.setLocalSessionStorage)( questionAnswerData.data.question, answerJson );
                    } else {
                        renderMessage( false, questionAnswerData.data.answer );

                        /** Set answer JSON to store in database */
                        const answerJson = JSON.stringify( { message: questionAnswerData.data.answer } );

                        /** Save to session storage last question and answer to it */
                        (0,_modules_local_storage__WEBPACK_IMPORTED_MODULE_7__.setLocalSessionStorage)( questionAnswerData.data.question, answerJson );
                    }

                    /** Reset message length if message has media */
                    if ( messageHasMedia( questionAnswerData.data.answer ) ) { currentMessageLength = 0; }

                    faqTimeout = setTimeout(async () => {
                        if ( enableMoreHelpMessage ) {
                            await createMoreHelpMenu();
                        } else {
                            await createFaqMenu();
                        }
                    }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) + 1000 );
                } );
            } );
            $categoriesButtons.forEach( $categoryButton => {
                $categoryButton.addEventListener( 'click', async event => {
                    removePreviousMenu();

                    faqCategory = event.target.dataset.category || event.target.closest( '.mdp-category-button' ).dataset.category;
                    await createFaqCategoryMenu( faqCategory );
                } );
            } );
        }, ( (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) + 500 ) * ( isMenu ? 0 : 1 ) );
    }

    /** Back to main menu button logic */
    function backToMainMenuBtn( $box, initialMessage = true ) {
        const returnButton = (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'backToMenu', mdpHelper.translations.backToStartMenuButton );
        $box.appendChild( returnButton );

        returnButton.addEventListener( 'click', async () => {

            // Remove previous menu
            removePreviousMenu();

            userDataMessages = {};

            await initStartMenu( initialMessage );
            botMenuActions();
        } );
    }

    async function createFaqMenu() {
        const botMessage = await getBotDialogMessage( '', 'faq' );
        const botMessageData = JSON.parse( botMessage );
        renderMessage( false, botMessageData.data );
        await createFaqQuestionsMenu();
    }

    async function initAcceptanceCheckBox() {
        const collectDataMessage = await getBotCollectDataMessages();
        botCollectDataMessages = JSON.parse( collectDataMessage ).data;

        // Exit if there is no acceptance checkbox
        if ( !acceptanceCheckBox ) { return; }

        // Acceptance checkbox listener
        acceptanceCheckBox.addEventListener( 'change', () => {
            userAcceptedLocalstorage = localStorage.getItem( 'mdpAcceptedCollectData' );
            if ( acceptanceCheckBox.checked ) {
                acceptanceWrapper.style.display = 'none';

                if ( acceptanceDisplaying === 'localstorage' && !userAcceptedLocalstorage ) {
                    localStorage.setItem( 'mdpAcceptedCollectData', 'confirmed' );
                    userAcceptedLocalstorage = localStorage.getItem( 'mdpAcceptedCollectData' );
                } else if ( acceptanceDisplaying === 'session' ) {
                    acceptanceConfirmed = true;
                }

                isDataCollecting = true;
                notConfirmedAcceptanceMenu = false;
                renderMessage( false, Object.values( botCollectDataMessages )[0].message );
                acceptanceCheckBox.checked = false;
            }
        } );

    }

    await initAcceptanceCheckBox();

    async function createCollectDataMenu() {
        const collectDataMessage = await getBotCollectDataMessages();
        botCollectDataMessages = JSON.parse( collectDataMessage ).data;
        userAcceptedLocalstorage = localStorage.getItem( 'mdpAcceptedCollectData' );

        if ( acceptanceDisplaying === 'localstorage' && userAcceptedLocalstorage === 'confirmed' ||
            acceptanceDisplaying === 'session' && acceptanceConfirmed ||
            acceptanceDisplaying === 'none' ) {
            notConfirmedAcceptanceMenu = false;
            isDataCollecting = true;
            // render first message
            renderMessage( false, Object.values( botCollectDataMessages )[0].message );
        } else {
            acceptanceWrapper.style.display = 'flex';
            notConfirmedAcceptanceMenu = true;
            renderMessage( false, mdpHelper.confirmationAcceptanceText );
        }


    }

    async function createMoreHelpMenu() {
       const moreHelpMenu = document.createElement( 'div' );
       const initialMessageJson = await getBotDialogMessage( '', 'general_more_help_initial' );
       const initialMessage = JSON.parse( initialMessageJson ).data;
       await renderMessage( false, initialMessage );

       moreHelpMenu.classList.add( 'mdp-bot-menu' );

        setTimeout( () => {
            removePreviousMenu();
            moreHelpMenu.appendChild( (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'moreHelp', mdpHelper.moreHelpConfirmButtonText ) );
            moreHelpMenu.appendChild( (0,_modules_bot_menu__WEBPACK_IMPORTED_MODULE_1__.createBotMenuButton)( 'noMoreHelp', mdpHelper.moreHelpDeclineButtonText ) );
            $chatBox.appendChild( moreHelpMenu );
        }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );

        botMenuActions();

    }

    async function createEndConversationMenu() {
        const endConversationMessageJson = await getBotDialogMessage( '', 'general_exit' );
        const endConversationMessage = JSON.parse( endConversationMessageJson ).data;
        const endConversationMenu = document.createElement( 'div' );
        endConversationMenu.classList.add( 'mdp-bot-menu' );

        backToMainMenuBtn( endConversationMenu, false );
        await renderMessage( false, endConversationMessage );
        setTimeout( () => {
            removePreviousMenu();
            $chatBox.appendChild( endConversationMenu );
        }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );

    }

    async function createTryAgainConversation() {
        const tryAgainMessageJson = await getBotDialogMessage( '', 'general_try_again' );
        const tryAgainMessage = JSON.parse( tryAgainMessageJson ).data;
        const tryAgainMessageMenu = document.createElement( 'div' );
        tryAgainMessageMenu.classList.add( 'mdp-bot-menu' );

        await renderMessage( false, tryAgainMessage );
        setTimeout( () => {
            removePreviousMenu();
        }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );

        await initStartMenu( false );
        botMenuActions();
    }

    async function createSendEmailMenu() {
        isSendingEmail = true;
        const botUserEmailAddressMessageJson = await getBotDialogMessage( '', 'get_emails_ask_user_email' );
        const botUserEmailAddressMessage = JSON.parse( botUserEmailAddressMessageJson ).data;

        renderMessage( false, botUserEmailAddressMessage );
    }

    async function getAiBotResponse( visitorMessage ) {
        const aiMessageResponse = await getBotResponse( visitorMessage );
        const responseData = JSON.parse( aiMessageResponse ).data;

        if ( responseData.link_widget_data ) {
            renderMessage( false, responseData.message, false, true, responseData.link_widget_data );

            /** Set last question and answer to local storage and session storage */
            (0,_modules_local_storage__WEBPACK_IMPORTED_MODULE_7__.setLocalSessionStorage)(
                visitorMessage,
                JSON.stringify( { message: responseData.message, link_widget_data: responseData.link_widget_data } )
            );

            if ( enableMoreHelpMessage ) {
                setTimeout( async () => {
                    await createMoreHelpMenu();
                }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );
            }
        } else {
            renderMessage( false, responseData.message );

            /** Set last question and answer to local storage and session storage */
            (0,_modules_local_storage__WEBPACK_IMPORTED_MODULE_7__.setLocalSessionStorage)( visitorMessage, JSON.stringify({ message: responseData.message } ) );

            if ( enableMoreHelpMessage ) {
                setTimeout( async () => {
                    await createMoreHelpMenu();
                }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) );
            }
        }

    }

    /** Validates user input in collect data messages */
    function validateUserInput( userInput, messageObj ) {
        switch ( messageObj.validation ) {
            case 'email':
                if ( (0,_modules_form_validator__WEBPACK_IMPORTED_MODULE_9__.validateEmail)( userInput ) ) {
                    isValidated = true;
                    messagesCounter++;
                } else {
                    isValidated = false;
                    renderMessage( false, messageObj.validation_error );
                }
                break;
            case 'number':
                if ( (0,_modules_form_validator__WEBPACK_IMPORTED_MODULE_9__.numberValidation)( userInput, messageObj.max_number, messageObj.min_number ) ) {
                    isValidated = true;
                    messagesCounter++;
                } else {
                    isValidated = false;
                    renderMessage( false, messageObj.validation_error );
                }
                break;
            case 'custom':
                if ( (0,_modules_form_validator__WEBPACK_IMPORTED_MODULE_9__.customValidation)( userInput, messageObj.validation_custom_regex ) ) {
                    isValidated = true;
                    messagesCounter++;
                } else {
                    isValidated = false;
                    renderMessage( false, messageObj.validation_error );
                }
        }
    }

    /** Return to main menu button signature action */
    async function returnToMainMenuAction() {

        // Do not render FAQ menu after return
        clearTimeout( faqTimeout );

        isSendingEmail = false;
        isDataCollecting = false;
        notConfirmedAcceptanceMenu = false;
        stopWritingAnimation = true;
        messagesCounter = 0;

        // Remove previous menu
        removePreviousMenu();

        userDataMessages = {};

        await initStartMenu();

        botMenuActions();

    }

    function removeMessagePreloader() {
        const $messagePreloader = document.querySelector( '.mdp-helper-message-preloader' );
        $chatBox.removeChild( $messagePreloader );
    }

    function generateHash(length) {
        let result = '';
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        const charactersLength = characters.length;
        let counter = 0;
        while ( counter < length ) {
            result += characters.charAt( Math.floor( Math.random() * charactersLength ) );
            counter += 1;
        }
        return result;
    }

    /** Send email button action */
    async function sendMessageHandler() {

        const $messageInput = document.querySelector( 'input.mdp-helper-input-messages-field' );

        /** Play send message audio */
        await playMessageSound( sendMessageAudio );

        const messageText = $messageInput.value;
        $messageInput.value = '';
        if ( messageText === '' ) { return; }

        /** Bot commands */
        if ( messageText === '/return' ) {
            isBotCommandWaiting = false;
            await initStartMenu();
            botMenuActions();
            return;
        }

        if ( messageText === '/info' ) {
            isBotCommandWaiting = false;
            (0,_modules_webspeech__WEBPACK_IMPORTED_MODULE_3__.stopSpeechSynthesis)();
            createInfoCommandsMessage();
        }

        renderMessage( true, messageText );

        /** Get messages from ai bot */
        const { botFeatures } = window.mdpHelper;
        if ( ! isSendingEmail && ! isDataCollecting && !notConfirmedAcceptanceMenu && botFeatures.includes( 'ai' ) ) {

            // Limit user requests
            if ( limitUsersRequests && !localStorage.getItem( 'mdpHelperHash' ) ) {
                localStorage.setItem( 'mdpHelperHash', generateHash( 20 ) );
            }

            if ( botPersonalityType === 'assistant' ) {
                await (0,_modules_assistants__WEBPACK_IMPORTED_MODULE_11__.checkAssistantThread)( generateHash( 10 ) );
            }

            await getAiBotResponse( messageText );
        }

        if ( isDataCollecting ) {
            const botMessages = Object.values( botCollectDataMessages );
            const messagesFieldsNames = Object.keys( botCollectDataMessages );

            // update user data object
            userDataMessages[messagesFieldsNames[messagesCounter]] = messageText;

            // increase message counter only if there is no validation
            if ( botMessages[messagesCounter].validation === 'none' ) {
                messagesCounter++
            } else {
                validateUserInput( messageText, botMessages[messagesCounter], botMessages );
            }

            if ( messagesCounter > messagesFieldsNames.length - 1 ) {
                const botResponse = await collectUserData( userDataMessages );

                // Send Google Analytics event
                if ( enabledGoogleAnalytics && typeof gtag !== "undefined" ) { gtag( "event", "generate_lead",  {
                    'event_label': 'Helper collect data',
                } ) }

                renderMessage( false, JSON.parse( botResponse ).data );

                // reset collect data variables
                isValidated = true;
                messagesCounter = 0;
                botCollectDataMessages = {};
                isDataCollecting = false;
                userDataMessages = {};

                // return to start menu and bind events
                setTimeout( async () => {
                    if ( enableMoreHelpMessage ) {
                        await createMoreHelpMenu();
                    } else {
                        await initStartMenu();
                        await botMenuActions();
                    }
                }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) + 1000 );
            } else {
                if ( isValidated ) { renderMessage( false, botMessages[messagesCounter].message ); }
            }
        }

        if ( notConfirmedAcceptanceMenu ) {
            renderMessage( false, mdpHelper.confirmationAcceptanceText );
        }

        if ( isSendingEmail ) {

            if ( messagesCounter === 0 ) {
                if ( (0,_modules_form_validator__WEBPACK_IMPORTED_MODULE_9__.validateEmail)( messageText ) ) {
                    userDataMessages['email'] = messageText;
                    messagesCounter++;
                    const botUserMessageRequestJson = await getBotDialogMessage( '', 'get_emails_ask_user_message' );
                    const botUserMessageRequest = JSON.parse( botUserMessageRequestJson ).data;
                    renderMessage( false, botUserMessageRequest );
                } else {
                    renderMessage( false, mdpHelper.translations.incorrectEmailMessage );
                }
            }

            if ( messagesCounter > 0 ) {
                userDataMessages['message'] = messageText;
                messagesCounter++;
            }

            if ( messagesCounter > 2 ) {
                const botResponse = await sendUserEmail( userDataMessages );
                renderMessage( false, JSON.parse( botResponse ).data )

                userDataMessages = {};
                isSendingEmail = false;
                messagesCounter = 0;

                // return to start menu and bind events
                setTimeout( async () => {
                    if ( enableMoreHelpMessage ) {
                        await createMoreHelpMenu();
                    } else {
                        await initStartMenu();
                        await botMenuActions();
                    }
                }, (0,_modules_utilities__WEBPACK_IMPORTED_MODULE_2__.getMessageDelay)( currentMessageLength ) + 1000 );
            }
        }

    }

    /** Send messages to Chat Bot */
    function sendMessage() {

        const { stt } = window.mdpHelper;

        const $sendMessageForm = document.querySelector( '.mdp-helper-send-form' );

        $sendMessageForm.addEventListener( 'submit', ( e ) => {
            e.preventDefault();
            sendMessageHandler().then();
        } );

        // Send message on recognition
        if ( stt.enabled && stt.autoSubmit ) {
            window.addEventListener('helper-recognition', sendMessageHandler);
        }

    }

    openChatBot();

    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.disablePageScrollOnFullSize)( fullSizeOnMobile );

    (0,_modules_ux__WEBPACK_IMPORTED_MODULE_5__.setMaxHeightForPopup)( maxPopupHeight );

}

/** Document Ready. */
document.readyState === 'loading' ?
    document.addEventListener( 'DOMContentLoaded', mdpHelperBot ) :
    mdpHelperBot;


})();

/******/ })()
;