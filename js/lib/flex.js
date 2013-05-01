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
Flex = new Object();

/* Flex Event Dispatcher */
Flex.EventDispatcher = Class.create();
Flex.EventDispatcher.prototype = {
    observers: new Array(),
    target: false,
    
    /**
            * Constructor
            * @param target event target
            */
    initialize: function(  ) {
        if ( arguments.length == 1 ) {  
            this.target = arguments[0];
        } else {
            this.target = this;
        }
    },
    
    /**
            * Dispatches event in event flow
            *
            * @param event event object
            */
    dispatchEvent: function ( eventObject )  {
        eventObject.target = this.target;
        
        this.observers.each( function( element, index ) {
            if( element.e == eventObject.type && !eventObject.dontPropogonate) {
                    element.c.call( element.s, eventObject );
            }           
        } );
        
        if( !eventObject.canceled ) {
            return true;
        }
        
        return false;
    },
    
    /**
            * Adds event listener
            *
            * @param type type of event
            * @param callback callback function
            * @param scope this object
            */
    addEventListener: function( type, callback, scope ) {
        this.observers.push( {
                e: type,
                c: callback,
                s: scope
        } );
    },
    
    /**
            * Removes event listener
            *
            * @param type type of event
            * @param callback callback function
            * @param scope this object
            */
    removeEventListener: function( type, callback, scope ) {
       this.observers = this.observers.without( {
                e: type,
                c: callback,
                s: scope
        } );
    },
    
    /**
            * Removes all event listeners.
            */
    removeAllEventListeners: function( ) {
        this.observers = new Array();
    },
    
    /**
            * Check exists of event listener for some event 
            *
            *  @param type type of event
            */
    hasEventListener: function( type ) {
        if( 
            this.observers.detect( function( element, index ) {
                if( element.e == type ) {
                    return true;
                }
                
                return false;
            } )
          ) {
            return true;
        }
        
        return false
    }
};

Flex.EventDispatcher.prototype.on = Flex.EventDispatcher.prototype.addEventListener;
Flex.EventDispatcher.prototype.un = Flex.EventDispatcher.prototype.removeEventListener;

/* Flex Event  Object */
Flex.Event = Class.create();
Flex.Event.LOAD = "load";
Flex.Event.PREINITIALIZE = "preinitialize";
Flex.Event.INITIALIZE = "initialize";

Flex.Event.prototype = {
    
    eventData: {},
    dontPropogonate: false,
    canceled: false,
    target: false,
    type: '',
    cancelable: false,
    
    /**
            * Constructor
            * 
            * @param type type of event
            * @param canelable specifies ability for event cancel
            */
    initialize: function( type, cancelable ) {
        this.cancelable = cancelable;
        this.type = type;
    },
    
    /**
             * Stop propaganate event
             */
    stopPropoganation: function () {
        if( this.cancelable ) {
            this.dontPropogonate = true;
        }
    },
    
    /**
            * Stop default event action
            */
    preventDefault: function () {
        if( this.cancelable ) {
            this.canceled = true;
        }
    },
    
    /**
             *  Stop propaganate event and stop default action
             */
    stopEvent: function () {
        this.stopPropoganation();
        this.preventDefault();
    }
};

if( !window.Mage )
{
    window.Mage = new Object();
}


/**
  * Bridge with flex
  */
Mage.FlexObjectApi = {
    objectMap: new Object(),
    
    callBack : function( id, type, eventData )	{
        var evt = new Flex.Event( type, true );
        evt.eventData = eventData;
        
        if (!this.objectMap[id].dispatchEvent( evt )) {
            return false;
        } 
        return true;
    },
    
    registerObject : function( id, obj ) {
        this.objectMap[id] = obj;
    },
    
    unregisterObject: function( id ) {
        delete( this.objectMap[id] );
    }
};

Flex.Object = Class.create();


    

Mage.FlexObjectApi = {
	objectMap: new Object(),	
	callBack : function( id, eventType, eventData )	{
		      
        var eventObj = new Flex.Event( eventType, true );
        eventObj.eventData = eventData;
        
		if(!this.objectMap[id].dispatchEvent( eventObj )) {
			return false;
		} 
        
		return true;
	},
	registerObject : function( id, obj ) {
		this.objectMap[id] = obj;
	},
	unregisterObject: function( id ) {
		delete( this.objectMap[id] );
	}
}
Flex.Object = Class.create();

Object.extend( Flex.Object.prototype,  Flex.EventDispatcher.prototype );

