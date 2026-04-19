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
			['ska-builder/container', { tailwindClasses: "flex border-b border-gray-200 bg-gray-50/50", templateLock: 'all' }, [
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100", htmlAttributes: [{ key: '@click', value: "activeTab = 'tab1'" }, { key: ':class', value: "activeTab === 'tab1' ? 'active' : ''" }] }, [
					['ska-builder/text', { content: 'Tab 1', tagName: 'span' }]
				]],
				['ska-builder/container', { tagName: 'button', tailwindClasses: "flex-1 px-4 py-3 text-sm font-medium text-center transition-colors [&.active]:text-blue-600 [&.active]:bg-white [&.active]:border-b-2 [&.active]:border-blue-600 hover:bg-gray-100", htmlAttributes: [{ key: '@click', value: "activeTab = 'tab2'" }, { key: ':class', value: "activeTab === 'tab2' ? 'active' : ''" }] }, [
					['ska-builder/text', { content: 'Tab 2', tagName: 'span' }]
				]]
			]],
			['ska-builder/container', { tailwindClasses: "p-6", templateLock: false }, [
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

// 3. Molecule: Standard Form (Mẫu chứa đa dạng các trường nhập liệu cơ bản)
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-standard-form',
	title: 'Ska Form Mẫu',
	description: 'Form giao diện mẫu kèm các loại trường nhập liệu: text, ngày, số, multi-select, true/false.',
	icon: 'media-document',
	category: 'ska-molecules',
	attributes: {
		tagName: 'form',
		tailwindClasses: "space-y-8 bg-white p-8 rounded-2xl shadow-xl ring-1 ring-gray-900/5 max-w-2xl mx-auto w-full",
		htmlAttributes: [
			{ key: 'x-data', value: "{ isSubmitting: false, status: '' }" },
			{ key: '@submit.prevent', value: "" }
		]
	},
	innerBlocks: [
		// Header
		['ska-builder/container', { tailwindClasses: "pb-4 border-b border-gray-100" }, [
			['ska-builder/text', { content: 'Đăng ký thông tin', tagName: 'h2', tailwindClasses: "text-2xl font-bold text-gray-900" }],
			['ska-builder/text', { content: 'Vui lòng điền đầy đủ các thông tin bên dưới.', tagName: 'p', tailwindClasses: "mt-1 text-sm text-gray-500" }]
		]],
		// Row 1: Text
		['ska-builder/container', { tailwindClasses: "flex flex-col space-y-1.5" }, [
			['ska-builder/text', { content: 'Họ và tên', tagName: 'label', tailwindClasses: "text-sm font-semibold text-gray-700" }],
			['ska-builder/input', { inputType: 'text', fieldName: 'full_name', placeholder: 'VD: Nguyễn Văn A...', tailwindClasses: "w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-900 outline-none" }]
		]],
		// Row 2: Layout 2 columns (Date + Number)
		['ska-builder/container', { tailwindClasses: "grid grid-cols-1 md:grid-cols-2 gap-6" }, [
			// Col 1: Date
			['ska-builder/container', { tailwindClasses: "flex flex-col space-y-1.5" }, [
				['ska-builder/text', { content: 'Ngày sinh', tagName: 'label', tailwindClasses: "text-sm font-semibold text-gray-700" }],
				['ska-builder/input', { inputType: 'date', fieldName: 'birth_date', tailwindClasses: "w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-900 outline-none" }]
			]],
			// Col 2: Number
			['ska-builder/container', { tailwindClasses: "flex flex-col space-y-1.5" }, [
				['ska-builder/text', { content: 'Độ tuổi', tagName: 'label', tailwindClasses: "text-sm font-semibold text-gray-700" }],
				['ska-builder/input', { inputType: 'number', fieldName: 'age', placeholder: 'Nhập số tuổi...', tailwindClasses: "w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all duration-200 text-gray-900 outline-none" }]
			]]
		]],
		// Row 3: Multi-Select (Using checkbox grid to fix dropdown background styling issue)
		['ska-builder/container', { tailwindClasses: "flex flex-col space-y-3" }, [
			['ska-builder/text', { content: 'Dịch vụ quan tâm', tagName: 'label', tailwindClasses: "text-sm font-semibold text-gray-700" }],
			['ska-builder/select', { isMultiple: true, displayStyle: 'checkbox', fieldName: 'preferences', optionsText: "Dịch vụ A:opt_a\nDịch vụ B:opt_b\nDịch vụ C:opt_c\nDịch vụ D:opt_d", tailwindClasses: "grid grid-cols-1 sm:grid-cols-2 gap-3" }]
		]],
		// Row 4: True/False (Toggle Switch)
		['ska-builder/container', { tagName: 'label', tailwindClasses: "inline-flex items-center cursor-pointer pt-2" }, [
			['ska-builder/input', { inputType: 'checkbox', fieldName: 'agree_terms', tailwindClasses: "sr-only peer" }],
			['ska-builder/container', {
				tailwindClasses: "relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
			}, []],
			['ska-builder/text', { content: 'Tôi đồng ý với các điều khoản và chính sách', tagName: 'span', tailwindClasses: "ms-3 text-sm font-medium text-gray-700 select-none" }]
		]],
		// Row 5: Submit Button
		['ska-builder/container', { tailwindClasses: "pt-6 border-t border-gray-100 flex justify-end" }, [
			['ska-builder/button', { tagName: 'button', text: 'Gửi Thông Tin Ngay', tailwindClasses: "w-full md:w-auto px-8 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98]", htmlAttributes: [{ key: 'type', value: 'submit' }] }]
		]]
	],
	scope: ['inserter']
});

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
	description: 'A beautiful sliding image carousel powered by Alpine.js.',
	icon: 'images-alt2',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "relative w-full overflow-hidden rounded-2xl shadow-xl group",
		htmlAttributes: [{ key: 'x-data', value: "{ activeSlide: 1, slides: [1, 2, 3] }" }]
	},
	innerBlocks: [
		// Slides container
		['ska-builder/container', { tailwindClasses: "relative h-[300px] md:h-[500px]" }, [
			// Slide 1
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-700 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 1" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/1200x800/4F46E5/white?text=Slide+1', tailwindClasses: "object-cover w-full h-full" }],
					['ska-builder/container', { tailwindClasses: "absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent flex flex-col justify-end p-8" }, [
						['ska-builder/text', { content: 'Trải Nghiệm Mượt Mà', tagName: 'h3', tailwindClasses: "text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow-md" }],
						['ska-builder/text', { content: 'Ska Carousel kết hợp sức mạnh của Alpine.js và Tailwind CSS mang đến tốc độ chớp nhoáng.', tagName: 'p', tailwindClasses: "text-gray-100 text-lg max-w-2xl" }]
					]]
				]],
			// Slide 2
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-700 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 2" }, { key: 'style', value: "display: none;" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/1200x800/059669/white?text=Slide+2', tailwindClasses: "object-cover w-full h-full" }],
					['ska-builder/container', { tailwindClasses: "absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent flex flex-col justify-end p-8" }, [
						['ska-builder/text', { content: 'Thiết Kế Đẳng Cấp', tagName: 'h3', tailwindClasses: "text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow-md" }],
						['ska-builder/text', { content: 'Không chỉ là ảnh nội dung được làm nổi bật với mask tuyệt đẹp.', tagName: 'p', tailwindClasses: "text-gray-100 text-lg max-w-2xl" }]
					]]
				]],
			// Slide 3
			['ska-builder/container', {
				tailwindClasses: "absolute inset-0 transition-opacity duration-700 ease-in-out",
				htmlAttributes: [{ key: 'x-show', value: "activeSlide === 3" }, { key: 'style', value: "display: none;" }]
			}, [
					['ska-builder/image', { url: 'https://placehold.co/1200x800/E11D48/white?text=Slide+3', tailwindClasses: "object-cover w-full h-full" }],
					['ska-builder/container', { tailwindClasses: "absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/20 to-transparent flex flex-col justify-end p-8" }, [
						['ska-builder/text', { content: 'Tùy Biến Thả Ga', tagName: 'h3', tailwindClasses: "text-3xl md:text-4xl font-bold text-white mb-2 drop-shadow-md" }],
						['ska-builder/text', { content: 'Hoàn toàn No-code. Chỉ cần thay block là chạy!', tagName: 'p', tailwindClasses: "text-gray-100 text-lg max-w-2xl" }]
					]]
			]]
		]],
		// Arrow Controls
		['ska-builder/container', { tailwindClasses: "absolute inset-0 flex items-center justify-between px-4 md:px-6 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none", templateLock: 'all' }, [
			['ska-builder/container', { tagName: 'button', tailwindClasses: "w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-md text-white hover:bg-white/40 border border-white/30 transition-all pointer-events-auto", htmlAttributes: [{ key: '@click', value: "activeSlide = activeSlide === 1 ? slides.length : activeSlide - 1" }] }, [
				['ska-builder/icon', { iconName: 'arrow_back_ios', tailwindClasses: "w-5 h-5 ml-1.5 material-symbols-outlined" }]
			]],
			['ska-builder/container', { tagName: 'button', tailwindClasses: "w-10 h-10 md:w-12 md:h-12 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-md text-white hover:bg-white/40 border border-white/30 transition-all pointer-events-auto", htmlAttributes: [{ key: '@click', value: "activeSlide = activeSlide === slides.length ? 1 : activeSlide + 1" }] }, [
				['ska-builder/icon', { iconName: 'arrow_forward_ios', tailwindClasses: "w-5 h-5 material-symbols-outlined" }]
			]]
		]],
		// Dots
		['ska-builder/container', { tailwindClasses: "absolute bottom-6 left-0 right-0 flex justify-center space-x-3", templateLock: 'all' }, [
			['ska-builder/button', { tagName: 'button', text: '1', tailwindClasses: "h-2 rounded-full transition-all text-[0px] shadow-sm", htmlAttributes: [{ key: ':class', value: "activeSlide === 1 ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'" }, { key: '@click', value: "activeSlide = 1" }] }],
			['ska-builder/button', { tagName: 'button', text: '2', tailwindClasses: "h-2 rounded-full transition-all text-[0px] shadow-sm", htmlAttributes: [{ key: ':class', value: "activeSlide === 2 ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'" }, { key: '@click', value: "activeSlide = 2" }] }],
			['ska-builder/button', { tagName: 'button', text: '3', tailwindClasses: "h-2 rounded-full transition-all text-[0px] shadow-sm", htmlAttributes: [{ key: ':class', value: "activeSlide === 3 ? 'w-6 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'" }, { key: '@click', value: "activeSlide = 3" }] }]
		]]
	],
	scope: ['inserter']
});

