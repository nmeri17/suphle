var editQuery = (q, param, newVal) => {

		return q.split('&').map(p => {
			var kv = p.split('='); if (kv[0] == param) kv[1] = newVal;

			return kv.join('=');
		}).join('&');
	}, idToNameMap,

	headerToQK = {'min amount': 'minimumTransactionCost', 'max amount': 'maximumTransactionCost', 'price today': 'priceToday', 'value': 'packageValue', 'validity period': 'validity', 'destination': 'currencyTo', 'curr. name': 'currencyFrom', 'approval mode': 'approvalMode'},

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

	populateUserMap = function  () {
		$.get('/ids-to-full-name/all', res => {

			try {
				idToNameMap = JSON.parse(res);

				for (var id in idToNameMap.others) $(`td:contains(${id})`).text((i,x) => idToNameMap.others[x]);
			}
			catch (e) { populateUserMap();}
		});
	},

	loadAdmin = () => {
		var base = '/assets/', scrpts = ['richtext.min.css', 'jquery.richtext.min.js', 'font-awesome.min.css', 'featherlight.min.js', 'featherlight.min.css'].map(rsx => {

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


	populateUserMap();

	loadAdmin().then(resolArr => {

		$('textarea').richText();

		$('.expand a').each((b,n) => $(n).attr('data-featherlight', $(n).attr('href')));
	});

	// panels with create button for new rows
	['coupons', 'vtu', 'currencies'].forEach(n => $(`[data-init=${n}] table`).before(templXtract('.new-button')))

	displayContainer.on('change', '[type=checkbox]', function (e) {
		
		var el = $(e.target), [href, dataStr] = el.attr('formaction').split('?');


		dataStr = editQuery( dataStr,
			$('main section th').eq(el.parent().index()).text(),
			+el[0].checked
		);
		
		$.post(href, dataStr, res => {

			if (/1$/.test(res)) {

				// if we're in this comp
				if ($('[data-init="transactions"]').find(el).length) // set initiator name

					el.parent().next().text(idToNameMap.requester);
			}

			else {
				if (el[0].checked) el.removeAttr('checked'); else el.attr('checked', '');
			}
		});
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

	// account man
	displayContainer.on('change', '[data-init="users"] select', function (e) {

		var el = $(e.target), fuName = el.parent().siblings().first().text(), all = idToNameMap.others,

		getId = n => {

			for (var c in all) if (all[c] == n) return c;
		};

		$.post(el.data('action'), 'role=' + el.val() + '&userId='+ getId(fuName), res => {
			if (/1$/.test(res)) el.blur();
		});
	});

	// table new rows
	displayContainer.on('click', 'button:contains(Create new)', function (e) {

		var el = $(e.target), table = el.parent().siblings('table'), nRow = table.find('tr:last').clone();


		el.siblings('button').removeAttr('disabled');

		if ($('[data-init="coupons"]').find(el).length) {

			nRow.children().empty().eq(1).html(templXtract('.coupons'))

			nRow.children().eq(2).attr('contenteditable', 'true');

			nRow.children(':last').append(
				$('<input/>', {type: 'hidden', formaction: "path?mode=&amount="}) // conform for contenteditable handler
			)
		}

		// update ids 
		if ($('[data-init="vtu"]').find(el).length) {

			nRow.children(':not(:last):not(:eq(1))').empty();

			nRow.children(':last').children().each((j,h) => {

				h = $(h); h.attr('id', (ind,currId) => {
				
					if (currId !== void(0)) return currId.replace(/\d$/, m => +m+1);
	
					h.attr('for', h.prev().attr('id'));
				});
			});
		}

		if ($('[data-init="currencies"]').find(el).length) {

			nRow.children(':not(:last):not(:nth-child(6))').empty().attr('contenteditable', 'true');
		}

		nRow.appendTo(table).children().css({border: '1px solid #ccc'});
	});


	// save new row
	displayContainer.on('click', 'button:contains(Create new) + button', function (e) {

		var el = $(e.target), table = el.parent().siblings('table'), lRow = table.find('tr:last'),

		sel = lRow.find('select');


		el.attr('disabled', ''); // prevent multiple save clicks

		if ($('[data-init="coupons"]').find(el).length) {

			var mode = sel.val(), amount = lRow.children().eq(2).text();

			$.post('/create/coupon', `mode=${mode}&amount=${amount}`, function (res) {

				var nVals = JSON.parse(res);

				lRow.find('td:first').text(nVals.code); lRow.find('td:last').text(nVals.time);
			});
		}

		// same behaviour for either panel
		else if (['currencies', 'vtu'].some(l => $(`[data-init=${l}]`).find(el).length)) {

			// action create new
			var cbBox = lRow.find('td:last input'), nAction = cbBox.attr('formaction').replace('modify', 'create');
		debugger

			cbBox.attr('formaction', nAction);
			
			tblSelectOption( lRow, sel.parent().index());

			table.find('[contenteditable]:last').trigger('blur'); // submit
		}
	});

	// vtu network modify
	displayContainer.on('change', '[data-init="vtu"] select', function (e) {

		var td = $(e.target).parent();
			
		tblSelectOption( td.parent(), td.index());

		td.next().trigger('blur');
	});

	displayContainer.on('search', '[type="search"]', e => {

		var toGet = $(e.target).val(), toRemove = ["type", "og_url", 'active_index', 'active_utilities', 'active_trade', 'active_buy_data', 'active_profile'];

		new FormActivity({statusContainer, toRemove, folderRoot: 'dig-currency'})

		.getContents(
			toGet, {
				done: () => $('main section form').attr('action', 'modify/page')

				.prepend($('<input/>', {type: 'hidden', value: toGet, name: 'name'}))
		});
	});