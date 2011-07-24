


$(document).ready(function() {
	
	
	// Text and number cells should have input boxes in them
	
	tabindex = 1;
	
	$('td.text, td.number').each(function(){
		value = $(this).text();
		cls = $(this).attr('class');
		tabindex += 1;
		
		
		$(this).empty()
			.removeClass(cls)
			.append($('<input type="text">')
						.attr('tabindex', tabindex)
						.addClass(cls)
						.attr('value', value)
						.attr('maxlength', 32)
						.blur(function(){
							// if the input box is a number box, get and parse the number
							if ($(this).hasClass('number')) {
								addtovalue($(this), 0);
							}
							
							// If the value of the imput box is '', set it to a default and
							// make it show up as empty
							if ($(this).attr('value') == '') {
								if ($(this).hasClass('text')) $(this).attr('value', 'Ingen text');
								else $(this).attr('value', 0);
								
								$(this).addClass('empty');
							}
						})
						.bind('click focus keydown', function(){
							
							$('.left, .right').addClass('hidden');
							
							if ($(this).hasClass('empty')) {
								$(this)
									.attr('value', '')
									.removeClass('empty');
							}
						})
						.blur()
				);
					
		if (cls == 'number') {
			$('<a href="#"></a>')
				.addClass('button single right')
				.appendTo($(this));
				
			$('<a href="#"></a>')
				.addClass('button single left')
				.prependTo($(this));
				
			$(this).children('input')
				.bind('focus click keydown', function(event) {
					$(this).siblings('.left, .right').removeClass('hidden');
					
					if (event.keyCode == 37) {
						$(this).siblings('.left').click();
						$(this).focus();
					}
				});
				
			$(this).children('a')
				.click( function(){
					// Parse the input's value as a float
					$boxelem = $(this).siblings('input');
					
					if ($(this).hasClass("right")) {
						addition = 1;
					} else {
						addition = -1;
					}
					
					addtovalue($boxelem, addition).removeClass('empty').blur();
					// Prevent default
					
					event.preventDefault();
					return false;
				});
		}
					
	});
	
	$('td input').first().focus();
	
	$('#save').click(function(){
		makeXML();
	});
	
});

function makenum(numstr) {
	number = parseFloat(numstr);
	if (isNaN(number)){
		return false;
	} else {
		return number;
	}
}


function addtovalue($box, addition) {
	number = makenum($box.attr('value'));
	
	if (number !== false) {
		$box.attr('value', number+addition);
	} else {
		$box.attr('value', '')
	}
	return $box;
}

function makeXML() {
	// Create an XML document
	text = "<form></form>";
	if (window.DOMParser) {
		parser=new DOMParser();
		xmlDoc=parser.parseFromString(text,"text/xml");
	} else {// Internet Explorer
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async="false";
		xmlDoc.loadXML(text);
	}
	
	
	// Add a <group>-element for each group, with title=groupname.text()
	
	$('.group').each(function(index){
		title = $(this).find('.groupheader').text()
		
		groupelem = xmlDoc.createElement("group");
		
		groupelem.setAttribute('title',title)
		groupelem.setAttribute('id',$(this).attr('id'));
		xmlDoc.getElementsByTagName("form")[0].appendChild(groupelem);


		// Add <row>-elements to each group

		$(this).find('tr').each(function(){
			rowelem = xmlDoc.createElement("row");
			rowelem.setAttribute('id',$(this).attr('id'));
			
			groupelem.appendChild(rowelem);


			// Add <field>-elements to each group, with type=field type and contents=value
			
			$(this).find('td').each(function(){
				
				// Create the element & insert it
				
				fieldelem = xmlDoc.createElement("field");
				rowelem.appendChild(fieldelem);
				
				// Set the id
				
				fieldelem.setAttribute('id',$(this).attr('id'));
				
				// Set the type & value
				
				var type;
				if ($(this).hasClass('static')) {
					type = 'static';
					value = $(this).text();
				} else if ($(this).hasClass('header')) {
					type = 'header';
					value = $(this).text();
				} else if ($(this).hasClass('number')) {
					type = 'number';
					value = $(this).find('input').attr('value');
				} else {
					type = 'text';
					value = $(this).find('input').attr('value');
				}
				
				var textNode = document.createTextNode (value);
				fieldelem.appendChild (textNode);
				fieldelem.setAttribute('type',type);
				
			})
			
		});
		
	})
	
	// Serialize the XML document and put it into the hidden XML <input>, which is first cleared
	
	var string;
	if (window.ActiveXObject) { // code for IE
		string = xmlobject.xml;
	} else { // code for Mozilla, Firefox, Opera, etc.
		string = (new XMLSerializer()).serializeToString(xmlDoc);
	}
	
	$('#xml').attr('value',string);

}