"use strict";
$(function () {
	// Display selected file(s) while using the Custom File Input
	// https://github.com/twbs/bootstrap/issues/23994#issuecomment-408644190
	$('.custom-file-input').on("change", function() {
		var fileName = $(this).val().split("\\").pop();
		var label = $(this).siblings(".custom-file-label");

		if (label.data("default-title") === undefined) {
			label.data("default-title", label.html());
		}

		if (fileName === "") {
			label.removeClass("selected").html(label.data('default-title'));
		} else {
			label.addClass("selected").html(fileName);
		}
	});

	// Display tooltips
	$("[data-toggle='tooltip']").tooltip();
});
