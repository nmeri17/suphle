form {

	repeat: data(payload_storage);
}

form input[name=title]:attr(value) {

	content: iteration(title);
}

#validation-errors:data[errors=null] {

	display: none;
}

#validation-errors ul {

	repeat: data(errors);
}

#validation-errors ul li {

	content: iteration(message);
}

#from-handler {

	content: data(data.id);
}