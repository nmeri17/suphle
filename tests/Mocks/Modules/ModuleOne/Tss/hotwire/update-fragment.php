form {

	repeat: data(ValidationFailureDiffuser::PAYLOAD_KEY);
}

form input[name=title]:attr(value) {

	content: iteration(title);
}

#validation-errors:data[ValidationFailureDiffuser::ERRORS_PRESENCE=null] {

	display: none;
}

#validation-errors ul {

	repeat: data(ValidationFailureDiffuser::ERRORS_PRESENCE);
}

#validation-errors ul li {

	content: iteration(message);
}

#from-handler {

	content: data(data.id);
}