// Stores the database ID of
// currently editing slider.
var LS_sliderID = 0,


// Store the indexes of currently
// selected items on the interface.
LS_activeSlideIndex = 0,
LS_activeLayerIndexSet = [0],
LS_activeLayerPageIndex = 0,
LS_activeLayerTransitionTab = 0,
LS_activeScreenType = 'desktop',

LS_lastSelectedLayerIndex = 0,

// Stores all preview items using an object
// to easily add and modify items.
//
// NOTE: it's not a jQuery collection, but a
// collection of jQuery-enabled elements.
LS_previewItems = [],


// Object references, pointing to the currently selected
// slide/layer data. These are not working copies, any
// change made will affect the main data object. This makes
// possible to avoid issues caused by inconsistent data.
LS_activeSlideData = {},
LS_activeLayerDataSet = [],
LS_activeStaticLayersDataSet = [],


// These objects will be filled with the default slide/layer
// properties when needed. They purpose as a caching mechanism
// for bulk slide/layer creation.
LS_defaultSliderData = {},
LS_defaultSlideData = {},
LS_defaultLayerData = {},


// Stores all previous editing sessions
// to cache results and speed up operations.
LS_editorSessions = [],

// Flag for unsaved changes on site.
// We use this to display a warning
// for the user when leaving the page.
LS_editorIsDirty = false,


// Flag for transformed layers due to
// combo box preview, which needs to
// be updated after closing the combo box.
LS_comboBoxIsDirty = false,


// Flag for dragging operations to better
// handle layer selection in a group-select
// scenario.
LS_layerWasDragged = false,


// Stores default UI settings of
// editing sessions.
LS_defaultEditorSession = {
	slideIndex: LS_activeSlideIndex,
	layerIndex: LS_activeLayerIndexSet,
	zoomSlider: 100,
	zoomAutoFit: true
},


// Stores temporary data for all
// copy & pate operations.
LS_clipboard = {},


// Stores the main UI elements
LS_previewZoom = 1,
LS_previewArea,
LS_previewHolder,
LS_previewWrapper,

// Context menu
LS_contextMenuTop = 10,
LS_contextMenuLeft = 10,


LS_transformStyles = [
	'rotation',
	'rotationX',
	'rotationY',
	'scaleX',
	'scaleY',
	'skewX',
	'skewY'
];

var $lasso = jQuery();

