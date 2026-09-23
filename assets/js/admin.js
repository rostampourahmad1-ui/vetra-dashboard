(function ($) {
	"use strict";

	$(function () {
		if ($.fn.wpColorPicker) {
			$(".vtd-color").wpColorPicker();
		}

		$(document).on("click", ".vtd-media-select", function (event) {
			event.preventDefault();
			var button = $(this);
			var input = button.siblings("input[type=text]");
			var frame = wp.media({
				title: (window.VTD_ADMIN && VTD_ADMIN.i18n.selectImage) || "Select",
				button: { text: (window.VTD_ADMIN && VTD_ADMIN.i18n.useImage) || "Use" },
				multiple: false
			});
			frame.on("select", function () {
				var attachment = frame.state().get("selection").first().toJSON();
				input.val(attachment.url).trigger("change");
			});
			frame.open();
		});

		$(document).on("click", ".vtd-repeater-add", function () {
			var repeater = $(this).closest(".vtd-repeater");
			var rows = repeater.find(".vtd-repeater-rows");
			var index = rows.find(".vtd-repeater-row").length;
			var name = repeater.data("repeater-name") || "profile_custom_fields";
			var template = rows.find(".vtd-repeater-row").first();
			var newRow;
			if (template.length) {
				newRow = template.clone();
				newRow.find("input, select, textarea").each(function () {
					var el = $(this);
					var fieldName = el.attr("name") || "";
					fieldName = fieldName.replace(/\[\d+\]/, "[" + index + "]");
					el.attr("name", fieldName);
					if (el.is("input[type=text], textarea")) {
						el.val("");
					}
					if (el.is("input[type=checkbox]")) {
						el.prop("checked", false);
					}
				});
			} else {
				newRow = $('<div class="vtd-repeater-row">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][slug]" placeholder="slug">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="label">' +
					'<select name="vetra_settings[' + name + "][" + index + '][type]"><option value="text">text</option><option value="email">email</option><option value="tel">tel</option><option value="number">number</option><option value="date">date</option><option value="url">url</option><option value="textarea">textarea</option><option value="select">select</option></select>' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][options]" placeholder="option1,option2">' +
					'<label><input type="checkbox" name="vetra_settings[' + name + "][" + index + '][required]" value="1"> Required</label>' +
					'<button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			}
			rows.append(newRow);
		});

		$(document).on("click", ".vtd-repeater-remove", function () {
			$(this).closest(".vtd-repeater-row").remove();
		});

		$(document).on("click", ".vtd-confirm", function (event) {
			if (!window.confirm($(this).data("message") || "Are you sure?")) {
				event.preventDefault();
			}
		});
	});
})(jQuery);
