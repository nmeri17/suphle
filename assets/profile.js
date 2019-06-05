var statusContainer = '#operation-info', displayContainer = $('main section');


$(document).ready(function() {
	
	// section height to viewport
	var totalHeader = $('header')[0].offsetHeight + $('header')[0].getBoundingClientRect().top,

	footerHeight = $('footer')[0].offsetHeight, sdni = 'active-side-nav',

	maxHeight = innerHeight - (totalHeader + footerHeight) + 50;
	

	displayContainer.css('min-height', maxHeight)
	
	// initialize with transactions comp
	displayContainer.append($('#init-contents').children().first().clone());


	$('#init-contents').hide();


	$('main > nav a').click(e => displayContainer.html([

		$(statusContainer), $('#init-contents').children().eq(

			$(e.target).siblings().removeClass(sdni) // dethrone previous holder
			.prevObject.addClass(sdni).index()
		).clone()
	]))
	.first().addClass(sdni); // init

	$('main > section').on('submit', 'form', function(e) {

		e.preventDefault();

		var form = $(e.target), alteredItem = $('main > nav a.'+sdni).text(),

		thisButt = form.find('[type=submit]'), prevButtText = thisButt.val();

		
		thisButt.val('loading...').css('width', 'auto').attr('disabled', true);

		new FormActivity({statusContainer, folderRoot: 'dig-currency'})

		.postToDb(form, {alteredItem})

		.then(res => {
			setTimeout(() => $('main section ' + statusContainer).hide(400).empty(), 10000);
		});

		thisButt.val(prevButtText).removeAttr('disabled');
	});
});