Object.extend( Flex.Object.prototype, { 
			initialize: function ( config ) {
                Flex.EventDispatcher.prototype.initialize.call( this );
                
                this.isIE  = (navigator.appVersion.indexOf("MSIE") != -1) ? true : false;
            	this.isWin = (navigator.appVersion.toLowerCase().indexOf("win") != -1) ? true : false;
            	this.isOpera = (navigator.userAgent.indexOf("Opera") != -1) ? true : false;
            	this.attributes = {
            		 quality:"high",
                     bgcolor:"#FFFFFF",
            		 pluginspage: "http://www.adobe.com/go/getflashplayer",
            		 type: "application/x-shockwave-flash",
            		 allowScriptAccess: "always",
                     classid: "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
            	};
            	
            	this.setAttributes( config );
            	this.applied = false;
            	
                var myTemplatesPattern = /(^|.|\r|\n)(\{(.*?)\})/;
                
            	if(this.detectFlashVersion(9, 0, 45)) {
            		if(this.isIE && !this.isOpera) {
            			this.template = new Template( '<object {objectAttributes}><param name="allowFullScreen" value="true"/>{objectParameters}</object>', myTemplatesPattern )
            		} else {
            			this.template = new Template( '<embed {embedAttributes} allowfullscreen="true" />', myTemplatesPattern );
            		}
            	} else {
            		this.template = new Template(  'This content requires the Adobe Flash Player. '
            										   +' <a href=http://www.adobe.com/go/getflash/>Get Flash</a>', myTemplatesPattern );
            	}
            	
            	
            	
            	this.paramtersTemplate = new Template( '<param name="{name}" {wmodeID} value="{value}" />', myTemplatesPattern );
            	this.attributesTemplate = new Template( ' {name}="{value}" ', myTemplatesPattern );            	
            },
            
			setAttribute : function( name, value ) {
				if(!this.applied) {
					this.attributes[name] = value;
                }
			},
			
			getAttribute : function( name ) {
				return this.attributes[name];
			},
			
			setAttributes : function( attributesList ) {
				for ( var key in attributesList )
				{
					if(!this.applied) {
						this.attributes[key] = attributesList[key];
                    }
				}
			},
			
			getAttributes : function( ) {
				return this.attributes;
			},
			
			apply : function( container ) {
				if (!this.applied)	{
                                        
					this.setAttribute( "id", Flex.uniqId());
					var readyHTML = this.template.evaluate( this.generateTemplateValues() );
                    $(container).update( readyHTML );
					Mage.FlexObjectApi.registerObject( this.getAttribute("id"), this );
				}
				this.applied = true;
			},
            
            applyWrite : function( ) {
				if (!this.applied)	{
                                        
					this.setAttribute( "id", Flex.uniqId());
					var readyHTML = this.template.evaluate( this.generateTemplateValues() );
                    //$(container).update( readyHTML );
                    document.write( readyHTML );
                    Mage.FlexObjectApi.registerObject( this.getAttribute("id"), this );
				}
				this.applied = true;
			},
            
            getApi : function() 
			{
				if (!this.applied) {
                    return false;
                }
				
				return $( this.getAttribute('id') );
			},

			generateTemplateValues : function( )
			{
				var embedAttributes = new Object();
				var objectAttributes = new Object();
				var parameters = new Object();
				for (var key in this.attributes ) {
					var attributeName = key.toLowerCase();
                    this.attributes[key] = this.escapeAttributes( this.attributes[key] );
                    
					switch (attributeName) {   
						case "pluginspage":
							embedAttributes[key] = this.attributes[key];
							break;
						case "src":
						case "movie": 
							embedAttributes['src'] = parameters['movie'] = this.attributes[key];
							break;
						case "type":
							embedAttributes[key]  = this.attributes[key];
						case "classid":
						case "codebase":
							objectAttributes[key] = this.attributes[key];
							break;
						case "id":
							embedAttributes['name'] = this.attributes[key];
						case "width":
						case "height":
						case "align":
						case "vspace": 
						case "hspace":
						case "class":
						case "title":
						case "accesskey":
						case "name":
						case "tabindex":
							embedAttributes[key] = objectAttributes[key] = this.attributes[key];
							break;
						default:
							embedAttributes[key] = parameters[key] = this.attributes[key];
							break;
					}
				}
				var i; 
				var result = new Object();
				result.objectAttributes = '';
				result.objectParameters = '';
				result.embedAttributes  = '';
				
				for ( i in objectAttributes) {
					result.objectAttributes += this.attributesTemplate.evaluate( {name:i, value: objectAttributes[i]} );
				}
				
				for ( i in embedAttributes)	{
					result.embedAttributes += this.attributesTemplate.evaluate( {name:i, value: embedAttributes[i]} );
				}
				
				for ( i in parameters) {
                
                    var wmodeId = ' id="' + this.getAttribute('id') + 'wmode' + '"';
                    
                    if( i.toLowerCase() != 'wmode' ) {
                        wmodeId = '';
                    }
                    
					result.objectParameters += this.paramtersTemplate.evaluate( {name:i, wmodeID: wmodeId, value: parameters[i]} );
				}
				
				return result;
			},
            escapeAttributes: function (value) {
                return value.replace(new RegExp("&","g"), "&amp;");
            },
            setForFullScreen: function (value) {
                if( value ) {
                    if( this.isIE && !this.isOpera ) {
                        $(this.getAttribute('id') + 'wmode').value = 'window';
                       
                    } else {
                        $(this.getAttribute('id')).wmode = 'window';
                    }                        
                } else {
                    if( this.isIE && !this.isOpera ) {
                        $(this.getAttribute('id') + 'wmode').value = 'opaque';
                       
                    } else {
                        $(this.getAttribute('id')).wmode = 'opaque';
                    }    
                }
            },
			detectFlashVersion : function( reqMajorVer, reqMinorVer, reqRevision ) {
				var versionStr = this.getSwfVer();
			    if (versionStr == -1 ) {
			        return false;
			    } else if (versionStr != 0) {
			        if(this.isIE && this.isWin && !this.isOpera) {
			            // Given "WIN 2,0,0,11"
			            tempArray         = versionStr.split(" ");  // ["WIN", "2,0,0,11"]
			            tempString        = tempArray[1];           // "2,0,0,11"
			            versionArray      = tempString.split(",");  // ['2', '0', '0', '11']
			        } else {
			            versionArray      = versionStr.split(".");
			        }
			        var versionMajor      = versionArray[0];
			        var versionMinor      = versionArray[1];
			        var versionRevision   = versionArray[2];

			            // is the major.revision >= requested major.revision AND the minor version >= requested minor
			        if (versionMajor > parseFloat(reqMajorVer)) {
			            return true;
			        } else if (versionMajor == parseFloat(reqMajorVer)) {
			            if (versionMinor > parseFloat(reqMinorVer))
			                return true;
			            else if (versionMinor == parseFloat(reqMinorVer)) {
			                if (versionRevision >= parseFloat(reqRevision))
			                    return true;
			            }
			        }
			        return false;
			    }
			},

			controlVersion : function () {
			    var version;
			    var axo;
			    var e;
			    try {
			        // version will be set for 7.X or greater players
			        axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
			        version = axo.GetVariable("$version");
			    } catch (e) {
			    }

			    if (!version) {
			        try {
			            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");
			            version = "WIN 6,0,21,0";
			            axo.AllowScriptAccess = "always";
			            version = axo.GetVariable("$version");

			        } catch (e) {
			        }
			    }

			    if (!version) {
			        try {
			            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
			            version = axo.GetVariable("$version");
			        } catch (e) {
			        }
			    }

			    if (!version) {
			        try {
			            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.3");
			            version = "WIN 3,0,18,0";
			        } catch (e) {
			        }
			    }

			    if (!version) {
			        try {
			            axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
			            version = "WIN 2,0,0,11";
			        } catch (e) {
			            version = -1;
			        }
			    }
			    return version;
			},

			getSwfVer : function (){
			    var flashVer = -1;
			    if (navigator.plugins != null && navigator.plugins.length > 0) {
			        if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]) {
			            var swVer2 = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "";
			            var flashDescription = navigator.plugins["Shockwave Flash" + swVer2].description;           
			            var descArray = flashDescription.split(" ");
			            var tempArrayMajor = descArray[2].split(".");
			            var versionMajor = tempArrayMajor[0];
			            var versionMinor = tempArrayMajor[1];
			            if ( descArray[3] != "" ) {
			                tempArrayMinor = descArray[3].split("r");
			            } else {
			                tempArrayMinor = descArray[4].split("r");
			            }
			            var versionRevision = tempArrayMinor[1] > 0 ? tempArrayMinor[1] : 0;
			            var flashVer = versionMajor + "." + versionMinor + "." + versionRevision;
			        }
			    }
			    else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.6") != -1) flashVer = 4;
			    else if (navigator.userAgent.toLowerCase().indexOf("webtv/2.5") != -1) flashVer = 3;
			    else if (navigator.userAgent.toLowerCase().indexOf("webtv") != -1) flashVer = 2;
			    else if ( this.isIE && this.isWin && !this.isOpera ) {
			        flashVer = this.controlVersion();
			    }
			    return flashVer;
			}
} );

Flex.currentID = 0;
Flex.uniqId = function() {
    return 'flexMovieUID'+( ++Flex.currentID );
}