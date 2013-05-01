/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

var Product = {};

Product.Gallery = Class.create();
Product.Gallery.prototype = {
    images: [],
    file2id: {'no_selection':0},
    idIncrement: 1,
    containerId: '',
    container: null,
    uploader: null,
    imageTypes: {},
    initialize: function (containerId, uploader, imageTypes) {
        this.containerId  = containerId,
        this.container = $(this.containerId);
        this.uploader = uploader;
        this.imageTypes = imageTypes;
        this.uploader.onFilesComplete = this.handleUploadComplete.bind(this);
        this.uploader.onFileProgress  = this.handleUploadProgress.bind(this);
        this.uploader.onFileError     = this.handleUploadError.bind(this);
        this.images = this.getElement('save').value.evalJSON();
        this.imagesValues = this.getElement('save_image').value.evalJSON();
        this.template = new Template('<tr id="__id__" class="preview">' + this.getElement('template').innerHTML + '</tr>', /(^|.|\r|\n)(__([a-zA-Z0-9_]+)__)/);
        this.fixParentTable();
        this.updateImages();
        varienGlobalEvents.attachEventHandler('moveTab', this.onImageTabMove.bind(this));
    },
    onImageTabMove: function(event) {
        var imagesTab = false;
        this.container.ancestors().each(function(parentItem){
            if (parentItem.tabObject) {
                imagesTab = parentItem.tabObject;
                throw $break;
            }
        }.bind(this));
        if(imagesTab && event.tab && event.tab.name && imagesTab.name == event.tab.name) {
            this.updateImages();
        }
    },
    fixParentTable: function() {
        this.container.ancestors().each(function(parentItem){
            if(parentItem.tagName.toLowerCase()=='td') {
                parentItem.style.width ='100%';
            }
            if(parentItem.tagName.toLowerCase()=='table') {
                parentItem.style.width ='100%';
                throw $break;
            }
        });
    },
    getElement:           function (name) {
        return $(this.containerId + '_' + name);
    },
    showUploader:         function () {
        this.getElement('add_images_button').hide();
        this.getElement('uploader').show();
    },
    handleUploadComplete: function (files) {
        files.each(function(item) {
           if (!item.response.isJSON()) {
               if (console) {
                   console.log(item.response);
               }
               return;
           }
           var response = item.response.evalJSON();
           if (response.error) {
               return;
           }
           var newImage = {};
           newImage.url = response.url;
           newImage.file = response.file;
           newImage.label = '';
           newImage.position = this.getNextPosition();
           newImage.disabled = 0;
           newImage.removed = 0;
           this.images.push(newImage);
           this.uploader.removeFile(item.id);
        }.bind(this));
        this.updateImages();
    },
    updateImages: function() {
        this.getElement('save').value = this.images.toJSON();
        $H(this.imageTypes).each(function(pair) {
           this.getFileElement('no_selection', 'cell-' + pair.key + ' input').checked = true;
        }.bind(this));
        this.images.each(function(row){
            if (!$(this.prepareId(row.file))) {
                this.createImageRow(row);
            }
            this.updateVisualisation(row.file);
        }.bind(this));
        this.updateUseDefault();
    },
    createImageRow: function(image) {
        var vars = Object.clone(image);
        vars.id = this.prepareId(image.file);
        var html = this.template.evaluate(vars);
        new Insertion.Bottom(this.getElement('list'), html);
    },
    prepareId: function(file) {
        if(typeof this.file2id[file] == 'undefined') {
            this.file2id[file] = this.idIncrement++;
        }
        return this.containerId + '-image-' + this.file2id[file];
    },
    getNextPosition: function() {
      var maxPosition = 0;
      this.images.each(function (item) {
         if (parseInt(item.position) > maxPosition) {
             maxPosition = parseInt(item.position);
         }
      });
      return maxPosition + 1;
    },
    updateImage: function(file) {
      var index = this.getIndexByFile(file);
      this.images[index].label = this.getFileElement(file, 'cell-label input').value;
      this.images[index].position = this.getFileElement(file, 'cell-position input').value;
      this.images[index].removed = (this.getFileElement(file, 'cell-remove input').checked ? 1 : 0);
      this.images[index].disabled = (this.getFileElement(file, 'cell-disable input').checked ? 1 : 0);
      this.getElement('save').value = this.images.toJSON();
      this.updateState(file);
    },
    loadImage: function(file) {
      var image = this.getImageByFile(file);
      this.getFileElement(file, 'cell-image img').src = image.url;
      this.getFileElement(file, 'cell-image img').show();
      this.getFileElement(file, 'cell-image .place-holder').hide();
    },
    setProductImages: function(file) {
      $H(this.imageTypes).each(function(pair){
          if(this.getFileElement(file, 'cell-' + pair.key + ' input').checked) {
              this.imagesValues[pair.key] = (file == 'no_selection' ? null : file);
          }
      }.bind(this));

      this.getElement('save_image').value = $H(this.imagesValues).toJSON();
    },
    updateVisualisation: function(file) {
      var image = this.getImageByFile(file);
      this.getFileElement(file, 'cell-label input').value = image.label;
      this.getFileElement(file, 'cell-position input').value = image.position;
      this.getFileElement(file, 'cell-remove input').checked = (image.removed == 1);
      this.getFileElement(file, 'cell-disable input').checked = (image.disabled == 1);
      $H(this.imageTypes).each(function(pair) {
          if(this.imagesValues[pair.key] == file) {
              this.getFileElement(file, 'cell-' + pair.key + ' input').checked = true;
          }
      }.bind(this));
      this.updateState(file);
    },
    updateState: function (file) {
      if(this.getFileElement(file, 'cell-disable input').checked) {
          this.getFileElement(file, 'cell-position input').disabled = true;
      } else {
          this.getFileElement(file, 'cell-position input').disabled = false;
      }
    },
    getFileElement: function(file, element){
        var selector = '#' + this.prepareId(file) + ' .' + element;
        var elems = $$(selector);
        if(!elems[0]) {
            if (console) {
                console.log(selector);
            }
        }

       return $$('#' + this.prepareId(file) + ' .' + element)[0];
    },
    getImageByFile: function(file) {
      if (this.getIndexByFile(file)===null) {
          return false;
      }

      return this.images[this.getIndexByFile(file)];
    },
    getIndexByFile: function(file) {
          var index;
          this.images.each(function (item, i) {
             if (item.file == file) {
                 index = i;
             }
          });
          return index;
    },
    updateUseDefault: function ()
    {
      if (this.getElement('default')) {
         this.getElement('default').getElementsBySelector('input').each(function(input){
             $(this.containerId).getElementsBySelector('.cell-' + input.value + ' input').each(function(radio) {
                 radio.disabled = input.checked;
             });
         }.bind(this));
      }
    },
    handleUploadProgress: function (file) {

    },
    handleUploadError:    function (fileId) {

    }
};