// 7. Molecule: Radio Group
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-radio-group',
	title: 'Ska Radio Group',
	description: 'A styled radio button group.',
	icon: 'yes',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "space-y-4"
	},
	innerBlocks: [
		// Radio Item 1
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { inputType: 'radio', fieldName: 'my_radio_group', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'Tiếng Việt', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Ngôn ngữ hiển thị mặc định.', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
			]]
		]],
		// Radio Item 2
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { inputType: 'radio', fieldName: 'my_radio_group', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'English', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Default display language.', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
			]]
		]]
	],
	scope: ['inserter']
});

// 7.1. Molecule: Checkbox Group
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-checkbox-group',
	title: 'Ska Checkbox Group',
	description: 'A styled checkbox group for multiple selection.',
	icon: 'forms',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "space-y-4"
	},
	innerBlocks: [
		// Checkbox Item 1
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { inputType: 'checkbox', fieldName: 'my_checkbox_group[]', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'Dịch vụ A', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Bao gồm hỗ trợ qua email 24/7.', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
			]]
		]],
		// Checkbox Item 2
		['ska-builder/container', { tagName: 'label', tailwindClasses: "flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:bg-blue-50 has-[:checked]:border-blue-500 transition-colors" }, [
			['ska-builder/input', { inputType: 'checkbox', fieldName: 'my_checkbox_group[]', tailwindClasses: "w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" }],
			['ska-builder/container', { tailwindClasses: "ml-3" }, [
				['ska-builder/text', { content: 'Dịch vụ B', tagName: 'span', tailwindClasses: "block text-sm font-medium text-gray-900" }],
				['ska-builder/text', { content: 'Tùy chọn bổ sung thiết kế cao cấp.', tagName: 'span', tailwindClasses: "block text-sm text-gray-500" }]
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
		['ska-builder/input', { inputType: 'checkbox', fieldName: 'my_toggle', tailwindClasses: "sr-only peer" }], // Screen reader only
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

// 10. Molecule: Offcanvas / Mobile Menu
registerBlockVariation('ska-builder/container', {
	name: 'ska-molecule-offcanvas',
	title: 'Ska Offcanvas',
	description: 'Trượt từ cạnh màn hình. Phù hợp làm Mobile Menu hoặc Filter.',
	icon: 'menu',
	category: 'ska-molecules',
	attributes: {
		tailwindClasses: "relative",
		htmlAttributes: [
			{ key: 'x-data', value: "{ isOffcanvasOpen: false }" },
			{ key: '@keydown.escape.window', value: "isOffcanvasOpen = false" },
			{ key: 'data-skapine-default-preview', value: "true" }
		]
	},
	innerBlocks: [
		// Trigger Button
		['ska-builder/button', {
			tagName: 'button',
			text: 'Mở Offcanvas',
			tailwindClasses: "px-6 py-2.5 bg-gray-900 text-white font-medium rounded-lg shadow-sm hover:bg-gray-800 focus:ring-4 focus:ring-gray-200 transition-all",
			htmlAttributes: [{ key: '@click', value: "isOffcanvasOpen = true" }]
		}],
		
		// Overlay & Panel Container
		['ska-builder/container', { 
			tailwindClasses: "fixed inset-0 z-[100] flex", 
			htmlAttributes: [
				{ key: 'x-show', value: "isOffcanvasOpen" }, 
				{ key: 'style', value: "display: none;" }
			],
			lock: { remove: true, move: true } 
		}, [
			// Backdrop (Overlay)
			['ska-builder/container', {
				tagName: 'div',
				tailwindClasses: "absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity",
				htmlAttributes: [
					{ key: 'x-show', value: "isOffcanvasOpen" },
					{ key: 'x-transition:enter', value: "ease-out duration-300" },
					{ key: 'x-transition:enter-start', value: "opacity-0" },
					{ key: 'x-transition:enter-end', value: "opacity-100" },
					{ key: 'x-transition:leave', value: "ease-in duration-200" },
					{ key: 'x-transition:leave-start', value: "opacity-100" },
					{ key: 'x-transition:leave-end', value: "opacity-0" },
					{ key: '@click', value: "isOffcanvasOpen = false" }
				],
				lock: { remove: true, move: true }
			}],
			
			// Sidebar Panel (Sliding from Left)
			['ska-builder/container', {
				tagName: 'div',
				tailwindClasses: "relative flex w-full max-w-xs flex-col bg-white shadow-2xl h-full",
				htmlAttributes: [
					{ key: 'x-show', value: "isOffcanvasOpen" },
					{ key: 'x-transition:enter', value: "transform transition ease-out duration-300" },
					{ key: 'x-transition:enter-start', value: "-translate-x-full" },
					{ key: 'x-transition:enter-end', value: "translate-x-0" },
					{ key: 'x-transition:leave', value: "transform transition ease-in duration-200" },
					{ key: 'x-transition:leave-start', value: "translate-x-0" },
					{ key: 'x-transition:leave-end', value: "-translate-x-full" }
				],
				lock: { remove: true, move: true }
			}, [
				// Close Button Area (Đặt vào góc trong, z-[9999] để không bao giờ bị đè)
				['ska-builder/container', { tailwindClasses: "absolute top-4 right-4 z-[9999]", lock: { remove: true, move: true } }, [
					['ska-builder/container', { 
						tagName: 'button', 
						tailwindClasses: "flex h-10 w-10 items-center justify-center rounded-full bg-black/5 hover:bg-black/10 text-gray-500 transition-colors focus:outline-none focus:ring-2 focus:ring-black/20 pointer-events-auto", 
						htmlAttributes: [{ key: '@click', value: "isOffcanvasOpen = false" }] 
					}, [
						['ska-builder/icon', { iconName: 'close', tailwindClasses: "w-6 h-6 material-symbols-outlined" }]
					]]
				]],
				// Actual Content Area (Unlocked)
				['ska-builder/container', { tailwindClasses: "p-6 flex-1 overflow-y-auto" }, [
					['ska-builder/text', { content: 'Menu Điều Hướng', tagName: 'h2', tailwindClasses: "text-xl font-bold text-gray-900 mb-2" }],
					['ska-builder/text', { content: 'Sử dụng khung này để thêm danh sách liên kết, form tìm kiếm hoặc bất kỳ nội dung nào bạn muốn.', tagName: 'p', tailwindClasses: "text-sm text-gray-500 mb-6 pb-6 border-b border-gray-100" }],
				]]
			]]
		]]
	],
	scope: ['inserter']
});
