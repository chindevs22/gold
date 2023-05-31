const { InspectorControls } = wp.blockEditor;
const { registerBlockType } = wp.blocks;
const { createElement } = wp.element;
const language = window.atcbI18nObj.language;

// preparing a dynamic date in the future for the default values
const today = new Date();
const nextDay = new Date();
nextDay.setDate( today.getDate() + 3 );
const defaultDate =
	nextDay.getFullYear() +
	'-' +
	( '0' + ( nextDay.getMonth() + 1 ) ).slice( -2 ) +
	'-' +
	( '0' + nextDay.getDate() ).slice( -2 );

// attributes to be added to the button within the editor mode
const editorAttr = {
	debug: true,
	blockInteraction: true,
	style: {
		display: 'flex',
	},
};

// defining the default event strings
const defaultEventName = window.atcbI18nObj.default.title;
const defaultEventLocation = window.atcbI18nObj.default.location;
const defaultLanguage = ( function () {
	const supportedLanguages = ['en', 'de', 'nl', 'fr', 'es', 'pt', 'tr', 'zh', 'ar', 'hi', 'pl', 'ro', 'id', 'no', 'fi', 'sv', 'cs', 'ja', 'it', 'ko', 'vi'];
	if ( language != 'en' && language != '' && supportedLanguages.includes(language) ) {
		return ' language="' + language + '"';
	}
	return '';
} )();

// defining a language slug for external websites
const languageSlug = ( function () {
	const supportedLanguages = ['en', 'de'];
	if ( language != 'en' && language != '' && supportedLanguages.includes(language) ) {
		return language + '/';
	}
	return '';
} )();

// defining a custom icon for the block
const iconEl = createElement(
	'svg',
	{
		width: 24,
		height: 24,
		viewBox: '0 0 24 24',
	},
	createElement( 'path', {
		d: 'm14.626 4.6159c0-0.33981 0.33589-0.61587 0.75122-0.61587s0.75122 0.27606 0.75122 0.61587v2.6977c0 0.33981-0.33589 0.61587-0.75122 0.61587s-0.75122-0.27606-0.75122-0.61587zm-0.47524 9.8989c0.2383 0 0.43228 0.19398 0.43228 0.43228 0 0.2383-0.19398 0.43228-0.43228 0.43228l-1.686-0.0052-0.0052 1.6835c0 0.2383-0.19398 0.43228-0.43228 0.43228-0.2383 0-0.43228-0.19398-0.43228-0.43228l0.0052-1.6847-1.6835-0.0065c-0.2383 0-0.43228-0.19398-0.43228-0.43228 0-0.2383 0.19398-0.43228 0.43228-0.43228l1.6847 0.0052 0.0052-1.6835c0-0.2383 0.19398-0.43228 0.43228-0.43228s0.43228 0.19398 0.43228 0.43228l-0.0052 1.686zm-6.2951-9.8989c0-0.33981 0.33597-0.61587 0.7513-0.61587s0.75122 0.27606 0.75122 0.61587v2.6977c0 0.33981-0.33589 0.61587-0.75122 0.61587s-0.75122-0.27606-0.75122-0.61587zm-3.0218 5.2847h14.332v-3.1052c0-0.10415-0.04296-0.19918-0.11199-0.2695-0.06903-0.069034-0.16407-0.11199-0.2695-0.11199h-1.3736c-0.23046 0-0.4166-0.18614-0.4166-0.4166s0.18614-0.4166 0.4166-0.4166h1.3736c0.33461 0 0.63795 0.13671 0.85801 0.35677 0.22006 0.22006 0.35669 0.52332 0.35669 0.85793v11.99c0 0.33461-0.13671 0.63795-0.35677 0.85801-0.22006 0.22006-0.5234 0.35677-0.85801 0.35677h-13.569c-0.33461 0-0.63795-0.13671-0.85801-0.35677-0.22006-0.22134-0.35677-0.52476-0.35677-0.85937v-11.989c0-0.33461 0.13671-0.63795 0.35677-0.85801s0.5234-0.35677 0.85801-0.35677h1.4673c0.23046 0 0.4166 0.18614 0.4166 0.4166s-0.18614 0.4166-0.4166 0.4166h-1.4673c-0.10415 0-0.19918 0.042956-0.2695 0.11199-0.069034 0.069034-0.11199 0.16407-0.11199 0.2695zm14.332 0.83457h-14.332v8.0488c0 0.10415 0.042956 0.19918 0.11199 0.2695 0.069034 0.06903 0.16407 0.11199 0.2695 0.11199h13.569c0.10415 0 0.19918-0.04296 0.2695-0.11199 0.06903-0.06903 0.11199-0.16407 0.11199-0.2695zm-8.5996-4.3212c-0.23046 0-0.4166-0.18614-0.4166-0.4166s0.18614-0.4166 0.4166-0.4166h2.7979c0.23046 0 0.4166 0.18614 0.4166 0.4166s-0.18614 0.4166-0.4166 0.4166z',
		strokeWidth: '.079993',
	} )
);

