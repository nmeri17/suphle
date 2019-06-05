var refTagger = {
		settings: {
			bibleVersion: "NKJV",			
			tooltipStyle: "dark"
		}
	},

	fonts = ['https://fonts.googleapis.com/css?family=Josefin+Slab|Khula|Imprima',
	'https://cdnjs.cloudflare.com/ajax/libs/foundicons/3.0.0/foundation-icons.min.css',
	'https://cdn.linearicons.com/free/1.0.0/icon-font.min.css'],

	scripts = ['//api.reftagger.com/v2/RefTagger.js'];

	$.each(fonts, function(i, e) {

		$('title').before($('<link/>').attr({rel: 'stylesheet', href: e}));
	});

	$.each(scripts, function(i, e) {

		$('link:last').before($('<script/>').attr({async: true, src: e}));
	});