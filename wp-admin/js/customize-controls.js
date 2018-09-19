/* global _wpCustomizeHeader, _wpCustomizeBackground, _wpMediaViewsL10n, MediaElementPlayer, console, confirm */
(function( exports, $ ){
	var Container, focus, normalizedTransitionendEventName, api = wp.customize;

	/**
	 * A notification that is displayed in a full-screen overlay.
	 *
	 * @since 4.9.0
	 * @class
	 * @augments wp.customize.Notification
	 */
	api.OverlayNotification = api.Notification.extend({

		/**
		 * Whether the notification should show a loading spinner.
		 *
		 * @since 4.9.0
		 * @var {boolean}
		 */
		loading: false,

		/**
		 * Initialize.
		 *
		 * @since 4.9.0
		 *
		 * @param {string} code - Code.
		 * @param {object} params - Params.
		 */
		initialize: function( code, params ) {
			var notification = this;
			api.Notification.prototype.initialize.call( notification, code, params );
			notification.containerClasses += ' notification-overlay';
			if ( notification.loading ) {
				notification.containerClasses += ' notification-loading';
			}
		},

		/**
		 * Render notification.
		 *
		 * @since 4.9.0
		 *
		 * @return {jQuery} Notification container.
		 */
		render: function() {
			var li = api.Notification.prototype.render.call( this );
			li.on( 'keydown', _.bind( this.handleEscape, this ) );
			return li;
		},

		/**
		 * Stop propagation on escape key presses, but also dismiss notification if it is dismissible.
		 *
		 * @since 4.9.0
		 *
		 * @param {jQuery.Event} event - Event.
		 * @returns {void}
		 */
		handleEscape: function( event ) {
			var notification = this;
			if ( 27 === event.which ) {
				event.stopPropagation();
				if ( notification.dismissible && notification.parent ) {
					notification.parent.remove( notification.code );
				}
			}
		}
	});

	/**
	 * A collection of observable notifications.
	 *
	 * @since 4.9.0
	 * @class
	 * @augments wp.customize.Values
	 */
	api.Notifications = api.Values.extend({

		/**
		 * Whether the alternative style should be used.
		 *
		 * @since 4.9.0
		 * @type {boolean}
		 */
		alt: false,

		/**
		 * The default constructor for items of the collection.
		 *
		 * @since 4.9.0
		 * @type {object}
		 */
		defaultConstructor: api.Notification,

		/**
		 * Initialize notifications area.
		 *
		 * @since 4.9.0
		 * @constructor
		 * @param {object}  options - Options.
		 * @param {jQuery}  [options.container] - Container element for notifications. This can be injected later.
		 * @param {boolean} [options.alt] - Whether alternative style should be used when rendering notifications.
		 * @returns {void}
		 * @this {wp.customize.Notifications}
		 */
		initialize: function( options ) {
			var collection = this;

			api.Values.prototype.initialize.call( collection, options );

			_.bindAll( collection, 'constrainFocus' );

			// Keep track of the order in which the notifications were added for sorting purposes.
			collection._addedIncrement = 0;
			collection._addedOrder = {};

			// Trigger change event when notification is added or removed.
			collection.bind( 'add', function( notification ) {
				collection.trigger( 'change', notification );
			});
			collection.bind( 'removed', function( notification ) {
				collection.trigger( 'change', notification );
			});
		},

		/**
		 * Get the number of notifications added.
		 *
		 * @since 4.9.0
		 * @return {number} Count of notifications.
		 */
		count: function() {
			return _.size( this._value );
		},

		/**
		 * Add notification to the collection.
		 *
		 * @since 4.9.0
		 *
		 * @param {string|wp.customize.Notification} notification - Notification object to add. Alternatively code may be supplied, and in that case the second notificationObject argument must be supplied.
		 * @param {wp.customize.Notification} [notificationObject] - Notification to add when first argument is the code string.
		 * @returns {wp.customize.Notification} Added notification (or existing instance if it was already added).
		 */
		add: function( notification, notificationObject ) {
			var collection = this, code, instance;
			if ( 'string' === typeof notification ) {
				code = notification;
				instance = notificationObject;
			} else {
				code = notification.code;
				instance = notification;
			}
			if ( ! collection.has( code ) ) {
				collection._addedIncrement += 1;
				collection._addedOrder[ code ] = collection._addedIncrement;
			}
			return api.Values.prototype.add.call( collection, code, instance );
		},

		/**
		 * Add notification to the collection.
		 *
		 * @since 4.9.0
		 * @param {string} code - Notification code to remove.
		 * @return {api.Notification} Added instance (or existing instance if it was already added).
		 */
		remove: function( code ) {
			var collection = this;
			delete collection._addedOrder[ code ];
			return api.Values.prototype.remove.call( this, code );
		},

		/**
		 * Get list of notifications.
		 *
		 * Notifications may be sorted by type followed by added time.
		 *
		 * @since 4.9.0
		 * @param {object}  args - Args.
		 * @param {boolean} [args.sort=false] - Whether to return the notifications sorted.
		 * @return {Array.<wp.customize.Notification>} Notifications.
		 * @this {wp.customize.Notifications}
		 */
		get: function( args ) {
			var collection = this, notifications, errorTypePriorities, params;
			notifications = _.values( collection._value );

			params = _.extend(
				{ sort: false },
				args
			);

			if ( params.sort ) {
				errorTypePriorities = { error: 4, warning: 3, success: 2, info: 1 };
				notifications.sort( function( a, b ) {
					var aPriority = 0, bPriority = 0;
					if ( ! _.isUndefined( errorTypePriorities[ a.type ] ) ) {
						aPriority = errorTypePriorities[ a.type ];
					}
					if ( ! _.isUndefined( errorTypePriorities[ b.type ] ) ) {
						bPriority = errorTypePriorities[ b.type ];
					}
					if ( aPriority !== bPriority ) {
						return bPriority - aPriority; // Show errors first.
					}
					return collection._addedOrder[ b.code ] - collection._addedOrder[ a.code ]; // Show newer notifications higher.
				});
			}

			return notifications;
		},

		/**
		 * Render notifications area.
		 *
		 * @since 4.9.0
		 * @returns {void}
		 * @this {wp.customize.Notifications}
		 */
		render: function() {
			var collection = this,
				notifications, hadOverlayNotification = false, hasOverlayNotification, overlayNotifications = [],
				previousNotificationsByCode = {},
				listElement, focusableElements;

			// Short-circuit if there are no container to render into.
			if ( ! collection.container || ! collection.container.length ) {
				return;
			}

			notifications = collection.get( { sort: true } );
			collection.container.toggle( 0 !== notifications.length );

			// Short-circuit if there are no changes to the notifications.
			if ( collection.container.is( collection.previousContainer ) && _.isEqual( notifications, collection.previousNotifications ) ) {
				return;
			}

			// Make sure list is part of the container.
			listElement = collection.container.children( 'ul' ).first();
			if ( ! listElement.length ) {
				listElement = $( '<ul></ul>' );
				collection.container.append( listElement );
			}

			// Remove all notifications prior to re-rendering.
			listElement.find( '> [data-code]' ).remove();

			_.each( collection.previousNotifications, function( notification ) {
				previousNotificationsByCode[ notification.code ] = notification;
			});

			// Add all notifications in the sorted order.
			_.each( notifications, function( notification ) {
				var notificationContainer;
				if ( wp.a11y && ( ! previousNotificationsByCode[ notification.code ] || ! _.isEqual( notification.message, previousNotificationsByCode[ notification.code ].message ) ) ) {
					wp.a11y.speak( notification.message, 'assertive' );
				}
				notificationContainer = $( notification.render() );
				notification.container = notificationContainer;
				listElement.append( notificationContainer ); // @todo Consider slideDown() as enhancement.

				if ( notification.extended( api.OverlayNotification ) ) {
					overlayNotifications.push( notification );
				}
			});
			hasOverlayNotification = Boolean( overlayNotifications.length );

			if ( collection.previousNotifications ) {
				hadOverlayNotification = Boolean( _.find( collection.previousNotifications, function( notification ) {
					return notification.extended( api.OverlayNotification );
				} ) );
			}

			if ( hasOverlayNotification !== hadOverlayNotification ) {
				$( document.body ).toggleClass( 'customize-loading', hasOverlayNotification );
				collection.container.toggleClass( 'has-overlay-notifications', hasOverlayNotification );
				if ( hasOverlayNotification ) {
					collection.previousActiveElement = document.activeElement;
					$( document ).on( 'keydown', collection.constrainFocus );
				} else {
					$( document ).off( 'keydown', collection.constrainFocus );
				}
			}

			if ( hasOverlayNotification ) {
				collection.focusContainer = overlayNotifications[ overlayNotifications.length - 1 ].container;
				collection.focusContainer.prop( 'tabIndex', -1 );
				focusableElements = collection.focusContainer.find( ':focusable' );
				if ( focusableElements.length ) {
					focusableElements.first().focus();
				} else {
					collection.focusContainer.focus();
				}
			} else if ( collection.previousActiveElement ) {
				$( collection.previousActiveElement ).focus();
				collection.previousActiveElement = null;
			}

			collection.previousNotifications = notifications;
			collection.previousContainer = collection.container;
			collection.trigger( 'rendered' );
		},

		/**
		 * Constrain focus on focus container.
		 *
		 * @since 4.9.0
		 *
		 * @param {jQuery.Event} event - Event.
		 * @returns {void}
		 */
		constrainFocus: function constrainFocus( event ) {
			var collection = this, focusableElements;

			// Prevent keys from escaping.
			event.stopPropagation();

			if ( 9 !== event.which ) { // Tab key.
				return;
			}

			focusableElements = collection.focusContainer.find( ':focusable' );
			if ( 0 === focusableElements.length ) {
				focusableElements = collection.focusContainer;
			}

			if ( ! $.contains( collection.focusContainer[0], event.target ) || ! $.contains( collection.focusContainer[0], document.activeElement ) ) {
				event.preventDefault();
				focusableElements.first().focus();
			} else if ( focusableElements.last().is( event.target ) && ! event.shiftKey ) {
				event.preventDefault();
				focusableElements.first().focus();
			} else if ( focusableElements.first().is( event.target ) && event.shiftKey ) {
				event.preventDefault();
				focusableElements.last().focus();
			}
		}
	});

	/**
	 * A Customizer Setting.
	 *
	 * A setting is WordPress data (theme mod, option, menu, etc.) that the user can
	 * draft changes to in the Customizer.
	 *
	 * @see PHP class WP_Customize_Setting.
	 *
	 * @since 3.4.0
	 * @class
	 * @augments wp.customize.Value
	 * @augments wp.customize.Class
	 */
	api.Setting = api.Value.extend({

		/**
		 * Default params.
		 *
		 * @since 4.9.0
		 * @var {object}
		 */
		defaults: {
			transport: 'refresh',
			dirty: false
		},

		/**
		 * Initialize.
		 *
		 * @since 3.4.0
		 *
		 * @param {string}  id                          - The setting ID.
		 * @param {*}       value                       - The initial value of the setting.
		 * @param {object}  [options={}]                - Options.
		 * @param {string}  [options.transport=refresh] - The transport to use for previewing. Supports 'refresh' and 'postMessage'.
		 * @param {boolean} [options.dirty=false]       - Whether the setting should be considered initially dirty.
		 * @param {object}  [options.previewer]         - The Previewer instance to sync with. Defaults to wp.customize.previewer.
		 */
		initialize: function( id, value, options ) {
			var setting = this, params;
			params = _.extend(
				{ previewer: api.previewer },
				setting.defaults,
				options || {}
			);

			api.Value.prototype.initialize.call( setting, value, params );

			setting.id = id;
			setting._dirty = params.dirty; // The _dirty property is what the Customizer reads from.
			setting.notifications = new api.Notifications();

			// Whenever the setting's value changes, refresh the preview.
			setting.bind( setting.preview );
		},

		/**
		 * Refresh the preview, respective of the setting's refresh policy.
		 *
		 * If the preview hasn't sent a keep-alive message and is likely
		 * disconnected by having navigated to a non-allowed URL, then the
		 * refresh transport will be forced when postMessage is the transport.
		 * Note that postMessage does not throw an error when the recipient window
		 * fails to match the origin window, so using try/catch around the
		 * previewer.send() call to then fallback to refresh will not work.
		 *
		 * @since 3.4.0
		 * @access public
		 *
		 * @returns {void}
		 */
		preview: function() {
			var setting = this, transport;
			transport = setting.transport;

			if ( 'postMessage' === transport && ! api.state( 'previewerAlive' ).get() ) {
				transport = 'refresh';
			}

			if ( 'postMessage' === transport ) {
				setting.previewer.send( 'setting', [ setting.id, setting() ] );
			} else if ( 'refresh' === transport ) {
				setting.previewer.refresh();
			}
		},

		/**
		 * Find controls associated with this setting.
		 *
		 * @since 4.6.0
		 * @returns {wp.customize.Control[]} Controls associated with setting.
		 */
		findControls: function() {
			var setting = this, controls = [];
			api.control.each( function( control ) {
				_.each( control.settings, function( controlSetting ) {
					if ( controlSetting.id === setting.id ) {
						controls.push( control );
					}
				} );
			} );
			return controls;
		}
	});

	/**
	 * Current change count.
	 *
	 * @since 4.7.0
	 * @type {number}
	 * @protected
	 */
	api._latestRevision = 0;

	/**
	 * Last revision that was saved.
	 *
	 * @since 4.7.0
	 * @type {number}
	 * @protected
	 */
	api._lastSavedRevision = 0;

	/**
	 * Latest revisions associated with the updated setting.
	 *
	 * @since 4.7.0
	 * @type {object}
	 * @protected
	 */
	api._latestSettingRevisions = {};

	/*
	 * Keep track of the revision associated with each updated setting so that
	 * requestChangesetUpdate knows which dirty settings to include. Also, once
	 * ready is triggered and all initial settings have been added, increment
	 * revision for each newly-created initially-dirty setting so that it will
	 * also be included in changeset update requests.
	 */
	api.bind( 'change', function incrementChangedSettingRevision( setting ) {
		api._latestRevision += 1;
		api._latestSettingRevisions[ setting.id ] = api._latestRevision;
	} );
	api.bind( 'ready', function() {
		api.bind( 'add', function incrementCreatedSettingRevision( setting ) {
			if ( setting._dirty ) {
				api._latestRevision += 1;
				api._latestSettingRevisions[ setting.id ] = api._latestRevision;
			}
		} );
	} );

	/**
	 * Get the dirty setting values.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param {object} [options] Options.
	 * @param {boolean} [options.unsaved=false] Whether only values not saved yet into a changeset will be returned (differential changes).
	 * @returns {object} Dirty setting values.
	 */
	api.dirtyValues = function dirtyValues( options ) {
		var values = {};
		api.each( function( setting ) {
			var settingRevision;

			if ( ! setting._dirty ) {
				return;
			}

			settingRevision = api._latestSettingRevisions[ setting.id ];

			// Skip including settings that have already been included in the changeset, if only requesting unsaved.
			if ( api.state( 'changesetStatus' ).get() && ( options && options.unsaved ) && ( _.isUndefined( settingRevision ) || settingRevision <= api._lastSavedRevision ) ) {
				return;
			}

			values[ setting.id ] = setting.get();
		} );
		return values;
	};

	/**
	 * Request updates to the changeset.
	 *
	 * @since 4.7.0
	 * @access public
	 *
	 * @param {object}  [changes] - Mapping of setting IDs to setting params each normally including a value property, or mapping to null.
	 *                             If not provided, then the changes will still be obtained from unsaved dirty settings.
	 * @param {object}  [args] - Additional options for the save request.
	 * @param {boolean} [args.autosave=false] - Whether changes will be stored in autosave revision if the changeset has been promoted from an auto-draft.
	 * @param {boolean} [args.force=false] - Send request to update even when there are no changes to submit. This can be used to request the latest status of the changeset on the server.
	 * @param {string}  [args.title] - Title to update in the changeset. Optional.
	 * @param {string}  [args.date] - Date to update in the changeset. Optional.
	 * @returns {jQuery.Promise} Promise resolving with the response data.
	 */
	api.requestChangesetUpdate = function requestChangesetUpdate( changes, args ) {
		var deferred, request, submittedChanges = {}, data, submittedArgs;
		deferred = new $.Deferred();

		// Prevent attempting changeset update while request is being made.
		if ( 0 !== api.state( 'processing' ).get() ) {
			deferred.reject( 'already_processing' );
			return deferred.promise();
		}

		submittedArgs = _.extend( {
			title: null,
			date: null,
			autosave: false,
			force: false
		}, args );

		if ( changes ) {
			_.extend( submittedChanges, changes );
		}

		// Ensure all revised settings (changes pending save) are also included, but not if marked for deletion in changes.
		_.each( api.dirtyValues( { unsaved: true } ), function( dirtyValue, settingId ) {
			if ( ! changes || null !== changes[ settingId ] ) {
				submittedChanges[ settingId ] = _.extend(
					{},
					submittedChanges[ settingId ] || {},
					{ value: dirtyValue }
				);
			}
		} );

		// Allow plugins to attach additional params to the settings.
		api.trigger( 'changeset-save', submittedChanges, submittedArgs );

		// Short-circuit when there are no pending changes.
		if ( ! submittedArgs.force && _.isEmpty( submittedChanges ) && null === submittedArgs.title && null === submittedArgs.date ) {
			deferred.resolve( {} );
			return deferred.promise();
		}

		// A status would cause a revision to be made, and for this wp.customize.previewer.save() should be used. Status is also disallowed for revisions regardless.
		if ( submittedArgs.status ) {
			return deferred.reject( { code: 'illegal_status_in_changeset_update' } ).promise();
		}

		// Dates not beung allowed for revisions are is a technical limitation of post revisions.
		if ( submittedArgs.date && submittedArgs.autosave ) {
			return deferred.reject( { code: 'illegal_autosave_with_date_gmt' } ).promise();
		}

		// Make sure that publishing a changeset waits for all changeset update requests to complete.
		api.state( 'processing' ).set( api.state( 'processing' ).get() + 1 );
		deferred.always( function() {
			api.state( 'processing' ).set( api.state( 'processing' ).get() - 1 );
		} );

		// Ensure that if any plugins add data to save requests by extending query() that they get included here.
		data = api.previewer.query( { excludeCustomizedSaved: true } );
		delete data.customized; // Being sent in customize_changeset_data instead.
		_.extend( data, {
			nonce: api.settings.nonce.save,
			customize_theme: api.settings.theme.stylesheet,
			customize_changeset_data: JSON.stringify( submittedChanges )
		} );
		if ( null !== submittedArgs.title ) {
			data.customize_changeset_title = submittedArgs.title;
		}
		if ( null !== submittedArgs.date ) {
			data.customize_changeset_date = submittedArgs.date;
		}
		if ( false !== submittedArgs.autosave ) {
			data.customize_changeset_autosave = 'true';
		}

		// Allow plugins to modify the params included with the save request.
		api.trigger( 'save-request-params', data );

		request = wp.ajax.post( 'customize_save', data );

		request.done( function requestChangesetUpdateDone( data ) {
			var savedChangesetValues = {};

			// Ensure that all settings updated subsequently will be included in the next changeset update request.
			api._lastSavedRevision = Math.max( api._latestRevision, api._lastSavedRevision );

			api.state( 'changesetStatus' ).set( data.changeset_status );

			if ( data.changeset_date ) {
				api.state( 'changesetDate' ).set( data.changeset_date );
			}

			deferred.resolve( data );
			api.trigger( 'changeset-saved', data );

			if ( data.setting_validities ) {
				_.each( data.setting_validities, function( validity, settingId ) {
					if ( true === validity && _.isObject( submittedChanges[ settingId ] ) && ! _.isUndefined( submittedChanges[ settingId ].value ) ) {
						savedChangesetValues[ settingId ] = submittedChanges[ settingId ].value;
					}
				} );
			}

			api.previewer.send( 'changeset-saved', _.extend( {}, data, { saved_changeset_values: savedChangesetValues } ) );
		} );
		request.fail( function requestChangesetUpdateFail( data ) {
			deferred.reject( data );
			api.trigger( 'changeset-error', data );
		} );
		request.always( function( data ) {
			if ( data.setting_validities ) {
				api._handleSettingValidities( {
					settingValidities: data.setting_validities
				} );
			}
		} );

		return deferred.promise();
	};

	/**
	 * Watch all changes to Value properties, and bubble changes to parent Values instance
	 *
	 * @since 4.1.0
	 *
	 * @param {wp.customize.Class} instance
	 * @param {Array}              properties  The names of the Value instances to watch.
	 */
	api.utils.bubbleChildValueChanges = function ( instance, properties ) {
		$.each( properties, function ( i, key ) {
			instance[ key ].bind( function ( to, from ) {
				if ( instance.parent && to !== from ) {
					instance.parent.trigger( 'change', instance );
				}
			} );
		} );
	};

	/**
	 * Expand a panel, section, or control and focus on the first focusable element.
	 *
	 * @since 4.1.0
	 *
	 * @param {Object}   [params]
	 * @param {Function} [params.completeCallback]
	 */
	focus = function ( params ) {
		var construct, completeCallback, focus, focusElement;
		construct = this;
		params = params || {};
		focus = function () {
			var focusContainer;
			if ( ( construct.extended( api.Panel ) || construct.extended( api.Section ) ) && construct.expanded && construct.expanded() ) {
				focusContainer = construct.contentContainer;
			} else {
				focusContainer = construct.container;
			}

			focusElement = focusContainer.find( '.control-focus:first' );
			if ( 0 === focusElement.length ) {
				// Note that we can't use :focusable due to a jQuery UI issue. See: https://github.com/jquery/jquery-ui/pull/1583
				focusElement = focusContainer.find( 'input, select, textarea, button, object, a[href], [tabindex]' ).filter( ':visible' ).first();
			}
			focusElement.focus();
		};
		if ( params.completeCallback ) {
			completeCallback = params.completeCallback;
			params.completeCallback = function () {
				focus();
				completeCallback();
			};
		} else {
			params.completeCallback = focus;
		}

		api.state( 'paneVisible' ).set( true );
		if ( construct.expand ) {
			construct.expand( params );
		} else {
			params.completeCallback();
		}
	};

	/**
	 * Stable sort for Panels, Sections, and Controls.
	 *
	 * If a.priority() === b.priority(), then sort by their respective params.instanceNumber.
	 *
	 * @since 4.1.0
	 *
	 * @param {(wp.customize.Panel|wp.customize.Section|wp.customize.Control)} a
	 * @param {(wp.customize.Panel|wp.customize.Section|wp.customize.Control)} b
	 * @returns {Number}
	 */
	api.utils.prioritySort = function ( a, b ) {
		if ( a.priority() === b.priority() && typeof a.params.instanceNumber === 'number' && typeof b.params.instanceNumber === 'number' ) {
			return a.params.instanceNumber - b.params.instanceNumber;
		} else {
			return a.priority() - b.priority();
		}
	};

	/**
	 * Return whether the supplied Event object is for a keydown event but not the Enter key.
	 *
	 * @since 4.1.0
	 *
	 * @param {jQuery.Event} event
	 * @returns {boolean}
	 */
	api.utils.isKeydownButNotEnterEvent = function ( event ) {
		return ( 'keydown' === event.type && 13 !== event.which );
	};

	/**
	 * Return whether the two lists of elements are the same and are in the same order.
	 *
	 * @since 4.1.0
	 *
	 * @param {Array|jQuery} listA
	 * @param {Array|jQuery} listB
	 * @returns {boolean}
	 */
	api.utils.areElementListsEqual = function ( listA, listB ) {
		var equal = (
			listA.length === listB.length && // if lists are different lengths, then naturally they are not equal
			-1 === _.indexOf( _.map( // are there any false values in the list returned by map?
				_.zip( listA, listB ), // pair up each element between the two lists
				function ( pair ) {
					return $( pair[0] ).is( pair[1] ); // compare to see if each pair are equal
				}
			), false ) // check for presence of false in map's return value
		);
		return equal;
	};

	/**
	 * Highlight the existence of a button.
	 *
	 * This function reminds the user of a button represented by the specified
	 * UI element, after an optional delay. If the user focuses the element
	 * before the delay passes, the reminder is canceled.
	 *
	 * @since 4.9.0
	 *
	 * @param {jQuery} button - The element to highlight.
	 * @param {object} [options] - Options.
	 * @param {number} [options.delay=0] - Delay in milliseconds.
	 * @param {jQuery} [options.focusTarget] - A target for user focus that defaults to the highlighted element.
	 *                                         If the user focuses the target before the delay passes, the reminder
	 *                                         is canceled. This option exists to accommodate compound buttons
	 *                                         containing auxiliary UI, such as the Publish button augmented with a
	 *                                         Settings button.
	 * @returns {Function} An idempotent function that cancels the reminder.
	 */
	api.utils.highlightButton = function highlightButton( button, options ) {
		var animationClass = 'button-see-me',
			canceled = false,
			params;

		params = _.extend(
			{
				delay: 0,
				focusTarget: button
			},
			options
		);

		function cancelReminder() {
			canceled = true;
		}

		params.focusTarget.on( 'focusin', cancelReminder );
		setTimeout( function() {
			params.focusTarget.off( 'focusin', cancelReminder );

			if ( ! canceled ) {
				button.addClass( animationClass );
				button.one( 'animationend', function() {
					/*
					 * Remove animation class to avoid situations in Customizer where
					 * DOM nodes are moved (re-inserted) and the animation repeats.
					 */
					button.removeClass( animationClass );
				} );
			}
		}, params.delay );

		return cancelReminder;
	};

	/**
	 * Get current timestamp adjusted for server clock time.
	 *
	 * Same functionality as the `current_time( 'mysql', false )` function in PHP.
	 *
	 * @since 4.9.0
	 *
	 * @returns {int} Current timestamp.
	 */
	api.utils.getCurrentTimestamp = function getCurrentTimestamp() {
		var currentDate, currentClientTimestamp, timestampDifferential;
		currentClientTimestamp = _.now();
		currentDate = new Date( api.settings.initialServerDate.replace( /-/g, '/' ) );
		timestampDifferential = currentClientTimestamp - api.settings.initialClientTimestamp;
		timestampDifferential += api.settings.initialClientTimestamp - api.settings.initialServerTimestamp;
		currentDate.setTime( currentDate.getTime() + timestampDifferential );
		return currentDate.getTime();
	};

	/**
	 * Get remaining time of when the date is set.
	 *
	 * @since 4.9.0
	 *
	 * @param {string|int|Date} datetime - Date time or timestamp of the future date.
	 * @return {int} remainingTime - Remaining time in milliseconds.
	 */
	api.utils.getRemainingTime = function getRemainingTime( datetime ) {
		var millisecondsDivider = 1000, remainingTime, timestamp;
		if ( datetime instanceof Date ) {
			timestamp = datetime.getTime();
		} else if ( 'string' === typeof datetime ) {
			timestamp = ( new Date( datetime.replace( /-/g, '/' ) ) ).getTime();
		} else {
			timestamp = datetime;
		}

		remainingTime = timestamp - api.utils.getCurrentTimestamp();
		remainingTime = Math.ceil( remainingTime / millisecondsDivider );
		return remainingTime;
	};

	/**
	 * Return browser supported `transitionend` event name.
	 *
	 * @since 4.7.0
	 *
	 * @returns {string|null} Normalized `transitionend` event name or null if CSS transitions are not supported.
	 */
	normalizedTransitionendEventName = (function () {
		var el, transitions, prop;
		el = document.createElement( 'div' );
		transitions = {
			'transition'      : 'transitionend',
			'OTransition'     : 'oTransitionEnd',
			'MozTransition'   : 'transitionend',
			'WebkitTransition': 'webkitTransitionEnd'
		};
		prop = _.find( _.keys( transitions ), function( prop ) {
			return ! _.isUndefined( el.style[ prop ] );
		} );
		if ( prop ) {
			return transitions[ prop ];
		} else {
			return null;
		}
	})();

	/**
	 * Base class for Panel and Section.
	 *
	 * @since 4.1.0
	 *
	 * @class
	 * @augments wp.customize.Class
	 */
	Container = api.Class.extend({
		defaultActiveArguments: { duration: 'fast', completeCallback: $.noop },
		defaultExpandedArguments: { duration: 'fast', completeCallback: $.noop },
		containerType: 'container',
		defaults: {
			title: '',
			description: '',
			priority: 100,
			type: 'default',
			content: null,
			active: true,
			instanceNumber: null
		},

		/**
		 * @since 4.1.0
		 *
		 * @param {string}         id - The ID for the container.
		 * @param {object}         options - Object containing one property: params.
		 * @param {string}         options.title - Title shown when panel is collapsed and expanded.
		 * @param {string=}        [options.description] - Description shown at the top of the panel.
		 * @param {number=100}     [options.priority] - The sort priority for the panel.
		 * @param {string}         [options.templateId] - Template selector for container.
		 * @param {string=default} [options.type] - The type of the panel. See wp.customize.panelConstructor.
		 * @param {string=}        [options.content] - The markup to be used for the panel container. If empty, a JS template is used.
		 * @param {boolean=true}   [options.active] - Whether the panel is active or not.
		 * @param {object}         [options.params] - Deprecated wrapper for the above properties.
		 */
		initialize: function ( id, options ) {
			var container = this;
			container.id = id;

			if ( ! Container.instanceCounter ) {
				Container.instanceCounter = 0;
			}
			Container.instanceCounter++;

			$.extend( container, {
				params: _.defaults(
					options.params || options, // Passing the params is deprecated.
					container.defaults
				)
			} );
			if ( ! container.params.instanceNumber ) {
				container.params.instanceNumber = Container.instanceCounter;
			}
			container.notifications = new api.Notifications();
			container.templateSelector = container.params.templateId || 'customize-' + container.containerType + '-' + container.params.type;
			container.container = $( container.params.content );
			if ( 0 === container.container.length ) {
				container.container = $( container.getContainer() );
			}
			container.headContainer = container.container;
			container.contentContainer = container.getContent();
			container.container = container.container.add( container.contentContainer );

			container.deferred = {
				embedded: new $.Deferred()
			};
			container.priority = new api.Value();
			container.active = new api.Value();
			container.activeArgumentsQueue = [];
			container.expanded = new api.Value();
			container.expandedArgumentsQueue = [];

			container.active.bind( function ( active ) {
				var args = container.activeArgumentsQueue.shift();
				args = $.extend( {}, container.defaultActiveArguments, args );
				active = ( active && container.isContextuallyActive() );
				container.onChangeActive( active, args );
			});
			container.expanded.bind( function ( expanded ) {
				var args = container.expandedArgumentsQueue.shift();
				args = $.extend( {}, container.defaultExpandedArguments, args );
				container.onChangeExpanded( expanded, args );
			});

			container.deferred.embedded.done( function () {
				container.setupNotifications();
				container.attachEvents();
			});

			api.utils.bubbleChildValueChanges( container, [ 'priority', 'active' ] );

			container.priority.set( container.params.priority );
			container.active.set( container.params.active );
			container.expanded.set( false );
		},

		/**
		 * Get the element that will contain the notifications.
		 *
		 * @since 4.9.0
		 * @returns {jQuery} Notification container element.
		 * @this {wp.customize.Control}
		 */
		getNotificationsContainerElement: function() {
			var container = this;
			return container.contentContainer.find( '.customize-control-notifications-container:first' );
		},

		/**
		 * Set up notifications.
		 *
		 * @since 4.9.0
		 * @returns {void}
		 */
		setupNotifications: function() {
			var container = this, renderNotifications;
			container.notifications.container = container.getNotificationsContainerElement();

			// Render notifications when they change and when the construct is expanded.
			renderNotifications = function() {
				if ( container.expanded.get() ) {
					container.notifications.render();
				}
			};
			container.expanded.bind( renderNotifications );
			renderNotifications();
			container.notifications.bind( 'change', _.debounce( renderNotifications ) );
		},

		/**
		 * @since 4.1.0
		 *
		 * @abstract
		 */
		ready: function() {},

		/**
		 * Get the child models associated with this parent, sorting them by their priority Value.
		 *
		 * @since 4.1.0
		 *
		 * @param {String} parentType
		 * @param {String} childType
		 * @returns {Array}
		 */
		_children: function ( parentType, childType ) {
			var parent = this,
				children = [];
			api[ childType ].each( function ( child ) {
				if ( child[ parentType ].get() === parent.id ) {
					children.push( child );
				}
			} );
			children.sort( api.utils.prioritySort );
			return children;
		},

		/**
		 * To override by subclass, to return whether the container has active children.
		 *
		 * @since 4.1.0
		 *
		 * @abstract
		 */
		isContextuallyActive: function () {
			throw new Error( 'Container.isContextuallyActive() must be overridden in a subclass.' );
		},

		/**
		 * Active state change handler.
		 *
		 * Shows the container if it is active, hides it if not.
		 *
		 * To override by subclass, update the container's UI to reflect the provided active state.
		 *
		 * @since 4.1.0
		 *
		 * @param {boolean}  active - The active state to transiution to.
		 * @param {Object}   [args] - Args.
		 * @param {Object}   [args.duration] - The duration for the slideUp/slideDown animation.
		 * @param {boolean}  [args.unchanged] - Whether the state is already known to not be changed, and so short-circuit with calling completeCallback early.
		 * @param {Function} [args.completeCallback] - Function to call when the slideUp/slideDown has completed.
		 */
		onChangeActive: function( active, args ) {
			var construct = this,
				headContainer = construct.headContainer,
				duration, expandedOtherPanel;

			if ( args.unchanged ) {
				if ( args.completeCallback ) {
					args.completeCallback();
				}
				return;
			}

			duration = ( 'resolved' === api.previewer.deferred.active.state() ? args.duration : 0 );

			if ( construct.extended( api.Panel ) ) {
				// If this is a panel is not currently expanded but another panel is expanded, do not animate.
				api.panel.each(function ( panel ) {
					if ( panel !== construct && panel.expanded() ) {
						expandedOtherPanel = panel;
						duration = 0;
					}
				});

				// Collapse any expanded sections inside of this panel first before deactivating.
				if ( ! active ) {
					_.each( construct.sections(), function( section ) {
						section.collapse( { duration: 0 } );
					} );
				}
			}

			if ( ! $.contains( document, headContainer.get( 0 ) ) ) {
				// If the element is not in the DOM, then jQuery.fn.slideUp() does nothing. In this case, a hard toggle is required instead.
				headContainer.toggle( active );
				if ( args.completeCallback ) {
					args.completeCallback();
				}
			} else if ( active ) {
				headContainer.slideDown( duration, args.completeCallback );
			} else {
				if ( construct.expanded() ) {
					construct.collapse({
						duration: duration,
						completeCallback: function() {
							headContainer.slideUp( duration, args.completeCallback );
						}
					});
				} else {
					headContainer.slideUp( duration, args.completeCallback );
				}
			}
		},

		/**
		 * @since 4.1.0
		 *
		 * @params {Boolean} active
		 * @param {Object}   [params]
		 * @returns {Boolean} false if state already applied
		 */
		_toggleActive: function ( active, params ) {
			var self = this;
			params = params || {};
			if ( ( active && this.active.get() ) || ( ! active && ! this.active.get() ) ) {
				params.unchanged = true;
				self.onChangeActive( self.active.get(), params );
				return false;
			} else {
				params.unchanged = false;
				this.activeArgumentsQueue.push( params );
				this.active.set( active );
				return true;
			}
		},

		/**
		 * @param {Object} [params]
		 * @returns {Boolean} false if already active
		 */
		activate: function ( params ) {
			return this._toggleActive( true, params );
		},

		/**
		 * @param {Object} [params]
		 * @returns {Boolean} false if already inactive
		 */
		deactivate: function ( params ) {
			return this._toggleActive( false, params );
		},

		/**
		 * To override by subclass, update the container's UI to reflect the provided active state.
		 * @abstract
		 */
		onChangeExpanded: function () {
			throw new Error( 'Must override with subclass.' );
		},

		/**
		 * Handle the toggle logic for expand/collapse.
		 *
		 * @param {Boolean}  expanded - The new state to apply.
		 * @param {Object}   [params] - Object containing options for expand/collapse.
		 * @param {Function} [params.completeCallback] - Function to call when expansion/collapse is complete.
		 * @returns {Boolean} false if state already applied or active state is false
		 */
		_toggleExpanded: function( expanded, params ) {
			var instance = this, previousCompleteCallback;
			params = params || {};
			previousCompleteCallback = params.completeCallback;

			// Short-circuit expand() if the instance is not active.
			if ( expanded && ! instance.active() ) {
				return false;
			}

			api.state( 'paneVisible' ).set( true );
			params.completeCallback = function() {
				if ( previousCompleteCallback ) {
					previousCompleteCallback.apply( instance, arguments );
				}
				if ( expanded ) {
					instance.container.trigger( 'expanded' );
				} else {
					instance.container.trigger( 'collapsed' );
				}
			};
			if ( ( expanded && instance.expanded.get() ) || ( ! expanded && ! instance.expanded.get() ) ) {
				params.unchanged = true;
				instance.onChangeExpanded( instance.expanded.get(), params );
				return false;
			} else {
				params.unchanged = false;
				instance.expandedArgumentsQueue.push( params );
				instance.expanded.set( expanded );
				return true;
			}
		},

		/**
		 * @param {Object} [params]
		 * @returns {Boolean} false if already expanded or if inactive.
		 */
		expand: function ( params ) {
			return this._toggleExpanded( true, params );
		},

		/**
		 * @param {Object} [params]
		 * @returns {Boolean} false if already collapsed.
		 */
		collapse: function ( params ) {
			return this._toggleExpanded( false, params );
		},

		/**
		 * Animate container state change if transitions are supported by the browser.
		 *
		 * @since 4.7.0
		 * @private
		 *
		 * @param {function} completeCallback Function to be called after transition is completed.
		 * @returns {void}
		 */
		_animateChangeExpanded: function( completeCallback ) {
			// Return if CSS transitions are not supported.
			if ( ! normalizedTransitionendEventName ) {
				if ( completeCallback ) {
					completeCallback();
				}
				return;
			}

			var construct = this,
				content = construct.contentContainer,
				overlay = content.closest( '.wp-full-overlay' ),
				elements, transitionEndCallback, transitionParentPane;

			// Determine set of elements that are affected by the animation.
			elements = overlay.add( content );

			if ( ! construct.panel || '' === construct.panel() ) {
				transitionParentPane = true;
			} else if ( api.panel( construct.panel() ).contentContainer.hasClass( 'skip-transition' ) ) {
				transitionParentPane = true;
			} else {
				transitionParentPane = false;
			}
			if ( transitionParentPane ) {
				elements = elements.add( '#customize-info, .customize-pane-parent' );
			}

			// Handle `transitionEnd` event.
			transitionEndCallback = function( e ) {
				if ( 2 !== e.eventPhase || ! $( e.target ).is( content ) ) {
					return;
				}
				content.off( normalizedTransitionendEventName, transitionEndCallback );
				elements.removeClass( 'busy' );
				if ( completeCallback ) {
					completeCallback();
				}
			};
			content.on( normalizedTransitionendEventName, transitionEndCallback );
			elements.addClass( 'busy' );

			// Prevent screen flicker when pane has been scrolled before expanding.
			_.defer( function() {
				var container = content.closest( '.wp-full-overlay-sidebar-content' ),
					currentScrollTop = container.scrollTop(),
					previousScrollTop = content.data( 'previous-scrollTop' ) || 0,
					expanded = construct.expanded();

				if ( expanded && 0 < currentScrollTop ) {
					content.css( 'top', currentScrollTop + 'px' );
					content.data( 'previous-scrollTop', currentScrollTop );
				} else if ( ! expanded && 0 < currentScrollTop + previousScrollTop ) {
					content.css( 'top', previousScrollTop - currentScrollTop + 'px' );
					container.scrollTop( previousScrollTop );
				}
			} );
		},

		/**
		 * Bring the container into view and then expand this and bring it into view
		 * @param {Object} [params]
		 */
		focus: focus,

		/**
		 * Return the container html, generated from its JS template, if it exists.
		 *
		 * @since 4.3.0
		 */
		getContainer: function () {
			var template,
				container = this;

			if ( 0 !== $( '#tmpl-' + container.templateSelector ).length ) {
				template = wp.template( container.templateSelector );
			} else {
				template = wp.template( 'customize-' + container.containerType + '-default' );
			}
			if ( template && container.container ) {
				return $.trim( template( _.extend(
					{ id: container.id },
					container.params
				) ) );
			}

			return '<li></li>';
		},

		/**
		 * Find content element which is displayed when the section is expanded.
		 *
		 * After a construct is initialized, the return value will be available via the `contentContainer` property.
		 * By default the element will be related it to the parent container with `aria-owns` and detached.
		 * Custom panels and sections (such as the `NewMenuSection`) that do not have a sliding pane should
		 * just return the content element without needing to add the `aria-owns` element or detach it from
		 * the container. Such non-sliding pane custom sections also need to override the `onChangeExpanded`
		 * method to handle animating the panel/section into and out of view.
		 *
		 * @since 4.7.0
		 * @access public
		 *
		 * @returns {jQuery} Detached content element.
		 */
		getContent: function() {
			var construct = this,
				container = construct.container,
				content = container.find( '.accordion-section-content, .control-panel-content' ).first(),
				contentId = 'sub-' + container.attr( 'id' ),
				ownedElements = contentId,
				alreadyOwnedElements = container.attr( 'aria-owns' );

			if ( alreadyOwnedElements ) {
				ownedElements = ownedElements + ' ' + alreadyOwnedElements;
			}
			container.attr( 'aria-owns', ownedElements );

			return content.detach().attr( {
				'id': contentId,
				'class': 'customize-pane-child ' + content.attr( 'class' ) + ' ' + container.attr( 'class' )
			} );
		}
	});

	/**
	 * @since 4.1.0
	 *
	 * @class
	 * @augments wp.customize.Class
	 */
	api.Section = Container.extend({
		containerType: 'section',
		containerParent: '#customize-theme-controls',
		containerPaneParent: '.customize-pane-parent',
		defaults: {
			title: '',
			description: '',
			priority: 100,
			type: 'default',
			content: null,
			active: true,
			instanceNumber: null,
			panel: null,
			customizeAction: ''
		},

		/**
		 * @since 4.1.0
		 *
		 * @param {string}         id - The ID for the section.
		 * @param {object}         options - Options.
		 * @param {string}         options.title - Title shown when section is collapsed and expanded.
		 * @param {string=}        [options.description] - Description shown at the top of the section.
		 * @param {number=100}     [options.priority] - The sort priority for the section.
		 * @param {string=default} [options.type] - The type of the section. See wp.customize.sectionConstructor.
		 * @param {string=}        [options.content] - The markup to be used for the section container. If empty, a JS template is used.
		 * @param {boolean=true}   [options.active] - Whether the section is active or not.
		 * @param {string}         options.panel - The ID for the panel this section is associated with.
		 * @param {string=}        [options.customizeAction] - Additional context information shown before the section title when expanded.
		 * @param {object}         [options.params] - Deprecated wrapper for the above properties.
		 */
		initialize: function ( id, options ) {
			var section = this, params;
			params = options.params || options;

			// Look up the type if one was not supplied.
			if ( ! params.type ) {
				_.find( api.sectionConstructor, function( Constructor, type ) {
					if ( Constructor === section.constructor ) {
						params.type = type;
						return true;
					}
					return false;
				} );
			}

			Container.prototype.initialize.call( section, id, params );

			section.id = id;
			section.panel = new api.Value();
			section.panel.bind( function ( id ) {
				$( section.headContainer ).toggleClass( 'control-subsection', !! id );
			});
			section.panel.set( section.params.panel || '' );
			api.utils.bubbleChildValueChanges( section, [ 'panel' ] );

			section.embed();
			section.deferred.embedded.done( function () {
				section.ready();
			});
		},

		/**
		 * Embed the container in the DOM when any parent panel is ready.
		 *
		 * @since 4.1.0
		 */
		embed: function () {
			var inject,
				section = this;

			section.containerParent = api.ensure( section.containerParent );

			// Watch for changes to the panel state.
			inject = function ( panelId ) {
				var parentContainer;
				if ( panelId ) {
					// The panel has been supplied, so wait until the panel object is registered.
					api.panel( panelId, function ( panel ) {
						// The panel has been registered, wait for it to become ready/initialized.
						panel.deferred.embedded.done( function () {
							parentContainer = panel.contentContainer;
							if ( ! section.headContainer.parent().is( parentContainer ) ) {
								parentContainer.append( section.headContainer );
							}
							if ( ! section.contentContainer.parent().is( section.headContainer ) ) {
								section.containerParent.append( section.contentContainer );
							}
							section.deferred.embedded.resolve();
						});
					} );
				} else {
					// There is no panel, so embed the section in the root of the customizer
					parentContainer = api.ensure( section.containerPaneParent );
					if ( ! section.headContainer.parent().is( parentContainer ) ) {
						parentContainer.append( section.headContainer );
					}
					if ( ! section.contentContainer.parent().is( section.headContainer ) ) {
						section.containerParent.append( section.contentContainer );
					}
					section.deferred.embedded.resolve();
				}
			};
			section.panel.bind( inject );
			inject( section.panel.get() ); // Since a section may never get a panel, assume that it won't ever get one.
		},

		/**
		 * Add behaviors for the accordion section.
		 *
		 * @since 4.1.0
		 */
		attachEvents: function () {
			var meta, content, section = this;

			if ( section.container.hasClass( 'cannot-expand' ) ) {
				return;
			}

			// Expand/Collapse accordion sections on click.
			section.container.find( '.accordion-section-title, .customize-section-back' ).on( 'click keydown', function( event ) {
				if ( api.utils.isKeydownButNotEnterEvent( event ) ) {
					return;
				}
				event.preventDefault(); // Keep this AFTER the key filter above

				if ( section.expanded() ) {
					section.collapse();
				} else {
					section.expand();
				}
			});

			// This is very similar to