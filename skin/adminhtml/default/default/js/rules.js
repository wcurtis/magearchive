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
var VarienRulesForm = new Class.create();
VarienRulesForm.prototype = {
    initialize : function(parent, newChildUrl){
        this.newChildUrl = newChildUrl;
        var elems = $(parent).getElementsByClassName('rule-param');

    	for (var i=0; i<elems.length; i++) {
            this.initParam(elems[i]);
        }
    },

    initParam: function (container) {
    	container.rulesObject = this;

        var label = Element.down(container, '.label');
        if (label) {
		  Event.observe(label, 'click', this.showParamInputField.bind(this, container));
        }

		var elem = Element.down(container, '.element');
		if (elem) {
		    elem = elem.down();
		    Event.observe(elem, 'change', this.hideParamInputField.bind(this, container));
		    Event.observe(elem, 'blur', this.hideParamInputField.bind(this, container));
		}

		var remove = Element.down(container, '.rule-param-remove');
		if (remove) {
		    Event.observe(remove, 'click', this.removeRuleEntry.bind(this, container));
		}
    },

    showParamInputField: function (container, event) {
    	Element.addClassName(container, 'rule-param-edit');
    	var elemContainer = Element.down(container, '.element');

    	var elem = Element.down(elemContainer, 'input.input-text');
    	if (elem) {
    	   elem.focus();
    	}

    	var elem = Element.down(elemContainer, 'select');
    	if (elem) {
    	   elem.focus();
    	   // trying to emulate enter to open dropdown
//    	   if (document.createEventObject) {
//        	   var event = document.createEventObject();
//        	   event.altKey = true;
//    	       event.keyCode = 40;
//    	       elem.fireEvent("onkeydown", evt);
//    	   } else {
//    	       var event = document.createEvent("Events");
//    	       event.altKey = true;
//    	       event.keyCode = 40;
//    	       elem.dispatchEvent(event);
//    	   }
    	}
    },

    hideParamInputField: function (container, event) {
    	Element.removeClassName(container, 'rule-param-edit');
    	var label = Element.down(container, '.label'), elem;

    	if (!container.hasClassName('rule-param-new-child')) {
        	elem = Element.down(container, 'select');
        	if (elem) {
        		label.innerHTML = elem.options[elem.selectedIndex].text;
        	}

        	elem = Element.down(container, 'input.input-text');
        	if (elem) {
        	    var str = elem.value.replace(/(^\s+|\s+$)/g, '');
        	    elem.value = str;
        	    if (str=='') {
        	        str = '...';
        	    } else if (str.length>30) {
        	        str = str.substr(0, 30)+'...';
        	    }
        		label.innerHTML = str;
        	}
    	} else {
    	    elem = Element.down(container, 'select');

    	    if (elem.value) {
    	        this.addRuleNewChild(elem);
    	    }

        	elem.value = '';
    	}
    },

    addRuleNewChild: function (elem) {
        var parent_id = elem.id.replace(/^.*:(.*):.*$/, '$1');
        var children_ul = $(elem.id.replace(/[^:]*$/, 'children'));
        var max_id = 0, i;
        var children_inputs = Selector.findChildElements(children_ul, $A(['input[type=hidden]']));
        if (children_inputs.length) {
            children_inputs.each(function(el){
                if (el.id.match(/:type$/)) {
                    i = 1*el.id.replace(/^.*:.*([0-9]+):.*$/, '$1');
                    max_id = i > max_id ? i : max_id;
                }
            });
        }
        var new_id = parent_id+'.'+(max_id+1);
        var new_type = elem.value;
        var new_elem = document.createElement('LI');
        new_elem.className = 'rule-param-wait';
        new_elem.innerHTML = 'Please wait, loading...';
        children_ul.insertBefore(new_elem, $(elem).up('li'));

        new Ajax.Updater(new_elem, this.newChildUrl, {
            parameters: { type:new_type.replace('/','-'), id:new_id },
            onComplete: this.onAddNewChildComplete.bind(this, new_elem),
            onFailure: this._processFailure.bind(this)
        });
    },

    _processFailure : function(transport){
        location.href = BASE_URL;
    },

    onAddNewChildComplete: function (new_elem) {
        $(new_elem).removeClassName('rule-param-wait');
        var elems = new_elem.getElementsByClassName('rule-param');
        for (var i=0; i<elems.length; i++) {
            this.initParam(elems[i]);
        }
    },

    removeRuleEntry: function (container, event) {
        var li = Element.up(container, 'li');
        li.parentNode.removeChild(li);
    }
}
