var editQuery = (q, param, newVal) => {

		return q.split('&').map(p => {
			var kv = p.split('='); if (kv[0] == param) kv[1] = newVal;

			return kv.join('=');
		}).join('&');
	}, idToNameMap,

	headerToQK = {},

	/* @description: assigns a new action to the row's action after its checkbox option changes
	* @param: {selInd}:Number index of the column the select lies on
	* @return: void
	*/
	tblSelectOption = ( row, selInd) => {
			
		var cbBox = row.find('[type=checkbox]'), [href, dataStr] = cbBox.attr('formaction').split('?'),

		sel = row.find('select'), colName = $('main section th').eq(selInd).text();

		cbBox.attr('formaction', href +'?'+ 
			editQuery(dataStr, headerToQK[colName] || colName, sel[0].value)
		);
	},

	loadAdmin = () => {
		var base = 'https://', scrpts = [
		'raw.githubusercontent.com/webfashionist/RichText/master/src/richtext.min.css',

		'raw.githubusercontent.com/webfashionist/RichText/master/src/jquery.richtext.min.js',

		'stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',

		'cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.13/featherlight.min.css',

		'cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.13/featherlight.min.css'
		].map(rsx => {

			if (/\.js$/.test(rsx)) return $.getScript(base + rsx);

			return (() => new Promise(suc => {

				suc(); // promise is considered resolved whenever its success hander is called
				
				return $('title').before(
					$('<link/>', { rel:"stylesheet", type:"text/css", href:base + rsx })
				)
			})) (() => true);
		});

		return Promise.all(scrpts); // each value must not necessarily return a promise. but each must be the result of a promise resolved internally
	},

	templXtract = target => $('<div/>', {html: $("template")[0].innerHTML}).find(target);


	loadAdmin().then(resolArr => {

		$('textarea').richText();

		$('.expand a').each((b,n) => $(n).attr('data-featherlight', $(n).attr('href')));
	});

	// assign a value to the template in the checkbox action and post it
	displayContainer.on('blur', '[contenteditable]:not(.richText-editor)', function (e) {

		// to reverse failed requests, store prev value in a focus handler/global cache for contenteditables
		
		var el = $(e.target), cbBox = el.siblings(':last').find('input'),

		[href, dataStr] = cbBox.attr('formaction').split('?'),

		colName = $('main section th').eq(el.index()).text(),

		setCbBox = cbBox.attr('type') == 'checkbox' ? editQuery(dataStr, 'visible', +cbBox[0].checked): dataStr;


		dataStr = editQuery(setCbBox, headerToQK[colName] || colName, el.text());
		
		// submit only if save button was clicked, or in modify, on alteration
		if (el.parents('[data-init]').children(':disabled').length) $.post(href, dataStr);

		// assign to defer POSTing
		else cbBox.attr('formaction', href+ '?'+ dataStr);
	});

	// table new rows
	displayContainer.on('click', 'button:contains(Create new)', function (e) {

		var el = $(e.target), table = el.parent().siblings('table'), nRow = table.find('tr:last').clone();


		el.siblings('button').removeAttr('disabled');

		nRow.appendTo(table).children().css({border: '1px solid #ccc'});
	});


	// save new row
	displayContainer.on('click', 'button:contains(Create new) + button', function (e) {

		var el = $(e.target), table = el.parent().siblings('table'), lRow = table.find('tr:last'),

		sel = lRow.find('select');

	});

	displayContainer.on('search', '[type="search"]', e => {

		var toGet = $(e.target).val(), toRemove = ["type", "og_url", 'active_index'];

		new FormActivity({statusContainer, toRemove, folderRoot: null, textareaLib: 'richText'})

		.getContents(
			toGet, {
				done: () => $('main section form').attr('action', 'modify/page')

				.prepend($('<input/>', {type: 'hidden', value: toGet, name: 'name'}))
		});
	});