Product.AttributesBridge = {
    tabsObject: false,
    bindTabs2Attributes: {},
    bind: function(tabId, attributesObject) {
        this.bindTabs2Attributes[tabId] = attributesObject;
    },
    getAttributes: function(tabId) {
        return this.bindTabs2Attributes[tabId];
    },
    setTabsObject: function(tabs) {
        this.tabsObject = tabs;
    },
    getTabsObject: function() {
        return this.tabsObject;
    },
    addAttributeRow: function(data)
    {
        $H(data).each(function(item){
            if(this.getTabsObject().activeTab.name!=item.key) {
                this.getTabsObject().showTabContent($(item.key));
            }
            this.getAttributes(item.key).addRow(item.value);
        }.bind(this));
    }
};

Product.Attributes = Class.create();
Product.Attributes.prototype = {
    config: {},
    containerId: null,
    initialize: function(containerId) {
        this.containerId = containerId;
    },
    setConfig: function(config) {
        this.config = config;
        Product.AttributesBridge.bind(this.getConfig().tab_id, this);
    },
    getConfig: function () {
        return this.config;
    },
    create: function () {
        var win = window.open(this.getConfig().url, 'new_attribute', 'width=900,height=600,resizable=1,scrollbars=1');
        win.focus();
    },
    addRow: function(html) {
        var attributesContainer = $$('#group_fields' + this.getConfig().group_id + ' .form-list tbody')[0];
        new Insertion.Bottom(attributesContainer, html);

        var childs = attributesContainer.immediateDescendants();
        var element = childs[childs.size()-1].getElementsBySelector('input','select','textarea')[0];
        if (element) {
            window.scrollTo(0, Position.cumulativeOffset(element)[1] + element.offsetHeight);
        }
    }
};

