/*!
    jQuery Masked Input Plugin
    Copyright (c) 2007 - 2015 Josh Bush (digitalbush.com)
    Licensed under the MIT license (http://digitalbush.com/projects/masked-input-plugin/#license)
    Version: 1.4.1
*/
!function(factory) {
    "function" == typeof define && define.amd ? define([ "jquery" ], factory) : factory("object" == typeof exports ? require("jquery") : jQuery);
}(function($) {
    var caretTimeoutId, ua = navigator.userAgent, iPhone = /iphone/i.test(ua), chrome = /chrome/i.test(ua), android = /android/i.test(ua);
    $.mask = {
        definitions: {
            "9": "[0-9]",
            a: "[A-Za-z]",
            "*": "[A-Za-z0-9]"
        },
        autoclear: !0,
        dataName: "rawMaskFn",
        placeholder: "_"
    }, $.fn.extend({
        caret: function(begin, end) {
            var range;
            if (0 !== this.length && !this.is(":hidden")) return "number" == typeof begin ? (end = "number" == typeof end ? end : begin, 
            this.each(function() {
                this.setSelectionRange ? this.setSelectionRange(begin, end) : this.createTextRange && (range = this.createTextRange(), 
                range.collapse(!0), range.moveEnd("character", end), range.moveStart("character", begin), 
                range.select());
            })) : (this[0].setSelectionRange ? (begin = this[0].selectionStart, end = this[0].selectionEnd) : document.selection && document.selection.createRange && (range = document.selection.createRange(), 
            begin = 0 - range.duplicate().moveStart("character", -1e5), end = begin + range.text.length), 
            {
                begin: begin,
                end: end
            });
        },
        unmask: function() {
            return this.trigger("unmask");
        },
        mask: function(mask, settings) {
            var input, defs, tests, partialPosition, firstNonMaskPos, lastRequiredNonMaskPos, len, oldVal;
            if (!mask && this.length > 0) {
                input = $(this[0]);
                var fn = input.data($.mask.dataName);
                return fn ? fn() : void 0;
            }
            return settings = $.extend({
                autoclear: $.mask.autoclear,
                placeholder: $.mask.placeholder,
                completed: null
            }, settings), defs = $.mask.definitions, tests = [], partialPosition = len = mask.length, 
            firstNonMaskPos = null, $.each(mask.split(""), function(i, c) {
                "?" == c ? (len--, partialPosition = i) : defs[c] ? (tests.push(new RegExp(defs[c])), 
                null === firstNonMaskPos && (firstNonMaskPos = tests.length - 1), partialPosition > i && (lastRequiredNonMaskPos = tests.length - 1)) : tests.push(null);
            }), this.trigger("unmask").each(function() {
                function tryFireCompleted() {
                    if (settings.completed) {
                        for (var i = firstNonMaskPos; lastRequiredNonMaskPos >= i; i++) if (tests[i] && buffer[i] === getPlaceholder(i)) return;
                        settings.completed.call(input);
                    }
                }
                function getPlaceholder(i) {
                    return settings.placeholder.charAt(i < settings.placeholder.length ? i : 0);
                }
                function seekNext(pos) {
                    for (;++pos < len && !tests[pos]; ) ;
                    return pos;
                }
                function seekPrev(pos) {
                    for (;--pos >= 0 && !tests[pos]; ) ;
                    return pos;
                }
                function shiftL(begin, end) {
                    var i, j;
                    if (!(0 > begin)) {
                        for (i = begin, j = seekNext(end); len > i; i++) if (tests[i]) {
                            if (!(len > j && tests[i].test(buffer[j]))) break;
                            buffer[i] = buffer[j], buffer[j] = getPlaceholder(j), j = seekNext(j);
                        }
                        writeBuffer(), input.caret(Math.max(firstNonMaskPos, begin));
                    }
                }
                function shiftR(pos) {
                    var i, c, j, t;
                    for (i = pos, c = getPlaceholder(pos); len > i; i++) if (tests[i]) {
                        if (j = seekNext(i), t = buffer[i], buffer[i] = c, !(len > j && tests[j].test(t))) break;
                        c = t;
                    }
                }
                function androidInputEvent() {
                    var curVal = input.val(), pos = input.caret();
                    if (oldVal && oldVal.length && oldVal.length > curVal.length) {
                        for (checkVal(!0); pos.begin > 0 && !tests[pos.begin - 1]; ) pos.begin--;
                        if (0 === pos.begin) for (;pos.begin < firstNonMaskPos && !tests[pos.begin]; ) pos.begin++;
                        input.caret(pos.begin, pos.begin);
                    } else {
                        for (checkVal(!0); pos.begin < len && !tests[pos.begin]; ) pos.begin++;
                        input.caret(pos.begin, pos.begin);
                    }
                    tryFireCompleted();
                }
                function blurEvent() {
                    checkVal(), input.val() != focusText && input.change();
                }
                function keydownEvent(e) {
                    if (!input.prop("readonly")) {
                        var pos, begin, end, k = e.which || e.keyCode;
                        oldVal = input.val(), 8 === k || 46 === k || iPhone && 127 === k ? (pos = input.caret(), 
                        begin = pos.begin, end = pos.end, end - begin === 0 && (begin = 46 !== k ? seekPrev(begin) : end = seekNext(begin - 1), 
                        end = 46 === k ? seekNext(end) : end), clearBuffer(begin, end), shiftL(begin, end - 1), 
                        e.preventDefault()) : 13 === k ? blurEvent.call(this, e) : 27 === k && (input.val(focusText), 
                        input.caret(0, checkVal()), e.preventDefault());
                    }
                }
                function keypressEvent(e) {
                    if (!input.prop("readonly")) {
                        var p, c, next, k = e.which || e.keyCode, pos = input.caret();
                        if (!(e.ctrlKey || e.altKey || e.metaKey || 32 > k) && k && 13 !== k) {
                            if (pos.end - pos.begin !== 0 && (clearBuffer(pos.begin, pos.end), shiftL(pos.begin, pos.end - 1)), 
                            p = seekNext(pos.begin - 1), len > p && (c = String.fromCharCode(k), tests[p].test(c))) {
                                if (shiftR(p), buffer[p] = c, writeBuffer(), next = seekNext(p), android) {
                                    var proxy = function() {
                                        $.proxy($.fn.caret, input, next)();
                                    };
                                    setTimeout(proxy, 0);
                                } else input.caret(next);
                                pos.begin <= lastRequiredNonMaskPos && tryFireCompleted();
                            }
                            e.preventDefault();
                        }
                    }
                }
                function clearBuffer(start, end) {
                    var i;
                    for (i = start; end > i && len > i; i++) tests[i] && (buffer[i] = getPlaceholder(i));
                }
                function writeBuffer() {
                    input.val(buffer.join(""));
                }
                function checkVal(allow) {
                    var i, c, pos, test = input.val(), lastMatch = -1;
                    for (i = 0, pos = 0; len > i; i++) if (tests[i]) {
                        for (buffer[i] = getPlaceholder(i); pos++ < test.length; ) if (c = test.charAt(pos - 1), 
                        tests[i].test(c)) {
                            buffer[i] = c, lastMatch = i;
                            break;
                        }
                        if (pos > test.length) {
                            clearBuffer(i + 1, len);
                            break;
                        }
                    } else buffer[i] === test.charAt(pos) && pos++, partialPosition > i && (lastMatch = i);
                    return allow ? writeBuffer() : partialPosition > lastMatch + 1 ? settings.autoclear || buffer.join("") === defaultBuffer ? (input.val() && input.val(""), 
                    clearBuffer(0, len)) : writeBuffer() : (writeBuffer(), input.val(input.val().substring(0, lastMatch + 1))), 
                    partialPosition ? i : firstNonMaskPos;
                }
                var input = $(this), buffer = $.map(mask.split(""), function(c, i) {
                    return "?" != c ? defs[c] ? getPlaceholder(i) : c : void 0;
                }), defaultBuffer = buffer.join(""), focusText = input.val();
                input.data($.mask.dataName, function() {
                    return $.map(buffer, function(c, i) {
                        return tests[i] && c != getPlaceholder(i) ? c : null;
                    }).join("");
                }), input.one("unmask", function() {
                    input.off(".mask").removeData($.mask.dataName);
                }).on("focus.mask", function() {
                    if (!input.prop("readonly")) {
                        clearTimeout(caretTimeoutId);
                        var pos;
                        focusText = input.val(), pos = checkVal(), caretTimeoutId = setTimeout(function() {
                            input.get(0) === document.activeElement && (writeBuffer(), pos == mask.replace("?", "").length ? input.caret(0, pos) : input.caret(pos));
                        }, 10);
                    }
                }).on("blur.mask", blurEvent).on("keydown.mask", keydownEvent).on("keypress.mask", keypressEvent).on("input.mask paste.mask", function() {
                    input.prop("readonly") || setTimeout(function() {
                        var pos = checkVal(!0);
                        input.caret(pos), tryFireCompleted();
                    }, 0);
                }), chrome && android && input.off("input.mask").on("input.mask", androidInputEvent), 
                checkVal();
            });
        }
    });
});
+function ( $ ) {
	'use strict';

	// CSS TRANSITION SUPPORT (Shoutout: http://www.modernizr.com/)
	// ============================================================

	function transitionEnd() {
		var el = document.createElement( 'bootstrap' )

		var transEndEventNames = {
			WebkitTransition: 'webkitTransitionEnd',
			MozTransition   : 'transitionend',
			OTransition     : 'oTransitionEnd otransitionend',
			transition      : 'transitionend'
		}

		for ( var name in transEndEventNames ) {
			if ( el.style[name] !== undefined ) {
				return {end: transEndEventNames[name]}
			}
		}

		return false // explicit for ie8 (  ._.)
	}

	// http://blog.alexmaccaw.com/css-transitions
	$.fn.emulateTransitionEnd = function ( duration ) {
		var called = false
		var $el = this
		$( this ).one( 'bsTransitionEnd', function () {
			called = true
		} )
		var callback = function () {
			if ( !called ) $( $el ).trigger( $.support.transition.end )
		}
		setTimeout( callback, duration )
		return this
	}

	$( function () {
		$.support.transition = transitionEnd()

		if ( !$.support.transition ) return

		$.event.special.bsTransitionEnd = {
			bindType    : $.support.transition.end,
			delegateType: $.support.transition.end,
			handle      : function ( e ) {
				if ( $( e.target ).is( this ) ) return e.handleObj.handler.apply( this, arguments )
			}
		}
	} )

}( jQuery );

