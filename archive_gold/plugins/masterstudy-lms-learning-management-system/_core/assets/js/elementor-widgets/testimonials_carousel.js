class StmLmsProTestimonials extends elementorModules.frontend.handlers.Base {

	getDefaultSettings() {
		return {
			selectors: {
				carousel: '.stm-testimonials-carousel-wrapper',
			}
		};
	}

	getDefaultElements() {
		const selectors = this.getSettings('selectors');
		const elementSettings = this.getElementSettings();
		return {
			$sliderContainer: this.$element.find(selectors.carousel),
			$sliderData: {
				'autoplay': elementSettings['autoplay'],
				'loop': elementSettings['loop'],
			},
		};
	}

	bindEvents() {
		jQuery(document).ready( this.sliderInit.bind(this) );
	}

	sliderInit() {
		let _this = this,
			autoplayData = false,
			widgetID = _this.elements.$sliderContainer.closest('.elementor-widget-stm_lms_pro_testimonials').data('id'),
			sliderContainer = document.querySelector(`[data-id="${widgetID}"] .stm-testimonials-carousel-wrapper`),
			bullets = document.querySelector(`[data-id="${widgetID}"] .ms-lms-elementor-testimonials-swiper-pagination`),
			sliderWrapper = document.querySelector(`[data-id="${widgetID}"] .elementor-testimonials-carousel`);
		if ( _this.elements.$sliderData['autoplay'] ) {
			autoplayData = {delay: 2000};
		}
		if ( _this.elements.$sliderContainer.length !== 0 && typeof edit_mode === 'undefined' ) {
			const mySwiper = new Swiper( sliderContainer, {
				slidesPerView: 1,
				allowTouchMove: true,
				loop: _this.elements.$sliderData['loop'],
				autoplay: autoplayData,
				pagination: {
					el: bullets,
					clickable: true,
					renderBullet: function(index, className) {
						let userThumbnail = '',
							testimonialItem = sliderWrapper.children[index];
						if (testimonialItem !== null && typeof testimonialItem === 'object') {
							userThumbnail = testimonialItem.getAttribute('data-thumbnail');
						}
						let span = jQuery('<span></span>');
						span.addClass(className);
						span.css("background-image", "url(" + userThumbnail + ")");
						return span.prop('outerHTML');
					},
				},
			});
		}
	}
}

jQuery(window).on('elementor/frontend/init', () => {
	const addHandler = ($element) => {
		elementorFrontend.elementsHandler.addHandler(StmLmsProTestimonials, {
			$element,
		});
	};
	elementorFrontend.hooks.addAction('frontend/element_ready/stm_lms_pro_testimonials.default', addHandler);
});