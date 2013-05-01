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
               throw $continue;
           }
           var response = item.response.evalJSON();
           if (response.error) {
               throw $continue;
           }
           var newImage = {};
           newImage.url = response.url;
           newImage.file = response.file;
           newImage.label = '';
           newImage.position = this.getNextPosition();
           newImage.disabled = 0;
           newImage.remove = 0;
           this.images.push(newImage);
           this.uploader.removeFile(item.id);
        }.bind(this));
        this.updateImages();
    },
    updateImages: function() {
        this.getElement('save').value = this.images.toJSON();
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
      var image = this.getImageByFile(file);
      image.label = this.getFileElement(file, 'cell-label input').value;
      image.position = this.getFileElement(file, 'cell-position input').value;
      image.remove = (this.getFileElement(file, 'cell-remove input').checked ? 1 : 0);
      image.disabled = (this.getFileElement(file, 'cell-disable input').checked ? 1 : 0);
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
      this.getFileElement(file, 'cell-remove input').checked = (image.remove == 1);
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
      var image = false;
      this.images.each(function (item) {
         if (item.file == file) {
             image = item;
         }
      });
      return image;
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
		this.attributes = attributes;
		this.templatesSyntax = /(^|.|\r|\n)('{{\s*(\w+)\s*}}')/;
		this.idPrefix   = idPrefix;
		this.links 		= $H(links);
		this.newProducts = [];
		this.addAttributeTemplate = new Template($(idPrefix + 'attribute_template').innerHTML.replace(/__id__/g,"'{{html_id}}'").replace(/ template no-display/g,''), this.templatesSyntax);
		this.addValueTemplate = new Template($(idPrefix + 'value_template').innerHTML.replace(/__id__/g,"'{{html_id}}'").replace(/ template no-display/g,''), this.templatesSyntax);
		this.container = $(idPrefix + 'attributes');
		this.onLabelUpdate = this.updateLabel.bindAsEventListener(this);
		this.onValuePriceUpdate = this.updateValuePrice.bindAsEventListener(this);
		this.onValueTypeUpdate = this.updateValueType.bindAsEventListener(this);
		this.createAttributes();
		this.grid = grid;
		this.grid.rowClickCallback = this.rowClick.bind(this);
		this.grid.initRowCallback = this.rowInit.bind(this);
		this.grid.checkboxCheckCallback = this.registerProduct.bind(this);
		this.grid.rows.each(function(row) {
			this.rowInit(this.grid, row);
		}.bind(this));
	},
	createAttributes: function() {
		this.attributes.each(function(attribute, index) {
			var li = $(Builder.node('li', {className:'attribute'}));
			li.id = this.idPrefix + '_attribute_' + index;
			attribute.html_id = li.id;
			if(attribute && attribute.label && attribute.label.blank()) {
				attribute.label = '&nbsp;'
			}
			li.update(this.addAttributeTemplate.evaluate(attribute));
			li.attributeObject = attribute;

			this.container.appendChild(li);
			li.attributeValues = li.getElementsByClassName('attribute-values')[0];
			if (attribute.values) {
    			attribute.values.each(function(value){
    				this.createValueRow(li, value);
    			}.bind(this));
			}

			Event.observe(li.getElementsByClassName('attribute-label')[0],'change', this.onLabelUpdate);
			Event.observe(li.getElementsByClassName('attribute-label')[0],'keyup',  this.onLabelUpdate);
		}.bind(this));
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
	addNewProduct: function(productId) {
        this.newProducts.push(productId);
        this.updateGrid();
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
	rowClick: function(grid, event) {
		var trElement = Event.findElement(event, 'tr');
        var isInput   = Event.element(event).tagName == 'INPUT';
        if(trElement){
            var checkbox = Element.getElementsBySelector(trElement, 'input');
            if(checkbox[0] && !checkbox[0].disabled){
                var checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                grid.setCheckboxChecked(checkbox[0], checked);
            }
        }
	},
	rowInit: 		 function(grid, row) {
		var checkbox = $(row).getElementsByClassName('checkbox')[0];
		var input = $(row).getElementsByClassName('value-json')[0];

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
		var checkbox = $(row).getElementsByClassName('checkbox')[0];
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
		this.links.each(function(pair) {
			for (var i = 0, length=pair.value.length; i < length; i ++) {
				var attribute = pair.value[i];
				if(uniqueAttributeValues.keys().indexOf(attribute.attribute_id)==-1) {
					uniqueAttributeValues[attribute.attribute_id] = $H({});
				}
				uniqueAttributeValues[attribute.attribute_id][attribute.value_index] = attribute;
			}
		});
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
	},
	createValueRow: function(container, value) {

		var templateVariables = $H({});
		if(!this.valueAutoIndex) {
			this.valueAutoIndex = 1;
		}
		templateVariables.html_id = container.id  + '_' + this.valueAutoIndex;
		templateVariables.merge(value);
		this.valueAutoIndex++;

		var li = $(Builder.node('li', {className:'attribute-value'}));
		li.id = templateVariables.html_id;
		li.update(this.addValueTemplate.evaluate(templateVariables));
		li.valueObject = value;
		if (typeof li.valueObject.is_percent == 'undefined') {
			li.valueObject.is_percent = 0;
		}

		if (typeof li.valueObject.pricing_value == 'undefined') {
			li.valueObject.pricing_value = null;
		}

		container.attributeValues.appendChild(li);
		var priceField = li.getElementsByClassName('attribute-price')[0];
		var priceTypeField = li.getElementsByClassName('attribute-price-type')[0];

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
		this.updateSaveInput();
	},
	updateValueType:  function(event) {
		var li = Event.findElement(event, 'LI');
		li.valueObject.is_percent = (Event.element(event).value.blank() ? null : Event.element(event).value);
		this.updateSaveInput();
	},
	updateSaveInput: function() {
		$(this.idPrefix + 'save_attributes').value = this.attributes.toJSON();
		$(this.idPrefix + 'save_links').value  = this.links.toJSON();
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