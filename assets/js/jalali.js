(function (window, document) {
	"use strict";

	var breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
	var monthNames = ["فروردین", "اردیبهشت", "خرداد", "تیر", "مرداد", "شهریور", "مهر", "آبان", "آذر", "دی", "بهمن", "اسفند"];
	var weekDays = ["ش", "ی", "د", "س", "چ", "پ", "ج"];
	var faDigits = "۰۱۲۳۴۵۶۷۸۹";

	function div(a, b) { return Math.trunc(a / b); }
	function mod(a, b) { return a - div(a, b) * b; }
	function toAscii(value) {
		return String(value || "").replace(/[۰-۹٠-٩]/g, function (digit) {
			var code = digit.charCodeAt(0);
			return String(code >= 1776 && code <= 1785 ? code - 1776 : code - 1632);
		});
	}
	function toPersian(value) {
		return String(value).replace(/\d/g, function (digit) { return faDigits.charAt(parseInt(digit, 10)); });
	}
	function jalCal(jy) {
		var gy = jy + 621, leapJ = -14, jp = breaks[0], jm = 0, jump = 0, n = 0;
		for (var i = 1; i < breaks.length; i++) {
			jm = breaks[i];
			jump = jm - jp;
			if (jy < jm) { break; }
			leapJ += div(jump, 33) * 8 + div(mod(jump, 33), 4);
			jp = jm;
		}
		n = jy - jp;
		leapJ += div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
		if (mod(jump, 33) === 4 && jump - n === 4) { leapJ += 1; }
		var leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
		var march = 20 + leapJ - leapG;
		if (jump - n < 6) { n = n - jump + div(jump + 4, 33) * 33; }
		var leap = mod(mod(n + 1, 33) - 1, 4);
		if (leap === -1) { leap = 4; }
		return { gy: gy, march: march, leap: leap };
	}
	function g2d(gy, gm, gd) {
		var d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4) + div(153 * mod(gm + 9, 12) + 2, 5) + gd - 34840408;
		return d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
	}
	function d2g(jdn) {
		var j = 4 * jdn + 139361631;
		j += div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
		var i = div(mod(j, 1461), 4) * 5 + 308;
		var gd = div(mod(i, 153), 5) + 1;
		var gm = mod(div(i, 153), 12) + 1;
		var gy = div(j, 1461) - 100100 + div(8 - gm, 6);
		return { year: gy, month: gm, day: gd };
	}
	function toGregorian(jy, jm, jd) {
		if (jy < 1200 || jy > 1600 || jm < 1 || jm > 12 || jd < 1 || jd > (jm <= 6 ? 31 : (jm <= 11 ? 30 : 30))) { return null; }
		var cal = jalCal(jy);
		if (jm === 12 && jd === 30 && cal.leap !== 0) { return null; }
		var jdn = g2d(cal.gy, 3, cal.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
		return d2g(jdn);
	}
	function toJalali(gy, gm, gd) {
		var jdn = g2d(gy, gm, gd);
		var jy = gy - 621;
		var cal = jalCal(jy);
		var k = jdn - g2d(cal.gy, 3, cal.march);
		var jm, jd;
		if (k >= 0) {
			if (k <= 185) { jm = 1 + div(k, 31); jd = mod(k, 31) + 1; return { year: jy, month: jm, day: jd }; }
			k -= 186;
		} else {
			jy -= 1;
			k += 179;
			if (cal.leap === 1) { k += 1; }
		}
		jm = 7 + div(k, 30);
		jd = mod(k, 30) + 1;
		return { year: jy, month: jm, day: jd };
	}
	function parseJalali(value) {
		var normalized = toAscii(value).trim().replace(/[.\-]/g, "/");
		var match = normalized.match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
		if (!match) { return normalized ? null : ""; }
		var jy = parseInt(match[1], 10), jm = parseInt(match[2], 10), jd = parseInt(match[3], 10);
		var result = toGregorian(jy, jm, jd);
		return result ? String(result.year).padStart(4, "0") + "-" + String(result.month).padStart(2, "0") + "-" + String(result.day).padStart(2, "0") : null;
	}
	function parseGregorian(iso) {
		var match = String(iso).match(/^(\d{4})-(\d{2})-(\d{2})$/);
		if (!match) { return null; }
		return toJalali(parseInt(match[1], 10), parseInt(match[2], 10), parseInt(match[3], 10));
	}
	function monthDays(year, month) {
		if (month <= 6) { return 31; }
		if (month <= 11) { return 30; }
		return jalCal(year).leap === 0 ? 30 : 29;
	}
	function currentJalali() {
		var now = new Date();
		return toJalali(now.getFullYear(), now.getMonth() + 1, now.getDate());
	}
	function closeCalendar(field) {
		var calendar = field.querySelector(".vtd-jalali-calendar");
		if (calendar) { calendar.remove(); }
	}
	function renderCalendar(field, year, month) {
		closeCalendar(field);
		var input = field.querySelector("[data-vtd-jalali-date]");
		var popup = document.createElement("div");
		popup.className = "vtd-jalali-calendar";
		popup.setAttribute("role", "dialog");
		popup.setAttribute("aria-label", "انتخاب تاریخ شمسی");
		var header = document.createElement("div");
		header.className = "vtd-jalali-calendar-head";
		header.innerHTML = '<button type="button" data-month-shift="-1" aria-label="ماه قبل">‹</button><strong>' + monthNames[month - 1] + " " + toPersian(year) + '</strong><button type="button" data-month-shift="1" aria-label="ماه بعد">›</button>';
		popup.appendChild(header);
		var grid = document.createElement("div");
		grid.className = "vtd-jalali-calendar-grid";
		weekDays.forEach(function (day) {
			var weekday = document.createElement("span");
			weekday.className = "is-weekday";
			weekday.textContent = day;
			grid.appendChild(weekday);
		});
		var first = toGregorian(year, month, 1);
		var offset = (new Date(Date.UTC(first.year, first.month - 1, first.day)).getUTCDay() + 1) % 7;
		for (var empty = 0; empty < offset; empty++) {
			var spacer = document.createElement("span");
			spacer.className = "is-empty";
			grid.appendChild(spacer);
		}
		for (var dayNumber = 1; dayNumber <= monthDays(year, month); dayNumber++) {
			var dayButton = document.createElement("button");
			dayButton.type = "button";
			dayButton.textContent = toPersian(dayNumber);
			dayButton.setAttribute("data-jalali-day", dayNumber);
			grid.appendChild(dayButton);
		}
		popup.appendChild(grid);
		field.appendChild(popup);
		popup.addEventListener("click", function (event) {
			var shift = event.target.getAttribute("data-month-shift");
			var selected = event.target.getAttribute("data-jalali-day");
			if (shift) {
				var next = month + parseInt(shift, 10);
				var nextYear = year;
				if (next < 1) { next = 12; nextYear -= 1; }
				if (next > 12) { next = 1; nextYear += 1; }
				renderCalendar(field, nextYear, next);
			} else if (selected) {
				input.value = toPersian(String(year).padStart(4, "0") + "/" + String(month).padStart(2, "0") + "/" + String(parseInt(selected, 10)).padStart(2, "0"));
				input.dispatchEvent(new Event("input", { bubbles: true }));
				input.dispatchEvent(new Event("change", { bubbles: true }));
				closeCalendar(field);
			}
		});
	}
	function init() {
		document.querySelectorAll("[data-vtd-jalali-date]").forEach(function (input) {
			if (input.dataset.jalaliReady) { return; }
			input.dataset.jalaliReady = "1";
			var field = document.createElement("div");
			field.className = "vtd-jalali-date-field";
			input.parentNode.insertBefore(field, input);
			field.appendChild(input);
			var button = document.createElement("button");
			button.type = "button";
			button.className = "vtd-jalali-calendar-toggle";
			button.setAttribute("aria-label", "بازکردن تقویم شمسی");
			button.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4M8 3v4M3 10h18"></path></svg>';
			field.appendChild(button);
			button.addEventListener("click", function () {
				if (field.querySelector(".vtd-jalali-calendar")) { closeCalendar(field); return; }
				var parsed = toAscii(input.value).trim().replace(/[.\-]/g, "/").match(/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/);
				var date = parsed ? { year: parseInt(parsed[1], 10), month: parseInt(parsed[2], 10) } : currentJalali();
				renderCalendar(field, date.year, date.month);
			});
			input.addEventListener("focus", function () {
				field.classList.add("is-focused");
			});
			input.addEventListener("blur", function () {
				field.classList.remove("is-focused");
			});
		});
		document.addEventListener("click", function (event) {
			if (!event.target.closest(".vtd-jalali-date-field")) {
				document.querySelectorAll(".vtd-jalali-date-field").forEach(function (field) { closeCalendar(field); });
			}
		});
	}

	window.VTDJalali = { parse: parseJalali, toGregorian: toGregorian, toJalali: toJalali, toPersian: toPersian, init: init };
	document.addEventListener("DOMContentLoaded", init);
})(window, document);
