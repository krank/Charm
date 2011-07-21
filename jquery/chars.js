


$(document).ready(function() {
	
	$('td.text').each(function(){
		value = $(this).text();
		$(this).empty()
			.removeClass('text')
			.append($('<input type="text">')
						.attr('value', value)
						.attr('maxlength', 32)
						.blur(function(){
							if ($(this).attr('value') == '') {
								$(this)
									.attr('value', 'Ingen text')
									.addClass('empty');
							}
						})
						.click(function(){
							if ($(this).hasClass('empty')) {
								$(this)
									.attr('value', '')
									.removeClass('empty');
							}
						})
				);
	});
	
	$('#save').click(function(){
		makeXML();
	});
	
	makeXML();
	
});




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