// Utility functions to perform
// commonly used tasks.
var LS_Utils = {

	convertDateToUTC: function(date) {
		return new Date(
				date.getUTCFullYear(),
				date.getUTCMonth(),
				date.getUTCDate(),
				date.getUTCHours(),
				date.getUTCMinutes(),
				date.getUTCSeconds()
		);
	},

	dataURItoBlob: function( dataURI, type ) {

		type = type || 'image/png';

		var binary = atob(dataURI.split(',')[1]);
		var array = [];
		for(var i = 0; i < binary.length; i++) {
			array.push(binary.charCodeAt(i));
		}
		return new Blob([new Uint8Array(array)], { type: type });
	},

	moveArrayItem: function(array, from, to) {
		if( to === from ) return;

		var target = array[from];
		var increment = to < from ? -1 : 1;

		for(var k = from; k != to; k += increment){
			array[k] = array[k + increment];
		}
		array[to] = target;
	},


	toAbsoluteURL: function(url) {
		// Handle absolute URLs (with protocol-relative prefix)
		// Example: //domain.com/file.png
		if (url.search(/^\/\//) != -1) {
			return window.location.protocol + url;
		}

		// Handle absolute URLs (with explicit origin)
		// Example: http://domain.com/file.png
		if (url.search(/:\/\//) != -1) {
			return url;
		}

		// Handle absolute URLs (without explicit origin)
		// Example: /file.png
		if (url.search(/^\//) != -1) {
			return window.location.origin + url;
		}

		// Handle relative URLs
		// Example: file.png
		var base = window.location.href.match(/(.*\/)/)[0];
		return base + url;
	},

	// credits: http://phpjs.org/functions/strip_tags/
	stripTags: function(input, allowed) {

		allowed = (((allowed || '') + '')
			.toLowerCase()
			.match(/<[a-z][a-z0-9]*>/g) || [])
			.join('');
		var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
			commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
		return input.replace(commentsAndPhpTags, '')
			.replace(tags, function($0, $1) {
				return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
			});
	},

	// credits: http://phpjs.org/functions/nl2br/
	nl2br: function(str, is_xhtml) {
		var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>'; // Adjust comment to avoid issue on phpjs.org display
		return (str + '')
			.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
	},

	// credits: http://stackoverflow.com/questions/3169786/clear-text-selection-with-javascript
	removeTextSelection: function() {
		var selection = window.getSelection ? window.getSelection() : document.selection ? document.selection : null;
		if(!!selection) selection.empty ? selection.empty() : selection.removeAllRanges();
	},

	// credits: http://locutus.io/php/stripslashes/
	stripslashes: function(str) {
	  return (str + '')
		.replace(/\\(.?)/g, function (s, n1) {
		switch (n1) {
			case '\\':
			  return '\\'
			case '0':
			  return '\u0000'
			case '':
			  return ''
			default:
			  return n1
		  }
		});
	},


	// credits: http://locutus.io/php/parse_url/
	parse_url: function(str, component) {
		var query;

		var mode = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.mode') : undefined) || 'php';

		var key = [
			'source',
			'scheme',
			'authority',
			'userInfo',
			'user',
			'pass',
			'host',
			'port',
			'relative',
			'path',
			'directory',
			'file',
			'query',
			'fragment'
		];

		// For loose we added one optional slash to post-scheme to catch file:/// (should restrict this)
		var parser = {
			php: new RegExp([
				'(?:([^:\\/?#]+):)?',
				'(?:\\/\\/()(?:(?:()(?:([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
				'()',
				'(?:(()(?:(?:[^?#\\/]*\\/)*)()(?:[^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
			].join('')),
			strict: new RegExp([
				'(?:([^:\\/?#]+):)?',
				'(?:\\/\\/((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?))?',
				'((((?:[^?#\\/]*\\/)*)([^?#]*))(?:\\?([^#]*))?(?:#(.*))?)'
			].join('')),
			loose: new RegExp([
				'(?:(?![^:@]+:[^:@\\/]*@)([^:\\/?#.]+):)?',
				'(?:\\/\\/\\/?)?',
				'((?:(([^:@\\/]*):?([^:@\\/]*))?@)?([^:\\/?#]*)(?::(\\d*))?)',
				'(((\\/(?:[^?#](?![^?#\\/]*\\.[^?#\\/.]+(?:[?#]|$)))*\\/?)?([^?#\\/]*))',
				'(?:\\?([^#]*))?(?:#(.*))?)'
			].join(''))
		};

		var m = parser[mode].exec(str);
		var uri = {};
		var i = 14;

		while (i--) {
			if (m[i]) {
				uri[key[i]] = m[i];
			}
		}

		if (component) {
			return uri[component.replace('PHP_URL_', '').toLowerCase()];
		}

		if (mode !== 'php') {
			var name = (typeof require !== 'undefined' ? require('../info/ini_get')('locutus.parse_url.queryKey') : undefined) || 'queryKey';
			parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
			uri[name] = {};
			query = uri[key[12]] || '';
			query.replace(parser, function ($0, $1, $2) {
				if ($1) {
					uri[name][$1] = $2;
				}
			});
		}

		delete uri.source;
		return uri;
	}
};


var LS_GUI = {

	updateImagePicker: function( $picker, image, updateProperties ) {

		updateProperties = updateProperties || {};

		if( typeof $picker === 'string' ) {
			$picker = jQuery('input[name="'+$picker+'"]').next();
		}

		if( image === 'useCurrent' ) {
			image = $picker.find('img').attr('src');
		}

		if( image && image.indexOf('blank.gif') !== -1 ) {
			if( ! updateProperties.fromPost ) {
				image = false;
			}
		}

		$picker
			.removeClass('has-image not-set')
			.addClass( image ? 'has-image' : 'not-set' )
			.find('img').attr('src', image ||  lsTrImgPath+'/blank.gif' );
	},


	updateLinkPicker: function( $input ) {

		if( typeof $input === 'string' ) {
			$input = jQuery('input[name="'+$input+'"]');
		}

		// Do nothing if no input found. Revisions and other pages
		// might load the Slider Builder script without an active
		// editor session in place.
		if( ! $input.length ) { return; }

		var $holder 		= $input.closest('.ls-slide-link'),
			inputName 		= $input.attr('name'),
			inputVal 		= $input.val(),
			isSlide 		= $holder.closest('.ls-slide-options').length,
			dataArea 		= isSlide ? LS_activeSlideData.properties : LS_activeLayerDataSet[0],

			$linkIdInput 	= $holder.find('input[name="linkId"]'),
			$linkNameInput 	= $holder.find('input[name="linkName"]'),
			$linkTypeInput 	= $holder.find('input[name="linkType"]'),


			linkId 			= $linkIdInput.val(),
			linkName 		= $linkNameInput.val(),
			linkType 		= $linkTypeInput.val(),
			l10nKey;

			// Normalize HTML entities
			linkName 		= jQuery('<textarea>').html(linkName).text();


		// Smart Link
		if( linkName && ( ( linkId && '#' === linkId.substr(0, 1) ) || ( inputVal && '#' === inputVal.substr(0, 1) ) ) ) {

			var placeholder = LS_l10n.SBLinkSmartAction.replace( '%s', linkName );

			$holder.addClass('has-link');

			// If linkId is not yet set, copy the input field value
			if( ! linkId.length ) {
				$linkIdInput.val( inputVal );
			}
			$input.val( placeholder ).prop('disabled', true);


		// URL from Dynamic Layer
		} else if( ( linkId && '[post-url]' === linkId ) || ( inputVal && '[post-url]' === inputVal ) ) {
			$holder.addClass('has-link');
			$input.val( LS_l10n.SBLinkPostDynURL ).prop('disabled', true);
			$linkIdInput.val('[post-url]');


		// Specific WP Post/Page
		} else if( linkId && linkName && linkType ) {

			l10nKey = 'SBLinkText'+ucFirst( linkType );

			$holder.addClass('has-link');
			$input.val( LS_l10n[l10nKey].replace('%s', linkName) ).prop('disabled', true);


		// No formatted link
		} else {
			$holder.removeClass('has-link');
			$input.prop('disabled', false);
		}


		// Update data source
		dataArea[ inputName ] 	= $input.val();
		dataArea.linkId 		= $linkIdInput.val();
		dataArea.linkName 		= $linkNameInput.val();
		dataArea.linkType 		= $linkTypeInput.val();

	},

	deeplinkSection: function() {
		var hash 		= document.location.hash.replace('#', ''),
			$target 	= jQuery('[data-deeplink="'+hash+'"]');

		if( $target.length ) {
			$target.click();
		}
	}
};



// Slide specific undo & redo operations.
// Uses callbacks to run any code stored by
// other methods. Supports custom parameters.
var LS_UndoManager = {

	index: -1,
	stack: [],
	limit: 50,

	add: function(cmd, name, updateInfo) {

		// Invalidate items higher on the stack when
		// called from an undo-ed position
		this.stack.splice(this.index + 1, this.stack.length - this.index);
		this.index = this.stack.length - 1;

		// Maintain stack limit
		if(this.stack.length > this.limit) {
			this.stack.shift();
		}

		// Verify 'history' object in slide
		if(!LS_activeSlideData.hasOwnProperty('history')) {
			LS_activeSlideData.history = [];
		}

		// Prepare updateInfo
		this.prepareUpdateInfo( updateInfo );

		// Add item
		this.stack.push({
			cmd: cmd,
			name: name,
			updateInfo: updateInfo
		});

		// Maintain buttons and stack index
		this.index = this.stack.length - 1;
		this.maintainButtons();

		// Mark unsaved changes on page
		LS_editorIsDirty = true;
	},


	// Replace this.stack when switching slides
	// to support slide-specific history.
	update: function() {

		// Verify 'history' object in slide
		if(!LS_activeSlideData.hasOwnProperty('history')) {
			LS_activeSlideData.history = [];
		}

		this.stack = LS_activeSlideData.history;
		this.index = this.stack.length - 1;

		if( LS_activeSlideData.meta && LS_activeSlideData.meta.undoStackIndex ) {
			this.index = LS_activeSlideData.meta.undoStackIndex;
		}

		this.maintainButtons();
	},


	// Merges new changes into the last item in
	// the UndoManager stack.
	merge: function( cmd, name, updateInfo ) {
		var lastItem = this.stack[ this.stack.length - 1 ];
		this.prepareUpdateInfo( updateInfo );
		jQuery.extend(true, lastItem.updateInfo, updateInfo);
	},


	// Empties the current slide's history and reset
	// every UndoManager-related properties
	empty: function() {
		LS_activeSlideData.history = [];

		if( LS_activeSlideData.meta && LS_activeSlideData.meta.undoStackIndex ) {
			LS_activeSlideData.meta.undoStackIndex = -1;
		}

		this.update();
	},


	undo: function() {
		if(this.stack[this.index]) {
			this.execute('undo', this.stack[this.index], this.stack[this.index-1]);
			this.index--;
			this.maintainButtons();
		}
	},


	redo: function() {
		if(this.stack[this.index+1]) {
			this.index++;
			this.execute('redo', this.stack[this.index], this.stack[this.index+1]);
			this.maintainButtons();
		}
	},


	prepareUpdateInfo: function( updateInfo ) {

		if( updateInfo && typeof updateInfo === 'object') {
			jQuery.each(updateInfo, function(key, val) {

				if( typeof val === 'object') {
					LS_UndoManager.prepareUpdateInfo( val );
					return true;
				}

				if( val === null || typeof val === 'undefined') {
					updateInfo[key] = '';
				}
			});
		}
	},

	maintainButtons: function(itemIndex) {

		var undoButton = jQuery('.ls-editor-undo'),
			redoButton = jQuery('.ls-editor-redo');

		LS_activeSlideData.meta.undoStackIndex = this.index;

		if(this.index !== -1) { undoButton.removeClass('disabled'); }
			else { undoButton.addClass('disabled'); }

		if(this.index < this.stack.length-1) { redoButton.removeClass('disabled'); }
			else { redoButton.addClass('disabled'); }
	},

	execute: function(action, item, followingItem) {

		var layerIndexSet = [];

		// Convert object to array to easily
		// handle multi-action steps.
		if( jQuery.type(item.updateInfo) === 'object' ) {
			item.updateInfo = [item.updateInfo];
		}

		// Iterate through actions in step.
		for(var c = 0; c < item.updateInfo.length; c++) {

			this.executeItem(
				item.cmd,
				item.updateInfo[c].itemIndex,
				item.updateInfo[c][action],
				item.updateInfo[c]
			);

			layerIndexSet.push( item.updateInfo[c].itemIndex );
		}

		this.restoreSelection( action, layerIndexSet, followingItem );
	},


	restoreSelection: function(action, layerIndexSet, followingItem) {

		if( followingItem && action === 'undo'  ) {

			var followingIndexSet = [];

			if( jQuery.type(followingItem.updateInfo) === 'object' ) {
				followingItem.updateInfo = [followingItem.updateInfo];
			}

			for(var c = 0; c < followingItem.updateInfo.length; c++) {
				followingIndexSet.push( followingItem.updateInfo[c].itemIndex );
			}
		}

		// Re-select affected layers if the selection has changed
		if( JSON.stringify( followingIndexSet || layerIndexSet) !== JSON.stringify(LS_activeLayerIndexSet)  ) {
			if( LS_activeSlideData.sublayers.length-1 < Math.max.apply(Math, followingIndexSet || layerIndexSet) ) {
				LayerSlider.selectLayer( [ LS_activeSlideData.sublayers.length-1] );
			} else {
				LayerSlider.selectLayer( followingIndexSet || layerIndexSet );
			}
		}
	},


	executeItem: function(command, itemIndex, updateInfo, item) {

		switch(command) {

			case 'slide.general':
				this.updateOptions(LS_activeSlideData.properties, itemIndex, updateInfo, 'slide');
				LayerSlider.updateSlideInterfaceItems();
				LayerSlider.generatePreview();
				break;


			case 'slide.layers':
				if(jQuery.isEmptyObject(updateInfo.data)) {
					LayerSlider.removeLayer(itemIndex, { histroyEvent: true, requireConfirmation: false });
				} else {
					LayerSlider.addLayer(updateInfo.data, itemIndex, { histroyEvent: true });
					LayerSlider.selectLayer( item.selectIndex );
				}
				LS_DataSource.buildLayersList();
				LayerSlider.generatePreview();
				break;


			case 'layer.order':
				LS_Utils.moveArrayItem(LS_activeSlideData.sublayers, updateInfo.from, updateInfo.to);
				LS_DataSource.buildLayersList();
				LayerSlider.generatePreview();
				break;


			case 'layer.general':
				this.updateOptions(LS_activeSlideData.sublayers[itemIndex], itemIndex, updateInfo);
				LayerSlider.updateLayerInterfaceItems(itemIndex);
				LayerSlider.generatePreviewItem(itemIndex);
				LayerSlider.u