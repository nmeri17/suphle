
var tmc ="trade-mode", currenHolder = 'FromTo', userBal = {ub: +$('#ub').text()};

$('select,input,label').before('<br/>');

Object.freeze(userBal); $('#ub').remove();


$('main > div h3').on('click', e => {

	var el = $(e.target), [hideElem, showElem] = [$('main section form'), $('main aside form')];
	
	if (!el.hasClass(tmc)) {

		el.siblings().removeClass(tmc).prevObject.addClass(tmc);

		$('main section:has(form)').empty().append(showElem);

		$('main aside').empty().append(hideElem);
	}
});

$(`[name=${currenHolder}]`).on('change', e => {

	var el = $(e.target), ctx = el.children().eq(el[0].selectedIndex),

	price = $(ctx).data('price');

	
	if (price) {
		el.data('price', price)

		.siblings('[name=amount]')
			
			.attr({min: $(ctx).data('min'), max: $(ctx).data('max')})

		.prev().prev().text('Cost of this exchange: '+ price);
	}
});

// validate form
$('main section').on('submit', 'form', function submitF (e) {

	e.preventDefault();

	var self = $(this), base = self.find(`[name=${currenHolder}]`), price = +self.find('[name=amount]').val(),

	mode = $(`main > div .${tmc}`).text(), enoughBal = +userBal.ub > (base.data('price') * price),

	emptySel = self.find('select').filter((t,r) => $(r).val().match(/-{2,}/));


	if (emptySel.length) {alert('Please fill all fields'); return false;}

	if (mode == 'Buy'){

		if (!enoughBal) {alert('Insufficient balance. Please recharge your account'); return false;}

		var n = currenHolder.split(/([A-Z][a-z]+)/g).filter(z => z.length);

		base.val().split(' - ').forEach((w,d) => self.append(
			
			$('<input/>', {type: 'hidden', value: w, name: `currency${n[d]}`})
		));

		base.remove();
	}

	// indicate mode
	self.append(
		$('<input/>', {type: 'hidden', value: mode, name: 'mode'})
	)
	.attr({'method': 'post', action: "/dig-currency/create/transaction"});
	
	$('main section').off('submit', 'form', submitF)

	self.submit();	
});