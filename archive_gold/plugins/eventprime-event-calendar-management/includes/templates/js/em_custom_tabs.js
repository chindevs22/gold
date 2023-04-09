"use strict";
var EMCustomTabs = function(options){

    this.emWidth=0;
    this.container= options.container;
    this.activeTabIndex= options.activeTabIndex || 0;
    this.tabPanels = [];
    this.tabHeadsDOMEle = [];

    this.animation = options.animation || 'none';
    this.accentColor = options.accentColor || '#000';
    this.onTabChange = options.onTabChange || null;
    this.init();
}

EMCustomTabs.prototype.getActiveTabIndex= function () {
	   return this.activeTabIndex;
};

EMCustomTabs.prototype.setActiveTabByIndex= function (i) {
        if(this.activeTabIndex != i && this.tabPanels.length > i && i >= 0 && this.tabPanels[i] != '__emt_noop'){
        	jQuery(this.tabHeadsDOMEle[this.activeTabIndex]).removeClass("emActiveTab");
			jQuery(this.tabHeadsDOMEle[i]).addClass("emActiveTab");

			this.switchTabWithAnim(jQuery(this.tabPanels[this.activeTabIndex]),
								   jQuery(this.tabPanels[i]));

			this.activeTabIndex = i;
                        if(typeof this.onTabChange == 'function')
                            this.onTabChange(i);
		}
};

EMCustomTabs.prototype.switchTabWithAnim= function (jqele_to_hide, jqele_to_show) {

	switch(this.animation) {
		case 'fade':
			jqele_to_hide.fadeOut(0);
			jqele_to_show.fadeIn(400);
		break;

		case 'slide':
			jqele_to_hide.hide(400);
			jqele_to_show.show(400);
		break;

		default:
			jqele_to_hide.hide();
			jqele_to_show.show();
		break;
	}
};


EMCustomTabs.prototype.init = function () {
	var emtabs = this;
	var tabContainer = jQuery(this.container);

	if(tabContainer.innerWidth()< 800)
		tabContainer.addClass('emNarrow');
	else
		tabContainer.addClass('emWide');
	
	tabContainer.find(".emtabs_head").each(function(i){
		var thisHead_jqele = jQuery(this);

		thisHead_jqele.addClass('em-menu-tab');

		thisHead_jqele.hover(function () {
	        jQuery(this).css({'border-left-color': emtabs.accentColor, 'border-left-style':'solid' });
                }, function () {
                    jQuery(this).css('border-left-color', 'transparent');
                });

		var tc = thisHead_jqele.data("emt-tabcontent");
		if(typeof tc == "undefined")
			emtabs.tabPanels.push("#emtabpanel_"+i);
		else
			emtabs.tabPanels.push(tc);

		emtabs.tabHeadsDOMEle.push(this);
		jQuery(emtabs.tabPanels[i]).addClass("em-tab-content");
		if(emtabs.activeTabIndex == i) {
			jQuery(emtabs.tabPanels[i]).show();
			thisHead_jqele.addClass('emActiveTab');
		}
		else
			jQuery(emtabs.tabPanels[i]).hide();

		jQuery(this).click(function(e){					
				emtabs.setActiveTabByIndex(i);
		})

	});
        tabContainer.show();
	
};
