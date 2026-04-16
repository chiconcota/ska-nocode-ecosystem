import { registerBlockVariation } from '@wordpress/blocks';

// Helper function to extract attributes from our PHP pattern definition or just recreate them here.
// Recreating the Tabs and Accordion structure using InnerBlocks syntax for block variations.

// 1. Molecule: Tabs (Alpine.js)
registerBlockVariation(
	'ska-builder/container',
	{
		name: 'ska-molecule-tabs',
		title: 'Ska Tabs',
		description: 'A pre-configured Tabs container powered by Alpine.js and Tailwind CSS.',
		icon: 'index-card',
		category: 'ska-molecules', // Overrides 'ska-atomic'
		attributes: {
			htmlAttributes: [{ key: 'x-data', value: "{ activeTab: 'tab1' }" }]
		},
		innerBlocks: [
			['ska-builder/container', { tailwindClasses: "flex border-b border-gray-200 bg-gray-50/50" }, [
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100", htmlAttributes: [{ key: '@click', value: "activeTab = 'tab1'" }, { key: ':class', value: "activeTab === 'tab1' ? 'active' : ''" }] }, [
					['ska-builder/text', { content: 'Tab 1', tagName: 'span' }]
				]],
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100", htmlAttributes: [{ key: '@click', value: "activeTab = 'tab2'" }, { key: ':class', value: "activeTab === 'tab2' ? 'active' : ''" }] }, [
					['ska-builder/text', { content: 'Tab 2', tagName: 'span' }]
				]]
			]],
			['ska-builder/container', { tailwindClasses: "p-6" }, [
				['ska-builder/container', { tailwindClasses: "prose text-gray-600", htmlAttributes: [{ key: 'x-show', value: "activeTab === 'tab1'" }, { key: 'x-transition:enter', value: "transition ease-out duration-200" }, { key: 'x-transition:enter-start', value: "opacity-0 translate-y-2" }, { key: 'x-transition:enter-end', value: "opacity-100 translate-y-0" }] }, [
					['ska-builder/text', { content: 'Nội dung của Tab 1. Click vào Tab 2 để đổi.', tagName: 'p' }]
				]],
				['ska-builder/container', { tailwindClasses: "prose text-gray-600", htmlAttributes: [{ key: 'x-show', value: "activeTab === 'tab2'" }, { key: 'x-transition:enter', value: "transition ease-out duration-200" }, { key: 'x-transition:enter-start', value: "opacity-0 translate-y-2" }, { key: 'x-transition:enter-end', value: "opacity-100 translate-y-0" }, { key: 'style', value: "display: none;" }] }, [
					['ska-builder/text', { content: 'Nội dung của Tab 2 đã hiển thị!', tagName: 'p' }]
				]]
			]]
		],
		scope: ['inserter'],
	}
);

