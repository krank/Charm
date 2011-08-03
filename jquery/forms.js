MAX_GROUPS = 16;
MAX_ROWS = 32;
MAX_FIELDS = 12;

$(document).ready(function() {
	
	// Add an adding-button to the footer
	
	$bigaddbutton = $('<a class="button big add" id="addgroup"></a>')
		.prependTo('#footer');
	
	$bigaddbutton.click(
		function(event) {
			addGroup().slideDown('fast');
		}
	);
		
	// If there's no data in the groups div
	if ($('#groups').text() == ""){
		// Add a group, and show it
		addGroup().show();
	} else {
		// Otherwise, make the form generated by PHP dynamic.
		
		makeDynamic();
		
	}
	
	// Make the groups sortable
	$('#groups').sortable({handle: '.move',
							placeholder: 'grouphighlight',
							opacity : 0.6,
							axis : 'y',
							revert : true});
	
	// Make the savebutton clickable
	$('#save').click(function(){
		makeXML();
	});
	
});

function makeDynamic() {
	// Find all groups
	$('#groups .group').each(function(){
		dynGroup($(this));
		
		// Find all rows, add dynamics to each
		
		$(this).find('.row').each(function(){
			dynRow($(this));
			
			// Find all fields, add dynamics to each
			$(this).find('.field').each(function(){
				dynField($(this));
			});
			
		});
	});

}


function addGroup() {
	// Get current number of groups
	glen = $('#groups .group').length;
	
	// Create the group element
	$groupelem = $('<div class="group" id="g'+glen+'"><div class="groupheader"></div></div>')
		.append($('<div></div')
			.addClass('rows')
			)
		.hide()
		.appendTo('#groups');
	
	// Find the group header
	$ghead = $groupelem.find('.groupheader');
	
	// Append a header
	$('<h2>Klicka på namnet för att ändra</h2>').appendTo($ghead);

	// Hide Add button if there are too many groups
	if (glen >= MAX_GROUPS-1) $('#addgroup').fadeOut();
	
	// make the group dynamic
	dynGroup($groupelem);
	
	return $groupelem;
}

function dynGroup($group) {
	// Add dynamics to header
		$ghead = $group.find('.groupheader');
		
		// Add the move handle
		$('<a class="button big move" name="movegroup"></a>')
			.prependTo($ghead);
		
		// Add the big del button
		$bigdelbutton = $('<a class="button big del" name="addgroup"></a>')
			.prependTo($ghead)
			.click(
				function(event) {
					$(event.target).parent().parent()
						.fadeOut('slow')
						.queue(function() {
							$(this).remove();
							if ($('#addgroup').css('display') == 'none') {
								$('#addgroup').fadeIn();
							}

						});
				}
			);
		
		// Label
		$group.find('h2')
			.after(
				$('<input type="text">')
					.attr('maxlength','32')
					.hide()
					.bind('blur keydown', function(event) {
						if (event.type == 'blur' || event.keyCode==13) {

							if ($(this).attr('value') == '') {
								$(this).attr('value', 'Klicka på namnet för att ändra')
							}
							
							$(this)
								.hide()
								.siblings('h2')
									.empty()
									.append($(this).attr('value'))
									.show()
							event.preventDefault();
							return false;
						}
						return true;
					})
					)
			.click(function() {
				$(this).hide()
					.siblings('input')
						.attr('value', $(this).text())
						.show()
						.focus()
						.select();
			})
			.hover(function(){
				$(this).toggleClass("hovered");
			});
		
		// Add Row button
		
		$('<a class="button add rows">L&auml;gg till en rad</a>')
			.appendTo($group)
				.click(function(event) {
					
					if (event.shiftKey) {
						// Find the last row
						$last = $(this).siblings('.rows').children('.row').last();
						
						// Create a clone, and hide it
						$new = $last.clone(true).hide();
						
						// Slide it in
						$new.insertAfter($last).slideDown('fast');
						
					} else {
						// Create a new row
						$new = addRow(event.target).slideDown('fast');
					}
					
					// Let the button fade out if the maximum number of rows
					if ($(this).siblings('.row').length >= MAX_ROWS) {
						$(this).fadeOut();
					}
					
					$new.find('input').last().focus().click();
					
				});
				
		// Make the rows sortable
		
		$group.children('.rows').sortable({handle		: '.move',
											placeholder	: 'rowhighlight',
											opacity		: 0.6,
											axis		: 'y',
											revert		: true});
}

function addRow(button) {
	$parent = $(button).siblings('.rows');
	plen = $parent.children('.row').length;

	// Container
	$rowelem = $('<div class="row"></div>')
		.attr('id', $parent.attr('id')+'r'+plen)
		.hide()
		.appendTo($parent);
	
	
	
	// Add the first field
	addField($rowelem).show();
	
	// Add the clearing element
	$('<div style="clear:both;" />').appendTo($rowelem);
	
	// Make the row dynamic
	dynRow($rowelem);
	
	return $rowelem;
	
}

