


$(document).ready(function() {
	
	
	// Text and number cells should have input boxes in them
	
	tabindex = 1;
	
	// For each td.text and td.number...
	$('td.text, td.number').each(function(){
		
		// Get the value
		value = $(this).text();
		
		// Get the class
		cls = $(this).attr('class');
		
		// Add one to the tab index
		tabindex += 1;
		
		
		$(this).empty()
			.removeClass(cls)
			.append($('<input type="text">')
						.attr('tabindex', tabindex)
						.addClass(cls)
						.attr('value', value)
						.attr('maxlength', 32)
						
						// When input box is unfocused...
						.blur(function(){
							// if the input box is a number box, get and parse the number

							
							// If the value of the input box is '', set it to a default and
							// make it show up as empty
							if ($(this).attr('value') == '') {
								
								// "No text" is it's a text box, otherwise a 0.
								if ($(this).hasClass('text')) $(this).attr('value', 'Ingen text');
								else $(this).attr('value', 0);
								
								$(this).addClass('empty');
							}
						})
						
						// When input box is clicked on, focused on, or a key is pressed in it
						.bind('click focus keydown', function(){
							
							// Check if it's empty - if it is, remove placeholder text
							if ($(this).hasClass('empty')) {
								$(this).removeClass('empty');
								$(this).attr('value','');
							}
						})
						.blur()
				);
		if (cls.indexOf('number') != -1) {
			$('<a href="#"></a>')
				.addClass('button single right hidden')
				.appendTo($(this));
				
			$('<a href="#"></a>')
				.addClass('button single left hidden')
				.prependTo($(this));
				
			$(this).children('input')
				.bind('focus click keydown', function(event) {
					
					// When a Number-input box is focused on, hide all other
					// arrow links, and show the input box's.
					$('.left, .right').addClass('hidden');
					$(this).siblings('.left, .right').removeClass('hidden');
					
					// Use keycodes to determine how much to add to the value
					if (event.keyCode == 37) { // Left key
						addtovalue($(this), -1);
						event.preventDefault();
						
					} else if (event.keyCode == 39) { // Right key
						addtovalue($(this), +1);
						event.preventDefault();
						
					} else if (event.keyCode == 38) { // Up key
						addtovalue($(this), +10);
						event.preventDefault();
						
					} else if (event.keyCode == 40) { // Down key
						addtovalue($(this), -10);
						event.preventDefault();
					}	
				})
				.blur(function(){
					addtovalue($(this), 0);
				});
				
			$(this).children('a')
				.click( function(event){

					// Get the box element
					$boxelem = $(this).siblings('input');
					
					if ($(this).hasClass("right")) addition = 1;
					else addition = -1;
					
					if (event.shiftKey) {
						addition *= 10;
					}
					
					// Use addtovalue to make sure it's a number
					addtovalue($boxelem, addition).removeClass('empty');
					
					$boxelem.click().focus();
					$(this).removeClass('hidden');
					
					// Prevent default
					event.preventDefault();
					return false;
				})
				
				.hover(function(){
					
					// When mouse is over either link, show them both
					$(this).parent().children('a').removeClass('hidden');
					
					}, function(){
					
					// If the element's input sibling isn't focused
					if ($(this).siblings('input:focus').length == 0) {
						
						// Hide the link arrow on mouseout
						$(this).parent().children('a').addClass('hidden');
					}
				})
				;
		}
					
	});
	
	$('td input').first().focus();
	
	$('#save').click(function(){
		makeXML();
		//document.charform.submit();
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
		number = Math.min(255, number+addition);
		number = Math.max(-255, number);
		$box.attr('value', number);
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
				} else if ($(this).children('input').hasClass('number')) {
					type = 'number';
					value = $(this).find('input').attr('value');
				} else {
					type = 'text';
					value = $(this).find('input').attr('value');
				}
				
				if ($(this).find('input').hasClass('empty')) {
					value = "";
				}
				
				
				
				var textNode = xmlDoc.createTextNode (value);
				fieldelem.appendChild (textNode);
				fieldelem.setAttribute('type',type);
				
				
				
			})
			
		});
		
	})
	
	
	
	// Serialize the XML document and put it into the hidden XML <input>, which is first cleared
	
	var string;
	if (window.ActiveXObject) { // code for IE
		string = xmlDoc.xml;
		
	} else { // code for Mozilla, Firefox, Opera, etc.
		string = (new XMLSerializer()).serializeToString(xmlDoc);
	}
	
	$('#xml').attr('value',string);

}