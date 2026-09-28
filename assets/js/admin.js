(function ($) {
	"use strict";

	$(function () {
		function updateMenuBuilderRow(row) {
			var type = row.find("[data-vtd-menu-type]").val();
			row.find("[data-menu-field]").each(function () {
				var field = $(this);
				field.toggle(field.data("menu-field") === type);
			});
		}

		$(".vtd-menu-builder-row").each(function () { updateMenuBuilderRow($(this)); });
		$(document).on("change", "[data-vtd-menu-type]", function () {
			updateMenuBuilderRow($(this).closest(".vtd-menu-builder-row"));
		});

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
			var kind = repeater.data("repeater") || "profile_fields";
			if (kind === "social_links") { return; }
			var index = 0;
			rows.find(".vtd-repeater-row:not(.vtd-repeater-template)").each(function () {
				var firstName = $(this).find("[name]").first().attr("name") || "";
				var match = firstName.match(/\[(\d+)\]\[/);
				if (match) { index = Math.max(index, parseInt(match[1], 10) + 1); }
			});
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
					if (el.is("input[type=text], input[type=url], textarea")) {
						el.val("");
					}
					if (el.is("select")) {
						el.prop("selectedIndex", 0);
					}
					if (el.is("input[type=checkbox]")) {
						el.prop("checked", kind === "dashboard_cards" ? true : (kind === "profile_fields" ? false : el.is(":checked")));
					}
				});
				newRow.removeClass("vtd-repeater-template").show();
			} else if (kind === "shortcuts") {
				newRow = $('<div class="vtd-repeater-row">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="عنوان میانبر">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][icon]" placeholder="نامک آیکون">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][url]" placeholder="نشانی پیوند">' +
					'<button type="button" class="button vtd-repeater-move-up">↑</button><button type="button" class="button vtd-repeater-move-down">↓</button>' +
					'<button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			} else if (kind === "dashboard_cards") {
				newRow = $('<div class="vtd-repeater-row vtd-dashboard-card-row">' +
					'<select name="vetra_settings[' + name + "][" + index + '][metric]"><option value="open_tickets">تیکت‌های باز</option><option value="total_tickets">کل تیکت‌ها</option><option value="unread_notifications">اعلان‌های خوانده‌نشده</option><option value="wallet_balance">موجودی کیف پول</option><option value="comments">دیدگاه‌ها</option><option value="polls">نظرسنجی‌ها</option><option value="custom">کارت سفارشی</option></select>' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="عنوان کارت">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][value]" placeholder="مقدار کارت سفارشی">' +
					'<input type="text" name="vetra_settings[' + name + "][" + index + '][icon]" placeholder="نام آیکون">' +
					'<select name="vetra_settings[' + name + "][" + index + '][tone]"><option value="primary">اصلی</option><option value="accent">تأکیدی</option><option value="success">موفق</option><option value="muted">خنثی</option></select>' +
					'<input type="url" name="vetra_settings[' + name + "][" + index + '][url]" placeholder="پیوند (اختیاری)">' +
					'<label><input type="checkbox" name="vetra_settings[' + name + "][" + index + '][enabled]" value="1" checked> نمایش</label>' +
					'<button type="button" class="button vtd-repeater-move-up">↑</button><button type="button" class="button vtd-repeater-move-down">↓</button><button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			} else if (kind === "faq_items") {
				newRow = $('<div class="vtd-repeater-row"><input type="text" name="vetra_settings[' + name + "][" + index + '][question]" placeholder="پرسش"><textarea name="vetra_settings[' + name + "][" + index + '][answer]" rows="2" placeholder="پاسخ"></textarea><input type="url" name="vetra_settings[' + name + "][" + index + '][url]" placeholder="پیوند آموزش (اختیاری)"><button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			} else if (kind === "faq_links") {
				newRow = $('<div class="vtd-repeater-row"><input type="text" name="vetra_settings[' + name + "][" + index + '][label]" placeholder="عنوان آموزش"><input type="url" name="vetra_settings[' + name + "][" + index + '][url]" placeholder="نشانی پیوند"><button type="button" class="button vtd-repeater-remove">&times;</button></div>');
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
			if (kind === "panel_menu") {
				updateMenuBuilderRow(newRow);
			}
		});

		$(document).on("click", ".vtd-repeater-remove", function () {
			var row = $(this).closest(".vtd-repeater-row");
			var repeater = row.closest(".vtd-repeater");
			if (repeater.hasClass("vtd-menu-builder") && repeater.find(".vtd-repeater-row").length === 1) {
				row.find("input[type=text], input[type=url], textarea").val("");
				row.find("input[type=checkbox]").prop("checked", true);
				row.find("select").prop("selectedIndex", 0);
				updateMenuBuilderRow(row);
				return;
			}
			row.remove();
		});

		$(document).on("click", ".vtd-repeater-move-up", function () {
			var row = $(this).closest(".vtd-repeater-row");
			row.prev(".vtd-repeater-row").before(row);
			renumberRepeater(row.closest(".vtd-repeater"));
		});
		$(document).on("click", ".vtd-repeater-move-down", function () {
			var row = $(this).closest(".vtd-repeater-row");
			row.next(".vtd-repeater-row").after(row);
			renumberRepeater(row.closest(".vtd-repeater"));
		});

		function renumberRepeater(repeater) {
			repeater.find(".vtd-repeater-rows > .vtd-repeater-row").each(function (index) {
				$(this).find("[name]").each(function () {
					var input = $(this);
					input.attr("name", (input.attr("name") || "").replace(/\[\d+\](?=\[)/, "[" + index + "]"));
				});
			});
		}

		$(document).on("click", ".vtd-repeater[data-repeater='social_links'] .vtd-repeater-add", function () {
			var repeater = $(this).closest(".vtd-repeater");
			var rows = repeater.find(".vtd-repeater-rows");
			var index = 0;
			rows.find(".vtd-repeater-row:not(.vtd-repeater-template)").each(function () {
				var firstName = $(this).find("[name]").first().attr("name") || "";
				var match = firstName.match(/\[(\d+)\]\[/);
				if (match) { index = Math.max(index, parseInt(match[1], 10) + 1); }
			});
			var name = repeater.data("repeater-name") || "social_links";
			var newRow = $('<div class="vtd-repeater-row vtd-social-row">' +
				'<select name="vetra_settings[' + name + '][' + index + '][platform]" class="vtd-social-platform"><option value="instagram">اینستاگرام</option><option value="telegram">تلگرام</option><option value="whatsapp">واتساپ</option><option value="twitter">ایکس</option><option value="facebook">فیسبوک</option><option value="linkedin">لینکدین</option><option value="youtube">یوتیوب</option><option value="aparat">آپارات</option><option value="rubika">روبیکا</option><option value="bale">بله</option><option value="eitaa">ایتا</option><option value="custom">سایر</option></select>' +
				'<input type="text" name="vetra_settings[' + name + '][' + index + '][label]" placeholder="عنوان نمایش">' +
				'<input type="url" name="vetra_settings[' + name + '][' + index + '][url]" placeholder="پیوند یا شناسه">' +
				'<input type="url" name="vetra_settings[' + name + '][' + index + '][icon_url]" placeholder="نشانی آیکون سفارشی (اختیاری)">' +
				'<input type="text" name="vetra_settings[' + name + '][' + index + '][color]" class="vtd-color" placeholder="رنگ">' +
				'<label><input type="checkbox" name="vetra_settings[' + name + '][' + index + '][enabled]" value="1" checked> نمایش</label>' +
				'<button type="button" class="button vtd-repeater-move-up" aria-label="انتقال به بالا">↑</button><button type="button" class="button vtd-repeater-move-down" aria-label="انتقال به پایین">↓</button>' +
				'<button type="button" class="button vtd-repeater-remove">&times;</button></div>');
			rows.append(newRow);
			if ($.fn.wpColorPicker) { newRow.find(".vtd-color").wpColorPicker(); }
		});

		$(document).on("click", ".vtd-confirm", function (event) {
			if (!window.confirm($(this).data("message") || "آیا مطمئن هستید؟")) {
				event.preventDefault();
			}
		});

		$(document).on("change", "[data-vtd-admin-change-stage]", function () {
			var row = $(this).closest(".vtd-change-admin-row");
			row.find("[data-vtd-change-admin-note]").prop("required", $(this).val() === "request_docs" || $(this).val() === "request_physical_docs");
		});
	});
})(jQuery);