// 2. Molecule: Accordion (Alpine.js)
registerBlockVariation(
	'ska-builder/container',
	{
		name: 'ska-molecule-accordion',
		title: 'Ska Accordion',
		description: 'A pre-configured Accordion panel powered by Alpine.js Collapse plugin.',
		icon: 'list-view',
		category: 'ska-molecules', // Overrides 'ska-atomic'
		attributes: {
			tailwindClasses: "w-full overflow-hidden transition-colors border border-gray-100 divide-y divide-gray-100 rounded-xl bg-white shadow-sm"
		},
		innerBlocks: [
			// Item 1
			['ska-builder/container', { tailwindClasses: "group", htmlAttributes: [{ key: 'x-data', value: "{ expanded: false }" }] }, [
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex items-center justify-between w-full p-4 text-left bg-transparent hover:bg-gray-50 focus:outline-none transition-colors", htmlAttributes: [{ key: '@click', value: "expanded = ! expanded" }] }, [
					['ska-builder/text', { content: 'Panel 1 Title', tagName: 'span', tailwindClasses: "font-medium text-gray-900 group-[.expanded]:text-blue-600" }],
					['ska-builder/icon', { iconName: 'keyboard_arrow_down', tailwindClasses: "w-5 h-5 text-gray-500 transition-transform duration-200 material-symbols-outlined", htmlAttributes: [{ key: ':class', value: "expanded ? 'rotate-180 text-blue-600' : ''" }] }]
				]],
				['ska-builder/container', { tailwindClasses: "p-4 text-gray-600 bg-white border-t border-gray-100", htmlAttributes: [{ key: 'x-show', value: "expanded" }, { key: 'x-collapse', value: "" }, { key: 'style', value: "display: none;" }] }, [
					['ska-builder/text', { content: 'Panel 1 details go here. They are hidden by default.', tagName: 'p' }]
				]]
			]],
			// Item 2
			['ska-builder/container', { tailwindClasses: "group", htmlAttributes: [{ key: 'x-data', value: "{ expanded: false }" }] }, [
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex items-center justify-between w-full p-4 text-left bg-transparent hover:bg-gray-50 focus:outline-none transition-colors", htmlAttributes: [{ key: '@click', value: "expanded = ! expanded" }] }, [
					['ska-builder/text', { content: 'Panel 2 Title', tagName: 'span', tailwindClasses: "font-medium text-gray-900 group-[.expanded]:text-blue-600" }],
					['ska-builder/icon', { iconName: 'keyboard_arrow_down', tailwindClasses: "w-5 h-5 text-gray-500 transition-transform duration-200 material-symbols-outlined", htmlAttributes: [{ key: ':class', value: "expanded ? 'rotate-180 text-blue-600' : ''" }] }]
				]],
				['ska-builder/container', { tailwindClasses: "p-4 text-gray-600 bg-white border-t border-gray-100", htmlAttributes: [{ key: 'x-show', value: "expanded" }, { key: 'x-collapse', value: "" }, { key: 'style', value: "display: none;" }] }, [
					['ska-builder/text', { content: 'Panel 2 details go here. Clicking the title toggles visibility via Alpine.js x-collapse plugin.', tagName: 'p' }]
				]]
			]]
		],
		scope: ['inserter'],
	}
);

// 3. Molecule: Multi-Step Quiz (Removed)

// 4. Molecule: Dropdown (Alpine.js)
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-dropdown',
	title: 'Ska Dropdown',
	description: 'A custom dropdown menu powered by Alpine.js.',
	icon: 'arrow-down-alt2',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "relative inline-block text-left",
		htmlAttributes: [
			{ key: 'x-data', value: "{ open: false }" },
			{ key: '@click.outside', value: "open = false" },
			{ key: '@mouseenter', value: "open = true" },
			{ key: '@mouseleave', value: "open = false" }
		]
	},
	innerBlocks: [
		['ska-builder/button', {
			label: 'Options',
			styleType: 'outline',
			htmlAttributes: [{ key: '@click', value: "open = !open" }, { key: ':aria-expanded', value: "open" }],
			tailwindClasses: "inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none"
		}],
		['ska-builder/container', {
			tailwindClasses: "absolute right-0 w-56 mt-2 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50",
			htmlAttributes: [
				{ key: 'x-show', value: "open" },
				{ key: 'x-transition:enter', value: "transition ease-out duration-100" },
				{ key: 'x-transition:enter-start', value: "transform opacity-0 scale-95" },
				{ key: 'x-transition:enter-end', value: "transform opacity-100 scale-100" },
				{ key: 'x-transition:leave', value: "transition ease-in duration-75" },
				{ key: 'x-transition:leave-start', value: "transform opacity-100 scale-100" },
				{ key: 'x-transition:leave-end', value: "transform opacity-0 scale-95" },
				{ key: 'style', value: "display: none;" }
			]
		}, [
				['ska-builder/container', { tailwindClasses: "py-1", htmlAttributes: [{ key: 'role', value: "menu" }] }, [
					['ska-builder/text', { content: 'Account settings', tagName: 'a', tailwindClasses: "block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900", htmlAttributes: [{ key: 'href', value: "#" }, { key: 'role', value: "menuitem" }] }],
					['ska-builder/text', { content: 'Support', tagName: 'a', tailwindClasses: "block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900", htmlAttributes: [{ key: 'href', value: "#" }, { key: 'role', value: "menuitem" }] }],
					['ska-builder/text', { content: 'License', tagName: 'a', tailwindClasses: "block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900", htmlAttributes: [{ key: 'href', value: "#" }, { key: 'role', value: "menuitem" }] }]
				]]
			]]
	],
	scope: ['inserter']
});

