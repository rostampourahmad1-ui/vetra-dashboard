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
				title: (window.VTD_ADMIN && VTD_ADMIN.i18n.selectImage) || "انتخاب تصویر",
				button: { text: (window.VTD_ADMIN && VTD_ADMIN.i18n.useImage) || "استفاده از این فایل" },
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
			var kind = repeater.data("repeater") || "profile_fields";
			var name = repeater.data("repeater-name") || (kind === "shortcuts" ? "dashboard_shortcuts" : "profile_custom_fields");
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
			} else if (kind === "shortcuts") {
				newRow = $('<div class="vtd-repeater-row">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="عنوان میانبر">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][icon]" placeholder="نامک آیکون">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][url]" placeholder="نشانی پیوند">' +
					'<button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			} else {
				newRow = $('<div class="vtd-repeater-row">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][slug]" placeholder="شناسه یکتا">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="عنوان فیلد">' +
					'<select name="vetra_settings[' + name + "][" + index + '][type]"><option value="text">متن</option><option value="email">ایمیل</option><option value="tel">تلفن</option><option value="number">عدد</option><option value="date">تاریخ شمسی</option><option value="url">پیوند</option><option value="textarea">متن چندخطی</option><option value="select">فهرست انتخاب</option></select>' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][options]" placeholder="گزینه۱,گزینه۲">' +
					'<label><input type="checkbox" name="vetra_settings[' + name + "][" + index + '][required]" value="1"> الزامی</label>' +
					'<button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			}
			rows.append(newRow);
		});

		$(document).on("click", ".vtd-repeater-remove", function () {
			$(this).closest(".vtd-repeater-row").remove();
		});

		$(document).on("click", ".vtd-confirm", function (event) {
			if (!window.confirm($(this).data("message") || "آیا مطمئن هستید؟")) {
				event.preventDefault();
			}
		});
	});
})(jQuery);