// global function to parse the input
function parseAttributes( str ) {
	const pattern = /([\w]+)(?:\s?=\s?)?(?:"([^"]+)"|'([^']+)')?/g;
	let match;
	const attributes = {};
	while ( ( match = pattern.exec( str ) ) !== null ) {
		// parsing the attributes, except for the style attribute, which cannot be used and would throw an error
		if (match[ 1 ] !== 'style') {
			if ( match[ 3 ] ) {
				attributes[ match[ 1 ] ] = match[ 3 ];
			} else if ( match[ 2 ] ) {
				attributes[ match[ 1 ] ] = match[ 2 ];
			} else {
				attributes[ match[ 1 ] ] = "true";
			}
		}
	}
	return attributes;
}

// the actual block generation magic (incl. its control elements)
registerBlockType( 'add-to-calendar/button', {
	title: 'Add to Calendar Button',
	icon: iconEl,
	category: 'widgets',
	keywords: [ 'Button', 'Event', 'Link', window.atcbI18nObj.keywords.k1, window.atcbI18nObj.keywords.k2, window.atcbI18nObj.keywords.k3, window.atcbI18nObj.keywords.k4 ],
	description: window.atcbI18nObj.description,
	textdomain: 'add-to-calendar-button',
	attributes: {
		content: {
			type: 'string',
			default: `name="${ defaultEventName }"\nstartDate="${ defaultDate }"\noptions="'Apple','Google','iCal','Outlook.com','Microsoft 365','Yahoo'"\nlocation="${ defaultEventLocation }"${ defaultLanguage }`,
		},
	},
	edit: function ( props ) {
		function updateContent( event ) {
			props.setAttributes( { content: event.target.value } );
		}
		return [
			createElement(
				InspectorControls,
				{},
				createElement(
					'div',
					{
						style: {
							padding: '5px 10px',
							fontWeight: '600',
						},
					},
					window.atcbI18nObj.label + ':'
				),
				createElement(
					'div',
					{
						style: {
							padding: '5px',
						},
					},
					createElement( 'textarea', {
						value: props.attributes.content,
						onChange: updateContent,
						style: {
							width: '100%',
							minHeight: '200px',
						},
					} )
				),
				createElement(
					'div',
					{
						style: {
							padding: '5px 10px',
						},
					},
					createElement(
						'a',
						{
							target: '_blank',
							href: 'https://add-to-calendar-button.com/' + languageSlug + 'configuration',
						},
						window.atcbI18nObj.help + '.'
					)
				),
				createElement(
					'div',
					{
						style: {
							padding: '10px 10px 15px',
							fontWeight: '600',
							fontStyle: 'italic',
						},
					},
					window.atcbI18nObj.note + '!'
				)
			),
			createElement( 'add-to-calendar-button', {
				...editorAttr,
				...parseAttributes( props.attributes.content ),
			} ),
		];
	},
	save: function ( props ) {
		return createElement( 'add-to-calendar-button', {
			...parseAttributes( props.attributes.content ),
		} );
	},
} );
