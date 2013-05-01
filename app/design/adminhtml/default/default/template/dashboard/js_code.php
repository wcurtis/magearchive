<?php
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
?>
<script language="JavaScript" type="text/javascript">
	<!--
	function buildGraph(xCount, yCount, xGraduation, yGraduation,  xHeading, yHeading, xType, yType, tId){
		var newGraph = document.getElementById(tId);
		var tr, td;
		var yNum = (yCount*yGraduation);
		var xNum = xGraduation;
		for(r=1;r<yCount+2;r++){
			tr = document.createElement("TR");
			if(r == 1){
				for(d=1;d<xCount+2;d++){
					td = document.createElement("td");
					if(d == 1){
						yNum -= yGraduation;
						td.className="yHeading";
						td.innerHTML = yHeading;
						tr.appendChild(td);
					}else{
						td.className="g"; 
						td.setAttribute("rowSpan", yCount+1);
						td.innerHTML = '<div id="gr'+(d-1)+'" style="height:'+(Math.floor(Math.random()*150)%150+50)+'px'+';"></div>'; // Math function just for testing!! Replace with real values!!
						tr.appendChild(td);
					}
				}
			}else{
				td = document.createElement("TD");
				td.className = 'y';
				td.innerHTML = (yNum);
				yNum -= yGraduation;
				tr.appendChild(td);
			}
			newGraph.lastChild.appendChild(tr);
		}
		tr = document.createElement("TR");
		for(h=1;h<xCount+2;h++){
			td = document.createElement("TD");
			if(h == 1){
				td.innerHTML = '&nbsp;';
				tr.appendChild(td);
			}else{
				td.className = 'x';
				td.innerHTML = xNum-1+(xType == 'hrs' ? ':00' : '$');
				xNum += 1;
				tr.appendChild(td);
			}
		}
		newGraph.lastChild.appendChild(tr);
	}
	function showTab(tab, set, tabQty){
		var tabOn = document.getElementById(set+tab+'Tab');
		var conOn = document.getElementById(set+'-'+tab+'-tab');
		tabOn.className = 'hover';
		conOn.style.display = 'block';
		for(t=1;t<tabQty+1;t++){ 
			if(t!=tab){
				document.getElementById(set+t+'Tab').className = '';
				document.getElementById(set+'-'+t+'-tab').style.display = 'none';
			}
		}
	}
	-->
</script>