/*!
 * jQuery blockUI plugin
 * Version 2.70.0-2014.11.23
 * Requires jQuery v1.7 or later
 *
 * Examples at: http://malsup.com/jquery/block/
 * Copyright (c) 2007-2013 M. Alsup
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 * Thanks to Amir-Hossein Sobhi for some excellent contributions!
 */

;
(function () {
	/*jshint eqeqeq:false curly:false latedef:false */
	"use strict";

	function setup( $ ) {
		$.fn._fadeIn = $.fn.fadeIn;

		var noOp = $.noop || function () {
			};

		// this bit is to ensure we don't call setExpression when we shouldn't (with extra muscle to handle
		// confusing userAgent strings on Vista)
		var msie = /MSIE/.test( navigator.userAgent );
		var ie6 = /MSIE 6.0/.test( navigator.userAgent ) && !/MSIE 8.0/.test( navigator.userAgent );
		var mode = document.documentMode || 0;
		var setExpr = $.isFunction( document.createElement( 'div' ).style.setExpression );

		// global $ methods for blocking/unblocking the entire page
		$.blockUI = function ( opts ) {
			install( window, opts );
		};
		$.unblockUI = function ( opts ) {
			remove( window, opts );
		};

		// convenience method for quick growl-like notifications  (http://www.google.com/search?q=growl)
		$.growlUI = function ( title, message, timeout, onClose ) {
			var $m = $( '<div class="growlUI"></div>' );
			if ( title ) $m.append( '<h1>' + title + '</h1>' );
			if ( message ) $m.append( '<h2>' + message + '</h2>' );
			if ( timeout === undefined ) timeout = 3000;

			// Added by konapun: Set timeout to 30 seconds if this growl is moused over, like normal toast notifications
			var callBlock = function ( opts ) {
				opts = opts || {};

				$.blockUI( {
					message    : $m,
					fadeIn     : typeof opts.fadeIn !== 'undefined' ? opts.fadeIn : 700,
					fadeOut    : typeof opts.fadeOut !== 'undefined' ? opts.fadeOut : 1000,
					timeout    : typeof opts.timeout !== 'undefined' ? opts.timeout : timeout,
					centerY    : false,
					showOverlay: false,
					onUnblock  : onClose,
					css        : $.blockUI.defaults.growlCSS
				} );
			};

			callBlock();
			var nonmousedOpacity = $m.css( 'opacity' );
			$m.mouseover( function () {
				callBlock( {
					fadeIn : 0,
					timeout: 30000
				} );

				var displayBlock = $( '.blockMsg' );
				displayBlock.stop(); // cancel fadeout if it has started
				displayBlock.fadeTo( 300, 1 ); // make it easier to read the message by removing transparency
			} ).mouseout( function () {
				$( '.blockMsg' ).fadeOut( 1000 );
			} );
			// End konapun additions
		};

		// plugin method for blocking element content
		$.fn.block = function ( opts ) {
			if ( this[0] === window ) {
				$.blockUI( opts );
				return this;
			}
			var fullOpts = $.extend( {}, $.blockUI.defaults, opts || {} );
			this.each( function () {
				var $el = $( this );
				if ( fullOpts.ignoreIfBlocked && $el.data( 'blockUI.isBlocked' ) )
					return;
				$el.unblock( {fadeOut: 0} );
			} );

			return this.each( function () {
				if ( $.css( this, 'position' ) == 'static' ) {
					this.style.position = 'relative';
					$( this ).data( 'blockUI.static', true );
				}
				this.style.zoom = 1; // force 'hasLayout' in ie
				install( this, opts );
			} );
		};

		// plugin method for unblocking element content
		$.fn.unblock = function ( opts ) {
			if ( this[0] === window ) {
				$.unblockUI( opts );
				return this;
			}
			return this.each( function () {
				remove( this, opts );
			} );
		};

		$.blockUI.version = 2.70; // 2nd generation blocking at no extra cost!

		// override these in your code to change the default behavior and style
		$.blockUI.defaults = {
			// message displayed when blocking (use null for no message)
			message: '<h1>Please wait...</h1>',

			title    : null,		// title string; only used when theme == true
			draggable: true,	// only used when theme == true (requires jquery-ui.js to be loaded)

			theme: false, // set to true to use with jQuery UI themes

			// styles for the message when blocking; if you wish to disable
			// these and use an external stylesheet then do this in your code:
			// $.blockUI.defaults.css = {};
			css: {
				padding        : 0,
				margin         : 0,
				width          : '30%',
				top            : '40%',
				left           : '35%',
				textAlign      : 'center',
				color          : '#000',
				border         : '3px solid #aaa',
				backgroundColor: '#fff',
				cursor         : 'wait'
			},

			// minimal style set used when themes are used
			themedCSS: {
				width: '30%',
				top  : '40%',
				left : '35%'
			},

			// styles for the overlay
			overlayCSS: {
				backgroundColor: '#000',
				opacity        : 0.6,
				cursor         : 'wait'
			},

			// style to replace wait cursor before unblocking to correct issue
			// of lingering wait cursor
			cursorReset: 'default',

			// styles applied when using $.growlUI
			growlCSS: {
				width                  : '350px',
				top                    : '10px',
				left                   : '',
				right                  : '10px',
				border                 : 'none',
				padding                : '5px',
				opacity                : 0.6,
				cursor                 : 'default',
				color                  : '#fff',
				backgroundColor        : '#000',
				'-webkit-border-radius': '10px',
				'-moz-border-radius'   : '10px',
				'border-radius'        : '10px'
			},

			// IE issues: 'about:blank' fails on HTTPS and javascript:false is s-l-o-w
			// (hat tip to Jorge H. N. de Vasconcelos)
			/*jshint scripturl:true */
			iframeSrc: /^https/i.test( window.location.href || '' ) ? 'javascript:false' : 'about:blank',

			// force usage of iframe in non-IE browsers (handy for blocking applets)
			forceIframe: false,

			// z-index for the blocking overlay
			baseZ: 1000,

			// set these to true to have the message automatically centered
			centerX: true, // <-- only effects element blocking (page block controlled via css above)
			centerY: true,

			// allow body element to be stetched in ie6; this makes blocking look better
			// on "short" pages.  disable if you wish to prevent changes to the body height
			allowBodyStretch: true,

			// enable if you want key and mouse events to be disabled for content that is blocked
			bindEvents: true,

			// be default blockUI will supress tab navigation from leaving blocking content
			// (if bindEvents is true)
			constrainTabKey: true,

			// fadeIn time in millis; set to 0 to disable fadeIn on block
			fadeIn: 200,

			// fadeOut time in millis; set to 0 to disable fadeOut on unblock
			fadeOut: 400,

			// time in millis to wait before auto-unblocking; set to 0 to disable auto-unblock
			timeout: 0,

			// disable if you don't want to show the overlay
			showOverlay: true,

			// if true, focus will be placed in the first available input field when
			// page blocking
			focusInput: true,

			// elements that can receive focus
			focusableElements: ':input:enabled:visible',

			// suppresses the use of overlay styles on FF/Linux (due to performance issues with opacity)
			// no longer needed in 2012
			// applyPlatformOpacityRules: true,

			// callback method invoked when fadeIn has completed and blocking message is visible
			onBlock: null,

			// callback method invoked when unblocking has completed; the callback is
			// passed the element that has been unblocked (which is the window object for page
			// blocks) and the options that were passed to the unblock call:
			//	onUnblock(element, options)
			onUnblock: null,

			// callback method invoked when the overlay area is clicked.
			// setting this will turn the cursor to a pointer, otherwise cursor defined in overlayCss will be used.
			onOverlayClick: null,

			// don't ask; if you really must know: http://groups.google.com/group/jquery-en/browse_thread/thread/36640a8730503595/2f6a79a77a78e493#2f6a79a77a78e493
			quirksmodeOffsetHack: 4,

			// class name of the message block
			blockMsgClass: 'blockMsg',

			// if it is already blocked, then ignore it (don't unblock and reblock)
			ignoreIfBlocked: false
		};

		// private data and functions follow...

		var pageBlock = null;
		var pageBlockEls = [];

		function install( el, opts ) {
			var css, themedCSS;
			var full = (el == window);
			var msg = (opts && opts.message !== undefined ? opts.message : undefined);
			opts = $.extend( {}, $.blockUI.defaults, opts || {} );

			if ( opts.ignoreIfBlocked && $( el ).data( 'blockUI.isBlocked' ) )
				return;

			opts.overlayCSS = $.extend( {}, $.blockUI.defaults.overlayCSS, opts.overlayCSS || {} );
			css = $.extend( {}, $.blockUI.defaults.css, opts.css || {} );
			if ( opts.onOverlayClick )
				opts.overlayCSS.cursor = 'pointer';

			themedCSS = $.extend( {}, $.blockUI.defaults.themedCSS, opts.themedCSS || {} );
			msg = msg === undefined ? opts.message : msg;

			// remove the current block (if there is one)
			if ( full && pageBlock )
				remove( window, {fadeOut: 0} );

			// if an existing element is being used as the blocking content then we capture
			// its current place in the DOM (and current display style) so we can restore
			// it when we unblock
			if ( msg && typeof msg != 'string' && (msg.parentNode || msg.jquery) ) {
				var node = msg.jquery ? msg[0] : msg;
				var data = {};
				$( el ).data( 'blockUI.history', data );
				data.el = node;
				data.parent = node.parentNode;
				data.display = node.style.display;
				data.position = node.style.position;
				if ( data.parent )
					data.parent.removeChild( node );
			}

			$( el ).data( 'blockUI.onUnblock', opts.onUnblock );
			var z = opts.baseZ;

			// blockUI uses 3 layers for blocking, for simplicity they are all used on every platform;
			// layer1 is the iframe layer which is used to supress bleed through of underlying content
			// layer2 is the overlay layer which has opacity and a wait cursor (by default)
			// layer3 is the message content that is displayed while blocking
			var lyr1, lyr2, lyr3, s;
			if ( msie || opts.forceIframe )
				lyr1 = $( '<iframe class="blockUI" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;position:absolute;width:100%;height:100%;top:0;left:0" src="' + opts.iframeSrc + '"></iframe>' );
			else
				lyr1 = $( '<div class="blockUI" style="display:none"></div>' );

			if ( opts.theme )
				lyr2 = $( '<div class="blockUI blockOverlay ui-widget-overlay" style="z-index:' + (z++) + ';display:none"></div>' );
			else
				lyr2 = $( '<div class="blockUI blockOverlay" style="z-index:' + (z++) + ';display:none;border:none;margin:0;padding:0;width:100%;height:100%;top:0;left:0"></div>' );

			if ( opts.theme && full ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:fixed">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if ( opts.theme ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement ui-dialog ui-widget ui-corner-all" style="z-index:' + (z + 10) + ';display:none;position:absolute">';
				if ( opts.title ) {
					s += '<div class="ui-widget-header ui-dialog-titlebar ui-corner-all blockTitle">' + (opts.title || '&nbsp;') + '</div>';
				}
				s += '<div class="ui-widget-content ui-dialog-content"></div>';
				s += '</div>';
			}
			else if ( full ) {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockPage" style="z-index:' + (z + 10) + ';display:none;position:fixed"></div>';
			}
			else {
				s = '<div class="blockUI ' + opts.blockMsgClass + ' blockElement" style="z-index:' + (z + 10) + ';display:none;position:absolute"></div>';
			}
			lyr3 = $( s );

			// if we have a message, style it
			if ( msg ) {
				if ( opts.theme ) {
					lyr3.css( themedCSS );
					lyr3.addClass( 'ui-widget-content' );
				}
				else
					lyr3.css( css );
			}

			// style the overlay
			if ( !opts.theme /*&& (!opts.applyPlatformOpacityRules)*/ )
				lyr2.css( opts.overlayCSS );
			lyr2.css( 'position', full ? 'fixed' : 'absolute' );

			// make iframe layer transparent in IE
			if ( msie || opts.forceIframe )
				lyr1.css( 'opacity', 0.0 );

			//$([lyr1[0],lyr2[0],lyr3[0]]).appendTo(full ? 'body' : el);
			var layers = [lyr1, lyr2, lyr3], $par = full ? $( 'body' ) : $( el );
			$.each( layers, function () {
				this.appendTo( $par );
			} );

			if ( opts.theme && opts.draggable && $.fn.draggable ) {
				lyr3.draggable( {
					handle: '.ui-dialog-titlebar',
					cancel: 'li'
				} );
			}

			// ie7 must use absolute positioning in quirks mode and to account for activex issues (when scrolling)
			var expr = setExpr && (!$.support.boxModel || $( 'object,embed', full ? null : el ).length > 0);
			if ( ie6 || expr ) {
				// give body 100% height
				if ( full && opts.allowBodyStretch && $.support.boxModel )
					$( 'html,body' ).css( 'height', '100%' );

				// fix ie6 issue when blocked element has a border width
				if ( (ie6 || !$.support.boxModel) && !full ) {
					var t = sz( el, 'borderTopWidth' ), l = sz( el, 'borderLeftWidth' );
					var fixT = t ? '(0 - ' + t + ')' : 0;
					var fixL = l ? '(0 - ' + l + ')' : 0;
				}

				// simulate fixed position
				$.each( layers, function ( i, o ) {
					var s = o[0].style;
					s.position = 'absolute';
					if ( i < 2 ) {
						if ( full )
							s.setExpression( 'height', 'Math.max(document.body.scrollHeight, document.body.offsetHeight) - (jQuery.support.boxModel?0:' + opts.quirksmodeOffsetHack + ') + "px"' );
						else
							s.setExpression( 'height', 'this.parentNode.offsetHeight + "px"' );
						if ( full )
							s.setExpression( 'width', 'jQuery.support.boxModel && document.documentElement.clientWidth || document.body.clientWidth + "px"' );
						else
							s.setExpression( 'width', 'this.parentNode.offsetWidth + "px"' );
						if ( fixL ) s.setExpression( 'left', fixL );
						if ( fixT ) s.setExpression( 'top', fixT );
					}
					else if ( opts.centerY ) {
						if ( full ) s.setExpression( 'top', '(document.documentElement.clientHeight || document.body.clientHeight) / 2 - (this.offsetHeight / 2) + (blah = document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + "px"' );
						s.marginTop = 0;
					}
					else if ( !opts.centerY && full ) {
						var top = (opts.css && opts.css.top) ? parseInt( opts.css.top, 10 ) : 0;
						var expression = '((document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + ' + top + ') + "px"';
						s.setExpression( 'top', expression );
					}
				} );
			}

			// show the message
			if ( msg ) {
				if ( opts.theme )
					lyr3.find( '.ui-widget-content' ).append( msg );
				else
					lyr3.append( msg );
				if ( msg.jquery || msg.nodeType )
					$( msg ).show();
			}

			if ( (msie || opts.forceIframe) && opts.showOverlay )
				lyr1.show(); // opacity is zero
			if ( opts.fadeIn ) {
				var cb = opts.onBlock ? opts.onBlock : noOp;
				var cb1 = (opts.showOverlay && !msg) ? cb : noOp;
				var cb2 = msg ? cb : noOp;
				if ( opts.showOverlay )
					lyr2._fadeIn( opts.fadeIn, cb1 );
				if ( msg )
					lyr3._fadeIn( opts.fadeIn, cb2 );
			}
			else {
				if ( opts.showOverlay )
					lyr2.show();
				if ( msg )
					lyr3.show();
				if ( opts.onBlock )
					opts.onBlock.bind( lyr3 )();
			}

			// bind key and mouse events
			bind( 1, el, opts );

			if ( full ) {
				pageBlock = lyr3[0];
				pageBlockEls = $( opts.focusableElements, pageBlock );
				if ( opts.focusInput )
					setTimeout( focus, 20 );
			}
			else
				center( lyr3[0], opts.centerX, opts.centerY );

			if ( opts.timeout ) {
				// auto-unblock
				var to = setTimeout( function () {
					if ( full )
						$.unblockUI( opts );
					else
						$( el ).unblock( opts );
				}, opts.timeout );
				$( el ).data( 'blockUI.timeout', to );
			}
		}

		// remove the block
		function remove( el, opts ) {
			var count;
			var full = (el == window);
			var $el = $( el );
			var data = $el.data( 'blockUI.history' );
			var to = $el.data( 'blockUI.timeout' );
			if ( to ) {
				clearTimeout( to );
				$el.removeData( 'blockUI.timeout' );
			}
			opts = $.extend( {}, $.blockUI.defaults, opts || {} );
			bind( 0, el, opts ); // unbind events

			if ( opts.onUnblock === null ) {
				opts.onUnblock = $el.data( 'blockUI.onUnblock' );
				$el.removeData( 'blockUI.onUnblock' );
			}

			var els;
			if ( full ) // crazy selector to handle odd field errors in ie6/7
				els = $( 'body' ).children().filter( '.blockUI' ).add( 'body > .blockUI' );
			else
				els = $el.find( '>.blockUI' );

			// fix cursor issue
			if ( opts.cursorReset ) {
				if ( els.length > 1 )
					els[1].style.cursor = opts.cursorReset;
				if ( els.length > 2 )
					els[2].style.cursor = opts.cursorReset;
			}

			if ( full )
				pageBlock = pageBlockEls = null;

			if ( opts.fadeOut ) {
				count = els.length;
				els.stop().fadeOut( opts.fadeOut, function () {
					if ( --count === 0 )
						reset( els, data, opts, el );
				} );
			}
			else
				reset( els, data, opts, el );
		}

		// move blocking element back into the DOM where it started
		function reset( els, data, opts, el ) {
			var $el = $( el );
			if ( $el.data( 'blockUI.isBlocked' ) )
				return;

			els.each( function ( i, o ) {
				// remove via DOM calls so we don't lose event handlers
				if ( this.parentNode )
					this.parentNode.removeChild( this );
			} );

			if ( data && data.el ) {
				data.el.style.display = data.display;
				data.el.style.position = data.position;
				data.el.style.cursor = 'default'; // #59
				if ( data.parent )
					data.parent.appendChild( data.el );
				$el.removeData( 'blockUI.history' );
			}

			if ( $el.data( 'blockUI.static' ) ) {
				$el.css( 'position', 'static' ); // #22
			}

			if ( typeof opts.onUnblock == 'function' )
				opts.onUnblock( el, opts );

			// fix issue in Safari 6 where block artifacts remain until reflow
			var body = $( document.body ), w = body.width(), cssW = body[0].style.width;
			body.width( w - 1 ).width( w );
			body[0].style.width = cssW;
		}

		// bind/unbind the handler
		function bind( b, el, opts ) {
			var full = el == window, $el = $( el );

			// don't bother unbinding if there is nothing to unbind
			if ( !b && (full && !pageBlock || !full && !$el.data( 'blockUI.isBlocked' )) )
				return;

			$el.data( 'blockUI.isBlocked', b );

			// don't bind events when overlay is not in use or if bindEvents is false
			if ( !full || !opts.bindEvents || (b && !opts.showOverlay) )
				return;

			// bind anchors and inputs for mouse and key events
			var events = 'mousedown mouseup keydown keypress keyup touchstart touchend touchmove';
			if ( b )
				$( document ).bind( events, opts, handler );
			else
				$( document ).unbind( events, handler );

			// former impl...
			//		var $e = $('a,:input');
			//		b ? $e.bind(events, opts, handler) : $e.unbind(events, handler);
		}

		// event handler to suppress keyboard/mouse events when blocking
		function handler( e ) {
			// allow tab navigation (conditionally)
			if ( e.type === 'keydown' && e.keyCode && e.keyCode == 9 ) {
				if ( pageBlock && e.data.constrainTabKey ) {
					var els = pageBlockEls;
					var fwd = !e.shiftKey && e.target === els[els.length - 1];
					var back = e.shiftKey && e.target === els[0];
					if ( fwd || back ) {
						setTimeout( function () {
							focus( back );
						}, 10 );
						return false;
					}
				}
			}
			var opts = e.data;
			var target = $( e.target );
			if ( target.hasClass( 'blockOverlay' ) && opts.onOverlayClick )
				opts.onOverlayClick( e );

			// allow events within the message content
			if ( target.parents( 'div.' + opts.blockMsgClass ).length > 0 )
				return true;

			// allow events for content that is not being blocked
			return target.parents().children().filter( 'div.blockUI' ).length === 0;
		}

		function focus( back ) {
			if ( !pageBlockEls )
				return;
			var e = pageBlockEls[back === true ? pageBlockEls.length - 1 : 0];
			if ( e )
				e.focus();
		}

		function center( el, x, y ) {
			var p = el.parentNode, s = el.style;
			var l = ((p.offsetWidth - el.offsetWidth) / 2) - sz( p, 'borderLeftWidth' );
			var t = ((p.offsetHeight - el.offsetHeight) / 2) - sz( p, 'borderTopWidth' );
			if ( x ) s.left = l > 0 ? (l + 'px') : '0';
			if ( y ) s.top = t > 0 ? (t + 'px') : '0';
		}

		function sz( el, p ) {
			return parseInt( $.css( el, p ), 10 ) || 0;
		}

	}


	/*global define:true */
	if ( typeof define === 'function' && define.amd && define.amd.jQuery ) {
		define( ['jquery'], setup );
	} else {
		setup( jQuery );
	}

})();