Product.Configurable = Class.create();
Product.Configurable.prototype = {
	initialize: function (attributes, links, idPrefix, grid) {
		this.templatesSyntax = /(^|.|\r|\n)('{{\s*(\w+)\s*}}')/;
	    this.attributes = attributes; // Attributes
		this.idPrefix   = idPrefix;   // Container id prefix
		this.links 		= $H(links);  // Associated products
		this.newProducts = [];        // For product that's created througth Create Empty and Copy from Configurable

		/* Generation templates */
		this.addAttributeTemplate     = new Template($(idPrefix + 'attribute_template').innerHTML.replace(/__id__/g,"'{{html_id}}'").replace(/ template no-display/g,''), this.templatesSyntax);
		this.addValueTemplate         = new Template($(idPrefix + 'value_template').innerHTML.replace(/__id__/g,"'{{html_id}}'").replace(/ template no-display/g,''), this.templatesSyntax);
		this.pricingValueTemplate     = new Template($(idPrefix + 'simple_pricing').innerHTML, this.templatesSyntax);
		this.pricingValueViewTemplate = new Template($(idPrefix + 'simple_pricing_view').innerHTML, this.templatesSyntax);

		this.container            = $(idPrefix + 'attributes');

		/* Listeners */
		this.onLabelUpdate        = this.updateLabel.bindAsEventListener(this);       // Update attribute label
		this.onValuePriceUpdate   = this.updateValuePrice.bindAsEventListener(this);  // Update pricing value
		this.onValueTypeUpdate    = this.updateValueType.bindAsEventListener(this);   // Update pricing type

		/* Grid initialization and attributes initialization */
		this.createAttributes(); // Creation of default attributes

		this.grid = grid;
		this.grid.rowClickCallback        = this.rowClick.bind(this);
		this.grid.initRowCallback         = this.rowInit.bind(this);
		this.grid.checkboxCheckCallback   = this.registerProduct.bind(this); // Associate/Unassociate simple product

		this.grid.rows.each(function(row) {
			this.rowInit(this.grid, row);
		}.bind(this));
	},
	createAttributes: function() {
	    this.attributes.each(function(attribute, index) {
	        //var li = Builder.node('li', {className:'attribute'});
	        var li = $(document.createElement('LI'));
	        li.className = 'attribute';
			li.id = this.idPrefix + '_attribute_' + index;
			attribute.html_id = li.id;
			if(attribute && attribute.label && attribute.label.blank()) {
				attribute.label = '&nbsp;'
			}
			li.update(this.addAttributeTemplate.evaluate(attribute));
			li.attributeObject = attribute;

			this.container.appendChild(li);
			li.attributeValues = li.down('.attribute-values');

			if (attribute.values) {
    			attribute.values.each(function(value){
    				this.createValueRow(li, value); // Add pricing values
    			}.bind(this));
			}

			/* Observe label change */
			Event.observe(li.down('.attribute-label'),'change', this.onLabelUpdate);
			Event.observe(li.down('.attribute-label'),'keyup',  this.onLabelUpdate);
		}.bind(this));
		// Creation of sortable for attributes sorting
		Sortable.create(this.container, {handle:'attribute-name-container',onUpdate:this.updatePositions.bind(this)});
		this.updateSaveInput();
	},

	updateLabel: function (event) {
		var li = Event.findElement(event, 'LI');
		li.attributeObject.label = Event.element(event).value;
		this.updateSaveInput();
	},
	updatePositions: function(param) {
		this.container.immediateDescendants().each(function(row, index) {
			row.attributeObject.position = index;
		});
		this.updateSaveInput();
	},
	addNewProduct: function(productId, attributes) {
	    if (this.checkAttributes(attributes)) {
	        this.links[productId] = attributes;
	    } else {
	        this.newProducts.push(productId);
	    }

        this.updateGrid();
	    this.updateValues();
	    this.grid.reload(null);
	},
	createEmptyProduct: function() {
	    var win = window.open(this.createEmptyUrl, 'new_product', 'width=900,height=600,resizable=1,scrollbars=1');
	    win.focus();
	},
	createNewProduct: function() {
	    var win = window.open(this.createNormalUrl, 'new_product', 'width=900,height=600,resizable=1,scrollbars=1');
	    win.focus();
	},
	registerProduct: function(grid, element, checked) {
		if(checked){
            if(element.linkAttributes) {
            	this.links[element.value]=element.linkAttributes;
            }
        }
        else{
            this.links.remove(element.value);
        }
        this.updateGrid();
        this.grid.rows.each(function(row) {
			this.revalidateRow(this.grid, row);
		}.bind(this));
		this.updateValues();
	},
	updateProduct: function(productId, attributes) {
	    var isAssociated = false;

	    if (typeof this.links[productId] != 'undefined') {
	        isAssociated = true;
	        this.links.remove(productId);
	    }

	    if (isAssociated && this.checkAttributes(attributes)) {
	        this.links[productId] = attributes;
	    } else if (isAssociated) {
	        this.newProducts.push(productId);
	    }

	    this.updateGrid();
	    this.updateValues();
	    this.grid.reload(null);
	},
	rowClick: function(grid, event) {
		var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName.toUpperCase() == 'INPUT';

        if ($(Event.findElement(event, 'td')).down('a')) {
            return;
        }

        if(trElement){
            var checkbox = $(trElement).down('input');
            if(checkbox && !checkbox.disabled){
                var checked = isInput ? checkbox.checked : !checkbox.checked;
                grid.setCheckboxChecked(checkbox, checked);
            }
        }
	},
	rowInit: 		 function(grid, row) {
		var checkbox = $(row).down('.checkbox');
		var input = $(row).down('.value-json');

		if(checkbox && input) {
			checkbox.linkAttributes = input.value.evalJSON();
			if(!checkbox.checked) {
				if(!this.checkAttributes(checkbox.linkAttributes)) {
					$(row).addClassName('invalid');
					checkbox.disable();
				} else {
					$(row).removeClassName('invalid');
					checkbox.enable();
				}
			}
		}
	},
	revalidateRow: function(grid, row) {
		var checkbox = $(row).down('.checkbox');
		if(checkbox ) {
			if(!checkbox.checked) {
				if(!this.checkAttributes(checkbox.linkAttributes)) {
					$(row).addClassName('invalid');
					checkbox.disable();
				} else {
					$(row).removeClassName('invalid');
					checkbox.enable();
				}
			}
		}
	},
	checkAttributes:	function(attributes) {
		var result = true;
		this.links.each(function(pair) {
			var fail = false;
			for(var i = 0; i < pair.value.length && !fail; i++) {
				for(var j = 0; j < attributes.length && !fail; j ++) {
					if(pair.value[i].attribute_id == attributes[j].attribute_id && pair.value[i].value_index != attributes[j].value_index) {
						fail = true;
					}
				}
			}
			if(!fail) {
				result = false;
			}
		});
		return result;
	},
	updateGrid: function () {
		this.grid.reloadParams = {'products[]':this.links.keys(), 'new_products[]':this.newProducts};
	},
	updateValues: function () {
		var uniqueAttributeValues = $H({});
		/* Collect unique attributes */
		this.links.each(function(pair) {
			for (var i = 0, length=pair.value.length; i < length; i ++) {
				var attribute = pair.value[i];
				if(uniqueAttributeValues.keys().indexOf(attribute.attribute_id)==-1) {
					uniqueAttributeValues[attribute.attribute_id] = $H({});
				}
				uniqueAttributeValues[attribute.attribute_id][attribute.value_index] = attribute;
			}
		});
		/* Updating attributes value container */
		this.container.immediateDescendants().each(function(row) {
			var attribute = row.attributeObject;
			for(var i = 0, length=attribute.values.length; i < length; i ++) {
				if(uniqueAttributeValues.keys().indexOf(attribute.attribute_id)==-1
					|| uniqueAttributeValues[attribute.attribute_id].keys().indexOf(attribute.values[i].value_index)==-1) {
					row.attributeValues.immediateDescendants().each(function(elem){
						if(elem.valueObject.value_index==attribute.values[i].value_index) {
							elem.remove();
						}
					});
					attribute.values[i] = undefined;

				} else {
					uniqueAttributeValues[attribute.attribute_id].remove(attribute.values[i].value_index);
				}
			}
			attribute.values = attribute.values.compact();
			if(uniqueAttributeValues[attribute.attribute_id]) {
				uniqueAttributeValues[attribute.attribute_id].each(function(pair){
					attribute.values.push(pair.value);
					this.createValueRow(row,pair.value);
				}.bind(this));
			}
		}.bind(this));
		this.updateSaveInput();
		this.updateSimpleForm();
	},
	createValueRow: function(container, value) {

		var templateVariables = $H({});
		if(!this.valueAutoIndex) {
			this.valueAutoIndex = 1;
		}
		templateVariables.html_id = container.id  + '_' + this.valueAutoIndex;
		templateVariables.merge(value);
		if (!isNaN(parseFloat(templateVariables.pricing_value))) {
		    templateVariables.pricing_value = parseFloat(templateVariables.pricing_value);
		} else  {
		    templateVariables.pricing_value = undefined;
		}
		this.valueAutoIndex++;

		//var li = $(Builder.node('li', {className:'attribute-value'}));
		var li = $(document.createElement('LI'));
		li.className = 'attribute-value';
		li.id = templateVariables.html_id;
		li.update(this.addValueTemplate.evaluate(templateVariables));
		li.valueObject = value;
		if (typeof li.valueObject.is_percent == 'undefined') {
			li.valueObject.is_percent = 0;
		}

		if (typeof li.valueObject.pricing_value == 'undefined') {
			li.valueObject.pricing_value = '';
		}

		container.attributeValues.appendChild(li);
		var priceField = li.down('.attribute-price');
		var priceTypeField = li.down('.attribute-price-type');

		if(parseInt(value.is_percent)) {
			priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = false);
		} else {
			priceTypeField.options[1].selected = !(priceTypeField.options[0].selected = true);
		}
		Event.observe(priceField, 'keyup', this.onValuePriceUpdate);
		Event.observe(priceField, 'change', this.onValuePriceUpdate);
		Event.observe(priceTypeField, 'change', this.onValueTypeUpdate);
	},
	updateValuePrice: function(event) {
		var li = Event.findElement(event, 'LI');
		li.valueObject.pricing_value = (Event.element(event).value.blank() ? null : Event.element(event).value);
		this.updateSimpleForm();
		this.updateSaveInput();
	},
	updateValueType:  function(event) {
		var li = Event.findElement(event, 'LI');
		li.valueObject.is_percent = (Event.element(event).value.blank() ? null : Event.element(event).value);
		this.updateSimpleForm();
		this.updateSaveInput();
	},
	updateSaveInput: function() {
		$(this.idPrefix + 'save_attributes').value = this.attributes.toJSON();
		$(this.idPrefix + 'save_links').value  = this.links.toJSON();
	},
	initializeAdvicesForSimpleForm: function() {
	    if ($(this.idPrefix + 'simple_form').advicesInited) {
	        return;
	    }

	    $(this.idPrefix + 'simple_form').getElementsBySelector('td.value').each(function (td) {
            var adviceContainer = $(Builder.node('div'));
            td.appendChild(adviceContainer);
	        td.getElementsBySelector('input', 'select').each(function(element){
	            element.advaiceContainer = adviceContainer;
	        });
	    });
	    $(this.idPrefix + 'simple_form').advicesInited = true;
	},
	quickCreateNewProduct: function() {
        this.initializeAdvicesForSimpleForm();
	    $(this.idPrefix + 'simple_form').removeClassName('ignore-validate');
	    var validationResult = $(this.idPrefix + 'simple_form').getElementsBySelector('input','select','textarea').collect(
	       function(elm) {
	            return Validation.validate(elm,{useTitle : false, onElementValidate : function(){}});
	       }
	    ).all();
	    $(this.idPrefix + 'simple_form').addClassName('ignore-validate');

	    if (!validationResult) {
	        return;
	    }

	    var params = Form.serializeElements(
	       $(this.idPrefix + 'simple_form').getElementsBySelector('input','select','textarea'),
	       true
	    );
        $('messages').update();
	    new Ajax.Request(this.createQuickUrl, {
	           parameters: params,
	           method:'post',
	           area: $(this.idPrefix + 'simple_form'),
	           onComplete: this.quickCreateNewProductComplete.bind(this)
	    });
	},
	quickCreateNewProductComplete: function (transport) {
	    var result = transport.responseText.evalJSON();

	    if (result.error) {
	        if (result.error.fields) {
	            $(this.idPrefix + 'simple_form').removeClassName('ignore-validate');
	            $H(result.error.fields).each(function(pair){
	                $('simple_product_' + pair.key).value = pair.value;
	                $('simple_product_' + pair.key + '_autogenerate').checked = false;
	                toggleValueElements(
	                   $('simple_product_' + pair.key + '_autogenerate'),
	                   $('simple_product_' + pair.key + '_autogenerate').parentNode
	                );
	                Validation.ajaxError($('simple_product_' + pair.key), result.error.message);
	            });
	            $(this.idPrefix + 'simple_form').addClassName('ignore-validate');
	        } else {
	            if (result.error.message) {
	                alert(result.error.message);
	            }
	            else {
	                alert(result.error);
	            }
	        }
	        return;
	    } else if(result.messages) {
	        $('messages').update(result.messages);
	    }


	    result.attributes.each(function(attribute) {
	        var attr = this.getAttributeById(attribute.attribute_id);
	        if (!this.getValueByIndex(attr, attribute.value_index)
	            && result.pricing
	            && result.pricing[attr.attribute_code]) {

	            attribute.is_percent    = result.pricing[attr.attribute_code].is_percent;
	            attribute.pricing_value = (result.pricing[attr.attribute_code].value == null ? '' : result.pricing[attr.attribute_code].value);
	        }
	    }.bind(this));

	    this.attributes.each(function(attribute) {
	        if ($('simple_product_' + attribute.attribute_code)) {
	            $('simple_product_' + attribute.attribute_code).value = '';
	        }
	    }.bind(this));

	    this.links[result.product_id] = result.attributes;
	    this.updateGrid();
	    this.updateValues();
	    this.grid.reload();
	},
	checkCreationUniqueAttributes: function () {
	    var attributes = [];
	    this.attributes.each(function(attribute) {
	        attributes.push({
	            attribute_id:attribute.attribute_id,
	            value_index: $('simple_product_' + attribute.attribute_code).value
	        });
	    }.bind(this));

	    return this.checkAttributes(attributes);
	},
	getAttributeByCode: function (attributeCode) {
	    var attribute = null;
	    this.attributes.each(function(item){
	        if (item.attribute_code == attributeCode) {
	            attribute = item;
	            throw $break;
	        }
	    });
	    return attribute;
	},
	getAttributeById: function (attributeId) {
	    var attribute = null;
	    this.attributes.each(function(item){
	        if (item.attribute_id == attributeId) {
	            attribute = item;
	            throw $break;
	        }
	    });
	    return attribute;
	},
	getValueByIndex: function (attribute, valueIndex) {
	    var result = null;
	    attribute.values.each(function(value){
	       if (value.value_index == valueIndex) {
	           result = value;
	           throw $break;
	       }
	    });
	    return result;
	},
	showPricing: function (select, attributeCode) {
        var attribute = this.getAttributeByCode(attributeCode);
        if (!attribute) {
            return;
        }

        select = $(select);
        if (select.value && !$('simple_product_' + attributeCode + '_pricing_container')) {
            new Insertion.After(select, '<div class="left"></div> <div id="simple_product_' + attributeCode + '_pricing_container" class="left"></div>');
            var newContainer = select.next('div');
            select.parentNode.removeChild(select);
            newContainer.appendChild(select);
            // Fix visualization bug
            $(this.idPrefix + 'simple_form').down('.form-list').style.width = '100%';
        }

        var container = $('simple_product_' + attributeCode + '_pricing_container');

        if (select.value) {
            var value = this.getValueByIndex(attribute,select.value);
            if (!value) {
                if (!container.down('.attribute-price')) {
                    container.update(this.pricingValueTemplate.evaluate(value));
                    var priceValueField = container.down('.attribute-price');
                    var priceTypeField = container.down('.attribute-price-type');

                    priceValueField.attributeCode = attributeCode;
                    priceValueField.priceField = priceValueField;
                    priceValueField.typeField = priceTypeField;

                    priceTypeField.attributeCode = attributeCode;
                    priceTypeField.priceField = priceValueField;
                    priceTypeField.typeField = priceTypeField;

                    Event.observe(priceValueField, 'change', this.updateSimplePricing.bindAsEventListener(this));
                    Event.observe(priceValueField, 'keyup', this.updateSimplePricing.bindAsEventListener(this));
                    Event.observe(priceTypeField, 'change',  this.updateSimplePricing.bindAsEventListener(this));

                    $('simple_product_' + attributeCode + '_pricing_value').value = null;
                    $('simple_product_' + attributeCode + '_pricing_type').value = null;
                }
            } else if (!isNaN(parseFloat(value.pricing_value))) {
                container.update(this.pricingValueViewTemplate.evaluate({
                    'value': (parseFloat(value.pricing_value) > 0 ? '+' : '') + parseFloat(value.pricing_value)
                             + ( parseInt(value.is_percent) > 0 ? '%' : '')
                }));
                $('simple_product_' + attributeCode + '_pricing_value').value = value.pricing_value;
                $('simple_product_' + attributeCode + '_pricing_type').value = value.is_percent;
            } else {
                container.update('');
                $('simple_product_' + attributeCode + '_pricing_value').value = null;
                $('simple_product_' + attributeCode + '_pricing_type').value = null;
            }
        } else if(container) {
            container.update('');
            $('simple_product_' + attributeCode + '_pricing_value').value = null;
            $('simple_product_' + attributeCode + '_pricing_type').value = null;
        }
	},
	updateSimplePricing: function(evt) {
        var element = Event.element(evt);
        if (!element.priceField.value.blank()) {
            $('simple_product_' + element.attributeCode + '_pricing_value').value = element.priceField.value;
            $('simple_product_' + element.attributeCode + '_pricing_type').value = element.typeField.value;
        } else {
            $('simple_product_' + element.attributeCode + '_pricing_value').value = null;
            $('simple_product_' + element.attributeCode + '_pricing_type').value = null;
        }
	},
	updateSimpleForm: function() {
	    this.attributes.each(function(attribute) {
	        if ($('simple_product_' + attribute.attribute_code)) {
	            this.showPricing($('simple_product_' + attribute.attribute_code), attribute.attribute_code);
	        }
	    }.bind(this));
	}
}

var onInitDisableFieldsList = [];

function toogleFieldEditMode(toogleIdentifier, fieldContainer) {
   if($(toogleIdentifier).checked) {
       enableFieldEditMode(fieldContainer);
   } else {
       disableFieldEditMode(fieldContainer);
   }
}

function disableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = true;
    if($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = true;
    }
}

function enableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = false;
    if($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = false;
    }
}

function initDisableFields(fieldContainer)
{
    onInitDisableFieldsList.push(fieldContainer);
}

function onCompleteDisableInited()
{
    onInitDisableFieldsList.each(function(item){
        disableFieldEditMode(item);
    });
}

Event.observe(window, 'load', onCompleteDisableInited);