// 5. Molecule: Modal (Removed -> Chuyển sang dùng Smart Object Pop-ups ở phase sau)
// 6. Molecule: Carousel (Alpine.js)
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-carousel',
	title: 'Ska Carousel',
	description: 'A sliding image carousel using Alpine.js.',
	icon: 'image-carousel',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "relative w-full overflow-hidden rounded-xl",
		htmlAttributes: [{ key: 'x-data', value: "{ activeSlide: 1, slides: [1, 2, 3] }" }]
	},
	innerBlocks: [
		// Slides container
		['ska-builder/container', { tailwindClasses: "relative h-64 md:h-96" }, [
			// Slide 1
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-500 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 1" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/800x400/blue/white', tailwindClasses: "object-cover w-full h-full" }]
				]],
			// Slide 2
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-500 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 2" }, { key: 'style', value: "display: none;" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/800x400/green/white', tailwindClasses: "object-cover w-full h-full" }]
				]],
			// Slide 3
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-500 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 3" }, { key: 'style', value: "display: none;" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/800x400/red/white', tailwindClasses: "object-cover w-full h-full" }]
				]]
		]],
		// Controls & Dots
		['ska-builder/container', { tailwindClasses: "absolute bottom-4 left-0 right-0 flex justify-center space-x-2" }, [
			['ska-builder/container', { tagName: 'button', tailwindClasses: "w-3 h-3 rounded-full transition-colors", htmlAttributes: [{ key: ':class', value: "activeSlide === 1 ? 'bg-blue-600' : 'bg-gray-300'" }, { key: '@click', value: "activeSlide = 1" }] }, []],
			['ska-builder/container', { tagName: 'button', tailwindClasses: "w-3 h-3 rounded-full transition-colors", htmlAttributes: [{ key: ':class', value: "activeSlide === 2 ? 'bg-blue-600' : 'bg-gray-300'" }, { key: '@click', value: "activeSlide = 2" }] }, []],
			['ska-builder/container', { tagName: 'button', tailwindClasses: "w-3 h-3 rounded-full transition-colors", htmlAttributes: [{ key: ':class', value: "activeSlide === 3 ? 'bg-blue-600' : 'bg-gray-300'" }, { key: '@click', value: "activeSlide = 3" }] }, []]
		]]
	],
	scope: ['inserter']
});

// 7. Molecule: Radio Group
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-radio-group',
	title: 'Ska Radio Group',
	description: 'A styled radio button group.',
	icon: 'forms',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "space-y-4"
	},
	innerBlocks: [
		// Radio Item 1
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { type: 'radio', name: 'my_radio_group', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'Option 1', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Description for option 1', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
			]]
		]],
		// Radio Item 2
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { type: 'radio', name: 'my_radio_group', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'Option 2', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Description for option 2', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
			]]
		]]
	],
	scope: ['inserter']
});

// 8. Molecule: Toggle Switch
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-toggle',
	title: 'Ska Toggle',
	description: 'A toggle switch component.',
	icon: 'yes-alt',
	category: 'ska-molecules',
	attributes: {
		tagName: 'label',
		tailwindClasses: "inline-flex items-center cursor-pointer"
	},
	innerBlocks: [
		['ska-builder/input', { type: 'checkbox', tailwindClasses: "sr-only peer" }], // Screen reader only
		['ska-builder/container', {
			tailwindClasses: "relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
		}, []],
		['ska-builder/text', { content: 'Toggle me', tagName: 'span', tailwindClasses: "ms-3 text-sm font-medium text-gray-900" }]
	],
	scope: ['inserter']
});

// 9. Molecule: Tooltip (Tailwind native group wrapper)
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-tooltip',
	title: 'Ska Tooltip',
	description: 'A hover tooltip component.',
	icon: 'testimonial',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "relative inline-block w-fit group cursor-help",
	},
	innerBlocks: [
		// Trigger Element
		['ska-builder/text', { content: 'Hover over me', tagName: 'span', tailwindClasses: "border-b border-dashed border-gray-400" }],
		// Tooltip Content
		['ska-builder/container', {
			tailwindClasses: "absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-max max-w-xs px-3 py-2 text-sm text-white bg-gray-900 rounded-md shadow-sm opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
		}, [
				['ska-builder/text', { content: 'Tooltip text goes here.', tagName: 'span' }]
			]]
	],
	scope: ['inserter']
});
