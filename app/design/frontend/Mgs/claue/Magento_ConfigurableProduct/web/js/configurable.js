/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'jquery/jquery.parsequery',
	'mgs/owlcarousel',
	'mgs/slick',
	'zoom-images',
    'magnificPopup'
], function ($, _, mageTemplate) {
    'use strict';
    
    $.widget('mage.configurable', {
        options: {
            superSelector: '.super-attribute-select',
            selectSimpleProduct: '[name="selected_configurable_option"]',
            priceHolderSelector: '.price-box',
            spConfig: {},
            state: {},
            priceFormat: {},
            optionTemplate: '<%- data.label %>' +
            "<% if (typeof data.finalPrice.value !== 'undefined') { %>" +
            ' <%- data.finalPrice.formatted %>' +
            '<% } %>',
            mediaGallerySelector: '[data-gallery-role=gallery-placeholder]',
            mediaGalleryInitial: null,
            onlyMainImg: false
        },

        /**
         * Creates widget
         * @private
         */
        _create: function () {
            // Initial setting of various option values
            this._initializeOptions();

            // Override defaults with URL query parameters and/or inputs values
            this._overrideDefaults();

            // Change events to check select reloads
            this._setupChangeEvents();

            // Fill state
            this._fillState();

            // Setup child and prev/next settings
            this._setChildSettings();

            // Setup/configure values to inputs
            this._configureForValues();
        },

        /**
         * Initialize tax configuration, initial settings, and options values.
         * @private
         */
        _initializeOptions: function () {
            var options = this.options,
                gallery = $(options.mediaGallerySelector),
                galleryTemplate = $('#mgs_template_layout').val(),
                priceBoxOptions = $(this.options.priceHolderSelector).priceBox('option').priceConfig || null;

            if (priceBoxOptions && priceBoxOptions.optionTemplate) {
                options.optionTemplate = priceBoxOptions.optionTemplate;
            }

            if (priceBoxOptions && priceBoxOptions.priceFormat) {
                options.priceFormat = priceBoxOptions.priceFormat;
            }
            options.optionTemplate = mageTemplate(options.optionTemplate);

            options.settings = options.spConfig.containerId ?
                $(options.spConfig.containerId).find(options.superSelector) :
                $(options.superSelector);

            options.values = options.spConfig.defaultValues || {};
            options.parentImage = $('[data-role=base-image-container] img').attr('src');

            this.inputSimpleProduct = this.element.find(options.selectSimpleProduct);
            
            var currentImages = [];   
            
            if(galleryTemplate == 0 || galleryTemplate == 4) {
                /* Get Current Image Gallery Bottom Thumb */
                $(".parent__gallery-thumbnail .image-item").each(function( index ) {
                   var item = [];
                   var url = $(this).attr('data-img-change');
                   item['full'] = url;
                   currentImages.push(item);
                });
            }else {
                /* Get Current Image Gallery List & Grid */
                $(".product.media img").each(function( index ) {
                    var item = [];
                   var url = $(this).attr('src');
                   item['full'] = url;
                   currentImages.push(item);
                });
            }
            
            options.mediaGalleryInitial = currentImages;
            
        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function () {
            var hashIndex = window.location.href.indexOf('#');

            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }

            if (this.options.spConfig.inputsInitialized) {
                this._setValuesByAttribute();
            }
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param {*} queryString - URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function (queryString) {
            var queryParams = $.parseQuery({
                query: queryString
            });

            $.each(queryParams, $.proxy(function (key, value) {
                this.options.values[key] = value;
            }, this));
        },

        /**
         * Override default options values with values based on each element's attribute
         * identifier.
         * @private
         */
        _setValuesByAttribute: function () {
            this.options.values = {};
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId;

                if (element.value) {
                    attributeId = element.id.replace(/[a-z]*/, '');
                    this.options.values[attributeId] = element.value;
                }
            }, this));
        },

        /**
         * Set up .on('change') events for each option element to configure the option.
         * @private
         */
        _setupChangeEvents: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                $(element).on('change', this, this._configure);
            }, this));
        },

        /**
         * Iterate through the option settings and set each option's element configuration,
         * attribute identifier. Set the state based on the attribute identifier.
         * @private
         */
        _fillState: function () {
            $.each(this.options.settings, $.proxy(function (index, element) {
                var attributeId = element.id.replace(/[a-z]*/, '');

                if (attributeId && this.options.spConfig.attributes[attributeId]) {
                    element.config = this.options.spConfig.attributes[attributeId];
                    element.attributeId = attributeId;
                    this.options.state[attributeId] = false;
                }
            }, this));
        },

        /**
         * Set each option's child settings, and next/prev option setting. Fill (initialize)
         * an option's list of selections as needed or disable an option's setting.
         * @private
         */
        _setChildSettings: function () {
            var childSettings = [],
                settings = this.options.settings,
                index = settings.length,
                option;

            while (index--) {
                option = settings[index];

                if (index) {
                    option.disabled = true;
                } else {
                    this._fillSelect(option);
                }

                _.extend(option, {
                    childSettings: childSettings.slice(),
                    prevSetting: settings[index - 1],
                    nextSetting: settings[index + 1]
                });

                childSettings.push(option);
            }
        },

        /**
         * Setup for all configurable option settings. Set the value of the option and configure
         * the option, which sets its state, and initializes the option's choices, etc.
         * @private
         */
        _configureForValues: function () {
            if (this.options.values) {
                this.options.settings.each($.proxy(function (index, element) {
                    var attributeId = element.attributeId;
                    element.value = this.options.values[attributeId] || '';
                    this._configureElement(element);
                }, this));
            }
        },

        /**
         * Event handler for configuring an option.
         * @private
         * @param {Object} event - Event triggered to configure an option.
         */
        _configure: function (event) {
            event.data._configureElement(this);
        },

        /**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _configureElement: function (element) {
            this.simpleProduct = this._getSimpleProductId(element);

            if (element.value) {
                this.options.state[element.config.id] = element.value;

                if (element.nextSetting) {
                    element.nextSetting.disabled = false;
                    this._fillSelect(element.nextSetting);
                    this._resetChildren(element.nextSetting);
                } else {
                    if (!!document.documentMode) {
                        this.inputSimpleProduct.val(element.options[element.selectedIndex].config.allowedProducts[0]);
                    } else {
                        this.inputSimpleProduct.val(element.selectedOptions[0].config.allowedProducts[0]);
                    }
                }
            } else {
                this._resetChildren(element);
            }
            this._reloadPrice();
            this._changeProductImage();
        },

        /**
         * Change displayed product image according to chosen options of configurable product
         * @private
         */
        _changeProductImage: function () {
            var images,
                initialImages = this.options.mediaGalleryInitial,
                galleryLayout = $('#mgs_template_layout').val(),
                galleryPopup = $('#galleryPopup').val(),
                img_change = '';
            if (this.options.spConfig.images[this.simpleProduct]) {
                images = $.extend(true, [], this.options.spConfig.images[this.simpleProduct]);
            }

            function updateGallery(imagesArr) {
                var imgToUpdate,
                    mainImg;

                mainImg = imagesArr.filter(function (img) {
                    return img.isMain;
                });

                imgToUpdate = mainImg.length ? mainImg[0] : imagesArr[0];
            }

            function updateSimpleImage(imagesArr) {
                var imgUrl = imagesArr[0]['full'];
                $(".product.media").replaceWith('<div class="product media"><div class="product-image-base"><img src="'+imgUrl+'" class="img-responsive" alt=""/></div></div>');
                setTimeout(function(){ zoomElement(".product-image-base"); }, 500);
            }

            function updateGalleryPopup(imagesArr) {
                var popup_change = '';
                
                $.each(imagesArr, function(index) {
                    popup_change = popup_change + '<a href="'+imagesArr[index]['full']+'" title="">&nbsp;</a>';
                });
                
                $("#popup-gallery").html(popup_change);
            }

            function updateGalleryList(imagesArr) {
                var htmlChange = '<div class="product media product-gallery-list">';
                $.each(imagesArr, function(index) {
                    htmlChange = htmlChange + '<div class="image-item"><img src="'+imagesArr[index]['full']+'" class="img-responsive" alt=""/></div>';
                });
                htmlChange = htmlChange + '</div>';
                
                return htmlChange;
            }

            function updateGalleryVerticalThumbs(imagesArr) {
                var htmlChange = '<div class="product media"><div class="row vertical-thumbnail">';
                            
                htmlChange = htmlChange + '<div class="parent__gallery-thumbnail"><div class="product-gallery-carousel gallery-thumbnail slick-thumbnail">';
                
                $.each(imagesArr, function(index) {
                    if(index == 0){
                        htmlChange = htmlChange + '<div class="item"><div class="image-item active" data-img-change="'+imagesArr[index]['full']+'">';
                    }else{
                        htmlChange = htmlChange + '<div class="item"><div class="image-item" data-img-change="'+imagesArr[index]['full']+'">';
                    }
                    htmlChange = htmlChange + '<img src="'+imagesArr[index]['full']+'" class="img-thumbs img-responsive" alt=""/>';
                    htmlChange = htmlChange + '</div></div>';
                });
                
                htmlChange = htmlChange + '</div></div>';
                
                $.each(imagesArr, function(index) {
                    if(index == 0){
                        htmlChange = htmlChange + '<div class="product-image-base"><img src="'+imagesArr[index]['full']+'" class="img-responsive" alt=""/></div>';
                    }
                });
                
                htmlChange = htmlChange + '</div></div>';
                
                return htmlChange;
            }

            function updateGalleryGrid(imagesArr) {
                var htmlChange = '<div class="product media product-gallery-grid"><div class="row">';
                $.each(imagesArr, function(index) {
                    htmlChange = htmlChange + '<div class="item col-xs-6"><div class="image-item"><img src="'+imagesArr[index]['full']+'" class="img-responsive" alt=""/></div></div>';
                    if(index % 2 == 1){
                        htmlChange = htmlChange + '<div class="clearfix"></div>';
                    }
                });
                htmlChange = htmlChange + '</div></div>';
                
                return htmlChange;
            }

            function updateGalleryQuickview(imagesArr) {
                var htmlChange = '<div class="product media"><div class="product-image-base"><div class="product-galley-image-carousel owl-carousel">';
                $.each(imagesArr, function(index) {
                    htmlChange = htmlChange + '<div class="item"><img class="img-responsive" src="'+imagesArr[index]['full']+'" alt=""/></div>';
                });
                htmlChange = htmlChange + '</div></div></div>';
                
                return htmlChange;
            }

            function updateGalleryBottomThumb(imagesArr) {
                var htmlChange = '<div class="product media">';
                $.each(imagesArr, function(index) {
                    if(index == 0){
                        htmlChange = htmlChange + '<div class="product-image-base"><img src="'+imagesArr[index]['full']+'" class="img-responsive" alt=""/></div>';
                    }
                });
                
                htmlChange = htmlChange + '<div class="parent__gallery-thumbnail"><div class="product-gallery-carousel gallery-thumbnail owl-carousel">';
                
                $.each(imagesArr, function(index) {
                    if(index == 0){
                        htmlChange = htmlChange + '<div class="item"><div class="image-item active" data-img-change="'+imagesArr[index]['full']+'">';
                    }else{
                        htmlChange = htmlChange + '<div class="item"><div class="image-item" data-img-change="'+imagesArr[index]['full']+'">';
                    }
                    htmlChange = htmlChange + '<img src="'+imagesArr[index]['full']+'" class="img-responsive img-thumbs" alt=""/>';
                    htmlChange = htmlChange + '</div></div>';
                });
                
                htmlChange = htmlChange + '</div></div></div>';
                
                return htmlChange;
            }

            if (images) {
                /* Light Box */
                if(galleryPopup == 1){
                    updateGalleryPopup(images);
                }
                /* Update Gallery */
                if (this.options.onlyMainImg) {
                    updateSimpleImage(images);
                } else {
                    if(images.length == 1){
                        updateSimpleImage(images);
                    }else {
                        /* Gallery Bottom Thumb */
                        if(galleryLayout == 0){
                            img_change = updateGalleryBottomThumb(images);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-image-base"); }, 500);
                            $(".product-gallery-carousel").owlCarousel({
								items: 4,
								autoplay: false,
								autoplayHoverPause: false,
								nav: true,
								dots: false,
								navText: ['<i class="pe-7s-angle-left"></i>','<i class="pe-7s-angle-right"></i>'],
								responsive:{
									0:{ items:2 },
									480:{ items:2 },
									768:{ items:3 },
									992:{ items:4 }
								}
							});
                            
                        /* Gallery Grid */
                        }else if(galleryLayout == 1){
                            img_change = updateGalleryGrid(images);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-gallery-grid .image-item"); }, 500);
                            
                        /* Gallery Vertical Thumbs */
                        }else if(galleryLayout == 4){
                            img_change = updateGalleryVerticalThumbs(images);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-image-base"); }, 500);
                            setTimeout(function(){ 
								$('.slick-thumbnail').slick({
									dots: false,
									arrows: true,
									vertical: true,
									slidesToShow: 5,
									slidesToScroll: 5,
									verticalSwiping: true,
									prevArrow: '<span class="pe-7s-angle-up"></span>',
									nextArrow: '<span class="pe-7s-angle-down"></span>'
								});
							}, 1000);
                            
                        /* Gallery Quickview */
                        }else if(galleryLayout == 'quickview'){
                            img_change = updateGalleryQuickview(images);
                            $(".product.media").replaceWith(img_change);
                            $('.product-galley-image-carousel').owlCarousel({
                                items: 1,
                                autoplay: false,
                                autoplayHoverPause: false,
                                nav: true,
                                dots: false,
                                navText: ["<i class='pe-7s-angle-left'></i>","<i class='pe-7s-angle-right'></i>"]
                            });
                            setTimeout(function(){ zoomElement(".product-galley-image-carousel .item"); }, 500);
                        /* Gallery List */
                        }else {
                            img_change = updateGalleryList(images);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-gallery-list .image-item"); }, 500);
                        }
                    }
                }
            } else {
                /* Light Box */
                if(galleryPopup == 1){
                    updateGalleryPopup(initialImages);
                }
                if (this.options.onlyMainImg) {
                    updateSimpleImage(initialImages);
                } else {
                    if(initialImages.length == 1){
                        updateSimpleImage(initialImages);
                    }else {
                        /* Gallery Bottom Thumb */
                        if(galleryLayout == 0){
                            img_change = updateGalleryBottomThumb(initialImages);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-image-base"); }, 500);
                            $(".product-gallery-carousel").owlCarousel({
								items: 4,
								autoplay: false,
								autoplayHoverPause: false,
								nav: true,
								dots: false,
								navText: ['<i class="pe-7s-angle-left"></i>','<i class="pe-7s-angle-right"></i>'],
								responsive:{
									0:{ items:2 },
									480:{ items:2 },
									768:{ items:3 },
									992:{ items:4 }
								}
							});
                            
                        /* Gallery Grid */
                        }else if(galleryLayout == 1){
                            img_change = updateGalleryGrid(initialImages);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-gallery-grid .image-item"); }, 500);
                            
                        /* Gallery Vertical Thumbs */
                        }else if(galleryLayout == 4){
                            img_change = updateGalleryVerticalThumbs(initialImages);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-image-base"); }, 500);
                            setTimeout(function(){ 
								$('.slick-thumbnail').slick({
									dots: false,
									arrows: true,
									vertical: true,
									slidesToShow: 5,
									slidesToScroll: 5,
									verticalSwiping: true,
									prevArrow: '<span class="pe-7s-angle-up"></span>',
									nextArrow: '<span class="pe-7s-angle-down"></span>'
								});
							}, 1000);
                            
                        /* Gallery Quickview */
                        }else if(galleryLayout == 'quickview'){
                            img_change = updateGalleryQuickview(initialImages);
                            $(".product.media").replaceWith(img_change);
                            $('.product-galley-image-carousel').owlCarousel({
                                items: 1,
                                autoplay: false,
                                autoplayHoverPause: false,
                                nav: true,
                                dots: false,
                                navText: ["<i class='pe-7s-angle-left'></i>","<i class='pe-7s-angle-right'></i>"]
                            });
                            setTimeout(function(){ zoomElement(".product-galley-image-carousel .item"); }, 500);
                        /* Gallery List */
                        }else {
                            img_change = updateGalleryList(initialImages);
                            $(".product.media").replaceWith(img_change);
                            setTimeout(function(){ zoomElement(".product-gallery-list .image-item"); }, 500);
                        }
                    }
                }
            }
        },

        /**
         * For a given option element, reset all of its selectable options. Clear any selected
         * index, disable the option choice, and reset the option's state if necessary.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _resetChildren: function (element) {
            if (element.childSettings) {
                _.each(element.childSettings, function (set) {
                    set.selectedIndex = 0;
                    set.disabled = true;
                });

                if (element.config) {
                    this.options.state[element.config.id] = false;
                }
            }
        },

        /**
         * Populates an option's selectable choices.
         * @private
         * @param {*} element - Element associated with a configurable option.
         */
        _fillSelect: function (element) {
            var attributeId = element.id.replace(/[a-z]*/, ''),
                options = this._getAttributeOptions(attributeId),
                prevConfig,
                index = 1,
                allowedProducts,
                i,
                j;

            this._clearSelect(element);
            element.options[0] = new Option('', '');
            element.options[0].innerHTML = this.options.spConfig.chooseText;
            prevConfig = false;

            if (element.prevSetting) {
                prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
            }

            if (options) {
                for (i = 0; i < options.length; i++) {
                    allowedProducts = [];

                    if (prevConfig) {
                        for (j = 0; j < options[i].products.length; j++) {
                            // prevConfig.config can be undefined
                            if (prevConfig.config &&
                                prevConfig.config.allowedProducts &&
                                prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                allowedProducts.push(options[i].products[j]);
                            }
                        }
                    } else {
                        allowedProducts = options[i].products.slice(0);
                    }

                    if (allowedProducts.length > 0) {
                        options[i].allowedProducts = allowedProducts;
                        element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                        if (typeof options[i].price !== 'undefined') {
                            element.options[index].setAttribute('price', options[i].prices);
                        }

                        element.options[index].config = options[i];
                        index++;
                    }
                }
            }
        },

        /**
         * Generate the label associated with a configurable option. This includes the option's
         * label or value and the option's price.
         * @private
         * @param {*} option - A single choice among a group of choices for a configurable option.
         * @return {String} The option label with option value and price (e.g. Black +1.99)
         */
        _getOptionLabel: function (option) {
            return option.label;
        },

        /**
         * Removes an option's selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _clearSelect: function (element) {
            var i;

            for (i = element.options.length - 1; i >= 0; i--) {
                element.remove(i);
            }
        },

        /**
         * Retrieve the attribute options associated with a specific attribute Id.
         * @private
         * @param {Number} attributeId - The id of the attribute whose configurable options are sought.
         * @return {Object} Object containing the attribute options.
         */
        _getAttributeOptions: function (attributeId) {
            if (this.options.spConfig.attributes[attributeId]) {
                return this.options.spConfig.attributes[attributeId].options;
            }
        },

        /**
         * Reload the price of the configurable product incorporating the prices of all of the
         * configurable product's option selections.
         */
        _reloadPrice: function () {
            $(this.options.priceHolderSelector).trigger('updatePrice', this._getPrices());
        },

        /**
         * Get product various prices
         * @returns {{}}
         * @private
         */
        _getPrices: function () {
            var prices = {},
                elements = _.toArray(this.options.settings),
                hasProductPrice = false;

            _.each(elements, function (element) {
                var selected = element.options[element.selectedIndex],
                    config = selected && selected.config,
                    priceValue = {};

                if (config && config.allowedProducts.length === 1 && !hasProductPrice) {
                    priceValue = this._calculatePrice(config);
                    hasProductPrice = true;
                }

                prices[element.attributeId] = priceValue;
            }, this);

            return prices;
        },

        /**
         * Returns pracies for configured products
         *
         * @param {*} config - Products configuration
         * @returns {*}
         * @private
         */
        _calculatePrice: function (config) {
            var displayPrices = $(this.options.priceHolderSelector).priceBox('option').prices,
                newPrices = this.options.spConfig.optionPrices[_.first(config.allowedProducts)];

            _.each(displayPrices, function (price, code) {
                if (newPrices[code]) {
                    displayPrices[code].amount = newPrices[code].amount - displayPrices[code].amount;
                }
            });

            return displayPrices;
        },

        /**
         * Returns Simple product Id
         *  depending on current selected option.
         *
         * @private
         * @param {HTMLElement} element
         * @returns {String|undefined}
         */
        _getSimpleProductId: function (element) {
            // TODO: Rewrite algorithm. It should return ID of
            //        simple product based on selected options.
            var allOptions = element.config.options,
                value = element.value,
                config;

            config = _.filter(allOptions, function (option) {
                return option.id === value;
            });
            config = _.first(config);

            return _.isEmpty(config) ?
                undefined :
                _.first(config.allowedProducts);

        }
        

    });

    return $.mage.configurable;
});
