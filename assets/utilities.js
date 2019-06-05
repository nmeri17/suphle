$(document).ready(function() {

	var displayContainer = $('main section'), sdni = 'active-side-nav',

	statusContainer = $('#operation-info'), allLis = $('details li'),

	qToObj = str => str.split('&').reduce((o, p) => {

		var [k,v] = p.split('=');

		o[k] = v; return o}, {}),

	electric = initComp = 'electric', pbParams = qToObj(location.search.substring(1)),

	getComp = compName => $(`#init-contents [data-init=${compName}]`).clone();


	allLis.on('click', e => {
		var el = $(e.target);

		allLis.removeClass(sdni).filter(el).addClass(sdni);

		statusContainer.text(el.text());
	});

	
	if (pbParams['cat'] !== void(0)) {

		initComp = pbParams.cat;

		allLis.filter(`:contains(${pbParams.mode})`).click()

		.parents('details').attr('open', true);
	}

	else $('details:first').attr('open', true);

	
	displayContainer.append([$('<br/>'), getComp(initComp) ]);


	$('#init-contents').hide();


	$('summary').on('click', function(e) {

		var el = $(e.target), comps = [electric, 'mcp', 'mcp', 'waec'];

		displayContainer.html([statusContainer, getComp(comps[el.parent().index()]) ]);

		statusContainer.text(el.siblings('ol').find('li:first').text())
	});


	$('details:first li').on('click', function(e) {
		var el = $(e.target);

		if (el.index() == 1) {

			var sels = $('<div/>', {html: $("template")[0].innerHTML}).find('select');

			$('[name=customerphone]').after(sels)
		}

		$('[name=location]').val(el.text().split()[0]);
	});

	$('.pull_up').on('click', e => {

		e.preventDefault();

		var [, quer] = $(e.target).attr('href').split('?');

		$('[name=multi_mode]').val(pbParams.mode);

		$('[name=product_code]').val(qToObj(quer).code);

		displayContainer.html([statusContainer, getComp('mcf') ]);
	});
});