function dynRow($row) {
	
	// Delete Row button
	$('<a href="#" class="single del button"></a>')
		.prependTo($row)
		
		// The move handle
		.after($('<a class="move"></a>')
			.addClass('button')
			.hover(function() {
				$(this).parent().addClass("hovered");
			}, function() {
				$(this).parent().removeClass("hovered");
			})
		)
			
		//Wrap them both
		.wrap('<div class="inner"/>')
		.hover(function() {
			$(this).parentsUntil('.row').parent().addClass("hovered");
		}, function() {
			$(this).parentsUntil('.row').parent().removeClass("hovered");
		})
		.click(function(event) {
			// Fade out, then remove
			$(this).parentsUntil('.row').parent()
					.fadeOut()
					.queue(function() {
						$(this).remove();
					});
			event.preventDefault();
			return false;
		});
		
	// Add Field button
	$('<a href="#" class="single add button fields"></a>')
		.insertBefore($row.children(':last-child'))
		.wrap('<div class="inner"/>')
		.click(function(event) {
			
			if (event.shiftKey) {
				// Find last field
				$lastfield = $(this).parent().siblings('.field').last();
				// Clone it, insert the clone, and fade it in.
				$field = $lastfield.clone(true).hide().insertAfter($lastfield).fadeIn();
			} else {
				$field = addField($(event.target).parentsUntil('.row').parent()).fadeIn();
			}
			
			$field.find('input').click().focus();

			// If the number of fields are [max], hide the button
			if ($(this).parent().siblings('.field').length >= MAX_FIELDS) {
				$(this).fadeOut();
			}

			// Prevent default
			event.preventDefault();
			return false;

		});
}

function addField($parent) {

	flen = $parent.children('.field').length;
	
	$field = $('<div class="inner field"></div>')
		.append($('<a class="button single text type" href="#"></a>'))
		.hide()
		.attr('id',
			$parent.attr('id') + 'f'+flen
		)

	if ($parent.find('.add').length != 0) {
		$field.insertBefore($parent.find('.add').parent())
	} else {
		$field.appendTo($parent);
	}

	// Make the field dynamic
	dynField($field);
	
	return $field;
	
}

function dynField($field) {
	text = $field.text();
	
	if ($field.hasClass('static')) {
		type = 'static';
	} else if ($field.hasClass('header')) {
		type = 'header';
	} else if ($field.hasClass('number')) {
		type = 'number';
	} else {
		type = 'text';
	}
	
	$field.keydown(function(event) {
			if (event.ctrlKey) {
				switch(event.keyCode) {
					case 37: // Left
						if($(this).prev().length>0) {
							$(this).prev().find('input').focus().click();
						}
						break;
					case 39: // Right
						if($(this).next().length>0) {
							$(this).next().find('input').focus().click();
						}
						break;

	
					case 38: // Up
						$prevcell = $(
										$(this).parent().prev('.row').
												children().
												get(
													$(this).index()
												)
									).find('input');
						if ($prevcell.length > 0) {
							$prevcell.click().focus();
						}
						break;
					case 40: // Down
						$nextcell = $(
										$(this).parent().next('.row').
												children().
												get(
													$(this).index()
												)
									).find('input');
						if ($nextcell.length > 0) {
							$nextcell.click().focus();
						}
						break;
				}
			}
			
		});
	
	

	$field.empty();
	
	// The input box
	$('<input type="text">')
		.attr('value',text)
		.blur(function(){
			if ($(this).attr('value') == '') {
				$(this)
					.attr('value', 'Ingen text')
					.addClass('empty');
			}
		})
		.bind('click keydown',function(){
			if ($(this).hasClass('empty')) {
				$(this)
					.attr('value', '')
					.removeClass('empty');
			}
		})
		.blur()
		.appendTo($field)
		
	$("<a class=\"button single type\" href=\"#\"></a>").appendTo($field)
		.addClass("button single type "+type)
		.click(function(event){
			$(this).siblings('ul').fadeIn('fast');
			event.preventDefault();
			return false;
		});
		
	// The type selector list
	$('<ul></ul>')
		.appendTo($field)
		.append(
			typeButton('Textbox', 'text'),
			typeButton('Statisk text', 'static'),
			typeButton('Rubrik', 'header'),
			typeButton('Nummer', 'number')
		)
		.mouseleave(function(){
			$(this).fadeOut('fast');
		})
		.wrapInner('<li />')
		.appendTo($field);
		
	// Delete button
	$('<a class="button single del" href="#"></a>')
		.click(function(event){

			// Fade in the Add button if it's invisible
			$addbutton = $(this).parentsUntil('.row').parent().find('.fields');
			if ($addbutton.css('display') == 'none') {
				$addbutton.fadeIn();
			}
			
			// Fade out and remove this element
			$(this).parent()
				.fadeOut()
				.queue(function() {
						$(this).remove();
					})
			event.preventDefault();
			return false;
		})
		.hover(function () {
			$(this).parent().toggleClass("hovered");
		})
		.appendTo($field);
		
		
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

		$(this).find('.row').each(function(){
			rowelem = xmlDoc.createElement("row");
			rowelem.setAttribute('id',$(this).attr('id'));
			
			groupelem.appendChild(rowelem);


			// Add <field>-elements to each group, with type=field type and contents=value
			
			$(this).find('.field').each(function(){
				
				// Create the element & insert it
				
				fieldelem = xmlDoc.createElement("field");
				rowelem.appendChild(fieldelem);
				
				// Set the id
				
				fieldelem.setAttribute('id',$(this).attr('id'));
				
				// Set the content
				
				contentelem = $(this).find('input')

				if (!contentelem.hasClass('empty')) {
					var textNode = document.createTextNode (contentelem.attr('value'));
					fieldelem.appendChild (textNode);
				}
				
				// Set the type
				
				var type;
				var $typeelem = $(this).children('.type');
				
				if ($typeelem.hasClass('text')) {
					type = 'text';
				} else if ($typeelem.hasClass('header')) {
					type = 'header';
				} else if ($typeelem.hasClass('static')) {
					type = 'static';
				} else if ($typeelem.hasClass('number')) {
					type = 'number';
				}

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






function typeButton(text, typeClass) {
	return $('<a class="button" href="#"></a>')
					.addClass(typeClass)
					.append(text)
					.click(function(event){
						$(this)
							.parentsUntil('ul')
							.parent().fadeOut('fast')
							.parent().children('.type')
								.removeClass('text static header number')
								.addClass(typeClass);
						event.preventDefault();
						return false;
					})

}