var FormActivity = (() => {
/*
*
* @description: Defines interaction with admin panel components, back and forth the server and conversion of the resonse into pretty fields
*
* @param: {statusContainer} Selector where the status of next activity will be displayed
* @param: {toRemove} fields in the returned data to not create inputs for
* @param: {addMore} will place a button beside these inputs to append more
* @param: {folderRoot} is dir name at which back end lives. set to null to use current domain
*
* @author: github.com/nmeri17
*/

function FA ({statusContainer= '', toRemove =[], addMore =[], folderRoot=null, textareaLib='richText'}) {

	// default props go to prototype but can be overridden in the constructor i.e final source of truth
	this.statusContainer = $(statusContainer);

	this.toRemove = toRemove;

	this.addMore = addMore;

	this.folderRoot = folderRoot ? '/' + folderRoot : '';

	this.textareaLib = textareaLib;

	this.version = 1.3;
}

FA.prototype = {
	// change hyphens to underscore and spaces to hyphens
	nameCleanUp: n => n.trim().replace(/\b(\s)|-/g, function(a,b){if (b == ' ') return '-'; else return '_'}),

	getFields: function (type, callback) {

		return $.get(`${this.folderRoot}/get-fields/${type}`, (res) => callback(res, type));
	},

	// will get db data and send it to be paired against valid fields
	getContents: function (itemToGet, {done}) {

		var h = this, d = h.nameCleanUp(itemToGet);
		
		return $.get(`${h.folderRoot}/get-contents/${d}`, function (contents) {

			try {

				var variables = JSON.parse(contents), {type, url_error} = variables;

				if (url_error !== void(0)) {throw new Error();}

				return h.getFields(type, h.fieldToContent(variables, {done}));
			}
			catch (e) {
				
				new FieldFormat({element: h.statusContainer.html('Invalid page requested.') });
			}
		});
	},

	// used for edit mode. fetches all variables in a template and changes them to input fields
	fieldToContent: function (data, {done}) {
		var h = this;

		return function(fields, actCxt) {

			var newFields = Object.values(JSON.parse(fields)), newData = h.inputify(fields);

			$.each(newFields, function(i,val) {

				newData.find(`[name="${val}"]`).attr('value', data[val]);
			});

			newData = newData[0].outerHTML;

			h.statusContainer.html('Currently editing ' + actCxt);

			new FieldFormat({
				element: $('<div/>').append(h.statusContainer, newData),

				semanticArrs: {exempt: h.toRemove, addMore: h.addMore}
			});

			done();
		};
	},

	fieldsToObj: function(form) {

		var valObj, h = this, regInputs = form.find('input:not([type="submit"]), textarea, select'),

		fileInp = Array.prototype.map.call(form.find('[type="file"]'), function( e) {

			var e = $(e), fieldName = e.attr('name'), val;

			if ( e[0].files.length > 0) val = h.nameCleanUp(e[0].files[0].name);

			else val = e.data('old-file'); // if there's no val, fallback to data-old-file

			return {name: e.attr('name'), value: val};
		});


		valObj = regInputs.serializeArray().concat(fileInp);
		
		return valObj.reduce((a,b)=> {

			if (a[b.name] === void(0)) a[b.name] = b.value;

			else a[b.name] = a[b.name] + ',' + b.value; // input attr multiple?

			return a;
		}, {});
	},

	// @return: Promise
	postToDb: function(form, {alteredStr}) {

		var fd = new FormData(), h = this, url = `${h.folderRoot}/${form.attr('action')}`/* || form.attr('formaction')*/,

		fileInp = form.find('[type="file"]').filter((i,e) => $(e).val().length);


		fd.append('dashboard', JSON.stringify(h.fieldsToObj(form)));

		fileInp.each((i,e) => fd.append($(e).attr('name'), $(e)[0].files[0]) );

		return $.ajax({url, data: fd, contentType: false, method: 'POST', processData: false, success: function(res) { // res should return 1 i.e true

			 	var sucText = (/1$/.test(res) ) ? `Changes successfully made to ${alteredStr}`

			 	: `Operation failed. Reason: ${res}`;

			 	h.statusContainer.html(sucText);

			 	$('html, body').animate({scrollTop:0}, 0 );
			}
		});
	},

	/**
	*
	* @description: set data to form inputs
	*
	* @param {data}:Object||JSON
	*
	* @return jq form
	*/
	inputify: function(data) {

		var form = $('<form/>'), button = $('<button/>').text('update'),

		data = typeof data == 'string' ? JSON.parse(data): Object.keys(data);


		$.each(data, function(i,v) {

			var label = $('<label/>').attr('for', v).text(''+v.replace(/[_]+/g, ' ')+ ':'),

			input = $('<input/>').attr({type: 'text', name: v, id: v});

			form.append(label, $('<br/>'), input, $('<br/>'));
		});

		return form.append(button).attr({enctype: "multipart/form-data"});
	}
};

/*
*
* @description: Helper class for deploying input fields. Overwrites the given component and animates the entrance of new
*
* @params: {element} A HTML component containing the target form to be formatted
*/

function FieldFormat({element=$, semanticArrs={}}) {

	var inputProps = {

		imageInput: ['feature_img', 'author_pic'],

		audioInput: ['audio'],

		textArea: ['content'],

		exempt: ['share_fb', 'share_twitter', 'comments', 'universal_date'],

		addMore: []
	};

	for (var y in semanticArrs)
		if (inputProps.hasOwnProperty(y) && semanticArrs[y].length)
			inputProps[y] = inputProps[y].concat(semanticArrs[y]);

	this.init( element, inputProps);
};

FieldFormat.prototype = {

	// transforms fields in the given form to their semantic input DOM elements based on their name attributes
	init: function (formContainer, inputProps) {

		var newForm = $('form', formContainer), h = this;

		newForm.hide()
		
		.find('input:not([type="submit"]), textarea').each(function(p, w) {
	
			$.each(inputProps, function(i, val) {

				if (val.includes($(w).attr('id'))) h.format($(w), i);
			});
		}).prevObject

		// retain file input value by setting it to data attr
		.find('[type="file"]').each(function (i, e) {
			e = $(e);
		 	
		 	if (e.attr('value') !== void(0) && e.attr('value').length > 0) e.attr('data-old-file', e.attr('value'));
		}).prevObject

		// textarea present?
		.find('textarea');

		$.fn[h.textareaLib].call(newForm);


		$('main > section').html(formContainer);

		newForm.show(75);

		return h;
	},

	format: function (input, action, propArr) {
		
		var behaviours = {
			exempt: function(input) {
				input.siblings(`[for='${input.attr("id")}']`).remove();

				input.remove();
			},

			textArea: function(input) {

				// even though we'll never use this textarea, for semantic sake it replaces the input
				var ta = $('<textarea/>').attr({
					name: input.attr('name'),
					id: input.attr('id')
				}).text( input.val());

				input.replaceWith(ta);
			},

			imageInput: function(input) {
				input.attr({type: 'file', accept: '.jpg,.png,.jpeg'});
			},

			audioInput: function(input) {
				input.attr({type: 'file', accept: '.mp3,.wav,.ogg'});
			},

			addMore: function(input) {

				if (input.val() !== void(0)) {
					// add the label to the first one when the loop is complete

					var inputGroup = [];

					JSON.parse(input.val()).forEach((e) => {

						var input = $('<input/>').attr({
							type: 'text',
							name: val,
							value: e,
						}),

						button = $('<span/>').attr({
							class: 'add-more',

							onClick: () => input.parent().after(
								input.clone().val(''),
								'<br/>'
							)
						}),

						unit = $('<span/>').append(input, button);


						inputGroup.push(unit, $('<br/>'));
					});

					input.after( inputGroup).remove();
				}
			}
		};

		behaviours[action](input, propArr);
	} // close format method
};

return FA;
})();