+function ( $ ) {
	'use strict';

	// COLLAPSE PUBLIC CLASS DEFINITION
	// ================================

	var Collapse = function ( element, options ) {
		this.$element = $( element )
		this.options = $.extend( {}, Collapse.DEFAULTS, options )
		this.$trigger = $( '[data-toggle="collapse"][href="#' + element.id + '"],' +
			'[data-toggle="collapse"][data-target="#' + element.id + '"]' )
		this.transitioning = null

		if ( this.options.parent ) {
			this.$parent = this.getParent()
		} else {
			this.addAriaAndCollapsedClass( this.$element, this.$trigger )
		}

		if ( this.options.toggle ) this.toggle()
	}

	Collapse.VERSION = '3.3.5'

	Collapse.TRANSITION_DURATION = 350

	Collapse.DEFAULTS = {
		toggle: true
	}

	Collapse.prototype.dimension = function () {
		var hasWidth = this.$element.hasClass( 'width' )
		return hasWidth ? 'width' : 'height'
	}

	Collapse.prototype.show = function () {
		if ( this.transitioning || this.$element.hasClass( 'in' ) ) return

		var activesData
		var actives = this.$parent && this.$parent.children( '.panel' ).children( '.in, .collapsing' )

		if ( actives && actives.length ) {
			activesData = actives.data( 'bs.collapse' )
			if ( activesData && activesData.transitioning ) return
		}

		var startEvent = $.Event( 'show.bs.collapse' )
		this.$element.trigger( startEvent )
		if ( startEvent.isDefaultPrevented() ) return

		if ( actives && actives.length ) {
			Plugin.call( actives, 'hide' )
			activesData || actives.data( 'bs.collapse', null )
		}

		var dimension = this.dimension()

		this.$element
			.removeClass( 'collapse' )
			.addClass( 'collapsing' )[dimension]( 0 )
			.attr( 'aria-expanded', true )

		this.$trigger
			.removeClass( 'collapsed' )
			.attr( 'aria-expanded', true )

		this.transitioning = 1

		var complete = function () {
			this.$element
				.removeClass( 'collapsing' )
				.addClass( 'collapse in' )[dimension]( '' )
			this.transitioning = 0
			this.$element
				.trigger( 'shown.bs.collapse' )
		}

		if ( !$.support.transition ) return complete.call( this )

		var scrollSize = $.camelCase( ['scroll', dimension].join( '-' ) )

		this.$element
			.one( 'bsTransitionEnd', $.proxy( complete, this ) )
			.emulateTransitionEnd( Collapse.TRANSITION_DURATION )[dimension]( this.$element[0][scrollSize] )
	}

	Collapse.prototype.hide = function () {
		if ( this.transitioning || !this.$element.hasClass( 'in' ) ) return

		var startEvent = $.Event( 'hide.bs.collapse' )
		this.$element.trigger( startEvent )
		if ( startEvent.isDefaultPrevented() ) return

		var dimension = this.dimension()

		this.$element[dimension]( this.$element[dimension]() )[0].offsetHeight

		this.$element
			.addClass( 'collapsing' )
			.removeClass( 'collapse in' )
			.attr( 'aria-expanded', false )

		this.$trigger
			.addClass( 'collapsed' )
			.attr( 'aria-expanded', false )

		this.transitioning = 1

		var complete = function () {
			this.transitioning = 0
			this.$element
				.removeClass( 'collapsing' )
				.addClass( 'collapse' )
				.trigger( 'hidden.bs.collapse' )
		}

		if ( !$.support.transition ) return complete.call( this )

		this.$element
			[dimension]( 0 )
			.one( 'bsTransitionEnd', $.proxy( complete, this ) )
			.emulateTransitionEnd( Collapse.TRANSITION_DURATION )
	}

	Collapse.prototype.toggle = function () {
		this[this.$element.hasClass( 'in' ) ? 'hide' : 'show']()
	}

	Collapse.prototype.getParent = function () {
		return $( this.options.parent )
			.find( '[data-toggle="collapse"][data-parent="' + this.options.parent + '"]' )
			.each( $.proxy( function ( i, element ) {
				var $element = $( element )
				this.addAriaAndCollapsedClass( getTargetFromTrigger( $element ), $element )
			}, this ) )
			.end()
	}

	Collapse.prototype.addAriaAndCollapsedClass = function ( $element, $trigger ) {
		var isOpen = $element.hasClass( 'in' )

		$element.attr( 'aria-expanded', isOpen )
		$trigger
			.toggleClass( 'collapsed', !isOpen )
			.attr( 'aria-expanded', isOpen )
	}

	function getTargetFromTrigger( $trigger ) {
		var href
		var target = $trigger.attr( 'data-target' )
			|| (href = $trigger.attr( 'href' )) && href.replace( /.*(?=#[^\s]+$)/, '' ) // strip for ie7

		return $( target )
	}


	// COLLAPSE PLUGIN DEFINITION
	// ==========================

	function Plugin( option ) {
		return this.each( function () {
			var $this = $( this )
			var data = $this.data( 'bs.collapse' )
			var options = $.extend( {}, Collapse.DEFAULTS, $this.data(), typeof option == 'object' && option )

			if ( !data && options.toggle && /show|hide/.test( option ) ) options.toggle = false
			if ( !data ) $this.data( 'bs.collapse', (data = new Collapse( this, options )) )
			if ( typeof option == 'string' ) data[option]()
		} )
	}

	var old = $.fn.collapse

	$.fn.collapse = Plugin
	$.fn.collapse.Constructor = Collapse


	// COLLAPSE NO CONFLICT
	// ====================

	$.fn.collapse.noConflict = function () {
		$.fn.collapse = old
		return this
	}


	// COLLAPSE DATA-API
	// =================

	$( document ).on( 'click.bs.collapse.data-api', '[data-toggle="collapse"]', function ( e ) {
		var $this = $( this )

		if ( !$this.attr( 'data-target' ) ) e.preventDefault()

		var $target = getTargetFromTrigger( $this )
		var data = $target.data( 'bs.collapse' )
		var option = data ? 'toggle' : $this.data()

		Plugin.call( $target, option )
	} )

}( jQuery );

var give_ffm_frontend;

(function ( $ ) {

	window.Give_FFM_Uploader = function ( browse_button, container, max, type, allowed_type, max_file_size ) {
		this.container = container;
		this.browse_button = browse_button;
		this.max = max || 1;
		this.count = $( '#' + container ).find( '.ffm-attachment-list > li' ).length; //count how many items are there
		//if no element found on the page, bail out
		if ( !$( '#' + browse_button ).length ) {
			return;
		}

		//instantiate the uploader
		this.uploader = new plupload.Uploader( {
			runtimes        : 'html5,html4',
			browse_button   : browse_button,
			container       : container,
			multipart       : true,
			multipart_params: {
				action: 'ffm_file_upload'
			},
			multiple_queues : false,
			multi_selection : false,
			urlstream_upload: true,
			file_data_name  : 'ffm_file',
			max_file_size   : max_file_size + 'kb',
			url             : give_ffm_frontend.plupload.url + '&type=' + type,
			flash_swf_url   : give_ffm_frontend.flash_swf_url,
			filters         : [{
				title     : 'Allowed Files',
				extensions: allowed_type
			}]
		} );

		//attach event handlers
		this.uploader.bind( 'Init', $.proxy( this, 'init' ) );
		this.uploader.bind( 'FilesAdded', $.proxy( this, 'added' ) );
		this.uploader.bind( 'QueueChanged', $.proxy( this, 'upload' ) );
		this.uploader.bind( 'UploadProgress', $.proxy( this, 'progress' ) );
		this.uploader.bind( 'Error', $.proxy( this, 'error' ) );
		this.uploader.bind( 'FileUploaded', $.proxy( this, 'uploaded' ) );

		this.uploader.init();

		$( '#' + container ).on( 'click', 'a.attachment-delete', $.proxy( this.removeAttachment, this ) );
	};

	Give_FFM_Uploader.prototype = {

		init: function ( up, params ) {
			this.showHide();
		},

		showHide: function () {

			if ( this.count >= this.max ) {

				$( '#' + this.container ).find( '.file-selector' ).hide();

				return;
			}

			$( '#' + this.container ).find( '.file-selector' ).show();
		},

		added: function ( up, files ) {
			var $container = $( '#' + this.container ).find( '.ffm-attachment-upload-filelist' );

			this.count += 1;
			this.showHide();

			$.each( files, function ( i, file ) {
				$container.append(
					'<div class="upload-item" id="' + file.id + '"><div class="progress progress-striped active"><div class="bar"></div></div><div class="filename original">' +
					file.name + ' (' + plupload.formatSize( file.size ) + ') <b></b>' +
					'</div></div>' );
			} );

			up.refresh(); // Reposition Flash/Silverlight
			up.start();
		},

		upload: function ( uploader ) {
			this.uploader.start();
		},

		progress: function ( up, file ) {
			var item = $( '#' + file.id );

			$( '.bar', item ).css( {width: file.percent + '%'} );
			$( '.percent', item ).html( file.percent + '%' );
		},

		error: function ( up, error ) {
			$( '#' + this.container ).find( '#' + error.file.id ).remove();
			alert( 'Error #' + error.code + ': ' + error.message );

			this.count -= 1;
			this.showHide();
			this.uploader.refresh();
		},

		uploaded: function ( up, file, response ) {
			 //var res = $.parseJSON(response);
			 //console.log( typeof response, typeof response.response);
			 //console.log(response, response.response);

			$( '#' + file.id + " b" ).html( "100%" );
			$( '#' + file.id ).remove();

			if ( response.response !== 'error' ) {
				var $container = $( '#' + this.container ).find( '.ffm-attachment-list' );
				$container.append( response.response );
			} else {
				alert( res.error );
				this.count -= 1;
				this.showHide();
			}
		},

		removeAttachment: function ( e ) {
			e.preventDefault();

			var self = this,
				el = $( e.currentTarget );

			if ( confirm( give_ffm_frontend.confirmMsg ) ) {
				var data = {
					'attach_id': el.data( 'attach_id' ),
					'nonce'    : give_ffm_frontend.nonce,
					'action'   : 'ffm_file_del'
				};

				jQuery.post( give_ffm_frontend.ajaxurl, data, function () {
					el.parent().parent().remove();

					self.count -= 1;
					self.showHide();
					self.uploader.refresh();
				} );
			}
		}
	};
})( jQuery );
;
(function ($) {

	$(function () {
		// mask phone fields with domestic formatting
		$('.js-phone-domestic').mask('(999) 999-9999');
	});

})(jQuery);
; //<- here for good measure
(function ($) {

	var $formEditor = $('ul#give-form-fields-editor');

	var Editor = {

		init: function () {

			this.makeSortable();

			// collapse all
			$('button.ffm-collapse').on('click', this.collapseEditFields);

			// add field click
			$('.give-form-fields-buttons').on('click', 'button', this.addNewField);

			// remove form field
			$formEditor.on('click', '.item-delete', this.removeFormField);

			// on blur event: set meta key
			$formEditor.on('blur', '.js-ffm-field-label', this.setMetaKey);
			$formEditor.on('blur', '.js-ffm-meta-key', this.setMetaKey);
			$formEditor.on('blur', '.js-ffm-meta-key', this.setEmailTag);
			$('.js-ffm-meta-key', $formEditor ).blur();

			// on blur event: check meta key
			$formEditor.on('blur', '.js-ffm-meta-key', this.checkDuplicateMetaKeys);

			// on change event: checkbox|radio fields
			$formEditor.on('change', '.give-form-fields-sub-fields input[type=text]', function () {
				$(this).prev('input[type=checkbox], input[type=radio]').val($(this).val());
			});

			// on change event: checkbox field for enabling/disabling ffm fields.
			$formEditor.on( 'change', '.hide-field-label input', this.showHideFFMFields );

			// on change event: checkbox|radio fields
			$formEditor.on('click', 'input[type=checkbox].multicolumn', function () {
				var $self = $(this),
					$parent = $self.closest('.give-form-fields-rows');

				if ($self.is(':checked')) {
					$parent.next().hide().next().hide();
					$parent.siblings('.column-names').show();
				} else {
					$parent.next().show().next().show();
					$parent.siblings('.column-names').hide();
				}
			});

			// clone and remove repeated field
			$formEditor.on('click', '.ffm-clone-field', this.cloneField);
			$formEditor.on('click', '.ffm-remove-field', this.removeField);

			$formEditor.on( 'click', '.give-icon-locked-anchor', this.unlock_meta_key );

			// show hide ffm field in export donation page
			$( document ).on( 'give_export_donations_form_response', function ( ev, response ) {
				/**
				 * FFM Fields
				 */
				var ffm_fields = (
					'undefined' !== typeof response.ffm_fields &&
					null !== response.ffm_fields
				) ? response.ffm_fields : '';

				if ( ffm_fields ) {

					var ffm_field_list = $( '.give-export-donations-ffm ul' );

					// Loop through FFM fields & output
					$( ffm_fields ).each( function ( index, value ) {

						// Repeater sections.
						var repeater_sections = (
							typeof value.repeaters !== 'undefined'
						) ? value.repeaters : '';

						if ( repeater_sections ) {

							ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

							var parent_title = '';
							// Repeater section field.
							$( repeater_sections ).each( function ( index, value ) {
								if ( parent_title !== value.parent_title ) {
									ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section-title" data-parent-meta="' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.parent_meta + '"><input type="checkbox" name="give_give_donations_export_parent[' + value.parent_meta + ']" id="give-give-donations-ffm-field-' + value.parent_meta + '">' + value.parent_title + '</label></li>' );
								}
								parent_title = value.parent_title;
								ffm_field_list.append( '<li class="give-export-donation-checkbox-remove repeater-section repeater-section-' + value.parent_meta + '"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.subkey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label></li>' );
							} );
						}
						// Repeater sections.
						var single_repeaters = (
							typeof value.single !== 'undefined'
						) ? value.single : '';

						if ( single_repeaters ) {

							ffm_field_list.closest( 'tr' ).removeClass( 'give-hidden' );

							// Repeater section field.
							$( single_repeaters ).each( function ( index, value ) {
								ffm_field_list.append( '<li class="give-export-donation-checkbox-remove"><label for="give-give-donations-ffm-field-' + value.subkey + '"><input type="checkbox" name="give_give_donations_export_option[' + value.metakey + ']" id="give-give-donations-ffm-field-' + value.subkey + '">' + value.label + '</label> </li>' );
							} );
						}
					} );
				}
			} );
		},

		unlock_meta_key: function( e ) {

			var user_input = confirm( give_ffm_formbuilder.notify_meta_key_lock );

			if( user_input ) {
				$( this ).closest( '.give-meta-key-wrap' ).find( 'input[type="text"]' ).removeAttr('readonly');
				$( this ).remove();
			}

			e.preventDefault();
		},

		/**
		 * Make Sortable
		 */
		makeSortable: function () {
			$formEditor = $('ul#give-form-fields-editor');

			if ($formEditor) {
				$formEditor.sortable({
					placeholder: "sortable-placeholder",
					handle: '> .ffm-legend',
					distance: 5
				});
			}
		},

		/**
		 * Add New Field
		 *
		 * @param e
		 */
		addNewField: function (e) {
			e.preventDefault();

			$('.ffm-loading').fadeIn();

			var $self = $(this),
				$formEditor = $('ul#give-form-fields-editor'),
				$metaBox = $('#ffm-metabox-editor'),
				name = $self.data('name'),
				type = $self.data('type'),
				data = {
					name: name,
					type: type,
					order: $formEditor.find('li').length + 1,
					action: 'give-form-fields_add_el'
				};

			$.post(ajaxurl, data, function (res) {
				$formEditor.append(res);
				Editor.makeSortable();
				$('.ffm-loading').fadeOut(); //hide loading
				$('.ffm-no-fields').hide(); //hide no fields placeholder
			});
		},

		/**
		 * Remove Form Field
		 * @param e
		 */
		removeFormField: function (e) {
			e.preventDefault();

			if (confirm('Are you sure you want to remove this form field?')) {
				$(this).closest('li').fadeOut(function () {
					$(this).remove();
				});
			}
		},

		/**
		 * Clone Field
		 *
		 * @param e
		 */
		cloneField: function (e) {
			e.preventDefault();

			var $div = $(this).closest('div');
			var $clone = $div.clone();

			//clear the inputs
			$clone.find('input').val('');
			$clone.find(':checked').attr('checked', '');
			$div.after($clone);
		},

		/**
		 * Remove Field
		 */
		removeField: function () {
			//check if it's the only item
			var $parent = $(this).closest('div');
			var items = $parent.siblings().andSelf().length;

			if (items > 1) {
				$parent.remove();
			}
		},

		/**
		 * Set Meta Key
		 */
		setMetaKey: function () {
			var $self = $(this);

			if ($self.hasClass('js-ffm-field-label')) {
				$fieldLabel = $self;
				$metaKey = $self.closest('.give-form-fields-rows').next().find('.js-ffm-meta-key');
			} else if ($self.hasClass('js-ffm-meta-key')) {
				$fieldLabel = $self.closest('.give-form-fields-rows').prev().find('.js-ffm-field-label');
				$metaKey = $self;
			} else {
				return false;
			}

			// only set meta key if input exists and is empty
			if ($metaKey.length && !$metaKey.val()) {

				var val = $fieldLabel
					.val() // get value of Field Label input
					.trim() // remove leading and trailing whitespace
					.toLowerCase() // convert to lowercase
					.replace(/[\s\-]/g, '_') // replace spaces and - with _
					.replace(/[^a-z0-9_]/g, ''); // remove all chars except lowercase, numeric, or _

				if (val.length > 200) {
					val = val.substring(0, 200);
				}

				if ( $metaKey.val() !== val ) {
					$metaKey.val( val ).blur();
				}
			}
		},

		/**
		 * Set Meta Key
		 */
		setEmailTag: function () {
			var $parent = $(this).closest('.give-form-fields-holder');

			$( '.give-form-field-email-tag-field', $parent ).val( '{meta_donation_' + $(this).val() + '}' );
		},

		/**
		 * Collapse
		 * @param e
		 */
		collapseEditFields: function (e) {
			e.preventDefault();

			$('ul#give-form-fields-editor').children('li').find('.collapse').collapse('toggle');
		},

		/**
		 * Check for duplicate Meta Keys
		 *
		 * @param e
		 */
		checkDuplicateMetaKeys: function (e) {
			$metaKey = $(e.target)
			justChecked = $metaKey.data('justChecked');

			// do not run if Meta Key is blank
			if ('' === $metaKey.val()) {
				return;
			}

			// prevent infinite alert loop after refocusing
			if (justChecked) {
				$metaKey.data('justChecked', false);
				return;
			}

			// get all Meta Key values in array and sort alphabetically
			var $allMetaKeys = $('#give-form-fields-editor').find('.js-ffm-meta-key').map(function () {
				return $(this).val();
			}).sort();

			// check for duplicates
			for (var i = 0; i < $allMetaKeys.length - 1; i++) {
				// only trigger alert if duplicate found and not blank
				if ($allMetaKeys[i + 1] == $allMetaKeys[i] && $allMetaKeys[i].length) {
					$metaKey.data('justChecked', true);
					alert(give_ffm_formbuilder.error_duplicate_meta);

					// refocus on duplicate Meta Key input
					setTimeout(function () {
						$metaKey.data('justChecked', false);
						$metaKey.focus();
					}, 50);

					return;
				}
			}
		},


		/**
		 * Sets the label title for enabled/disabled fields.
		 */
		showHideFFMFields: function() {
			if ( this.checked ) {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_enable );
			} else {
				$( this ).closest( '.hide-field-label' ).attr( 'title', give_ffm_formbuilder.hidden_field_disable );
			}
		}
	};

	// on DOM ready
	$(function () {
		Editor.init();
	});

})(jQuery);


/**
 * This JS is releated to repeatation fields
 *
 * @since 1.2.1
 */
jQuery( function ( $ ) {
	var give_ffm = {
		init: function () {
			$( 'body' ).on( 'click', 'span.ffm-clone-field', this.cloneField );
			$( 'body' ).on( 'click', 'span.ffm-remove-field', this.removeField );
		},
		cloneField: function ( e ) {
			e.preventDefault();

			var $div = $( this ).closest( 'tr' );
			var $clone = $div.clone();
			// console.log($clone);

			//clear the inputs
			$clone.find( 'input' ).val( '' );
			$clone.find( ':checked' ).attr( 'checked', '' );
			$div.after( $clone );
		},

		removeField: function () {
			//check if it's the only item
			var $parent = $( this ).closest( 'tr' );
			var items = $parent.siblings().andSelf().length;

			if ( items > 1 ) {
				$parent.remove();
			}
		}
	};

	give_ffm.init();
} );