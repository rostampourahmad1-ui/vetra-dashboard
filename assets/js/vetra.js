(function () {
	"use strict";

	var api = (window.VTD && window.VTD.rest) || "";
	var nonce = (window.VTD && window.VTD.nonce) || "";
	var i18n = (window.VTD && window.VTD.i18n) || {};

	function request(path, options) {
		options = options || {};
		var headers = options.headers || {};
		headers["X-WP-Nonce"] = nonce;
		if (!(options.body instanceof FormData) && options.data) {
			headers["Content-Type"] = "application/json";
			options.body = JSON.stringify(options.data);
		}
		return fetch(api + path, {
			method: options.method || "POST",
			headers: headers,
			body: options.body,
			credentials: "same-origin"
		}).then(function (response) {
			return response.json().catch(function () {
				return {};
			});
		});
	}

	function setMessage(el, message, ok) {
		if (!el) {
			return;
		}
		el.textContent = message;
		el.classList.remove("is-ok", "is-error");
		el.classList.add(ok ? "is-ok" : "is-error");
	}

	function formData(form) {
		var data = {};
		new FormData(form).forEach(function (value, key) {
			if (key.slice(-2) === "[]") {
				key = key.slice(0, -2);
				data[key] = data[key] || [];
				data[key].push(value);
			} else if (key in data) {
				if (!Array.isArray(data[key])) {
					data[key] = [data[key]];
				}
				data[key].push(value);
			} else {
				data[key] = value;
			}
		});
		form.querySelectorAll("[data-vtd-jalali-date]").forEach(function (field) {
			var iso = window.VTDJalali ? window.VTDJalali.parse(field.value) : null;
			if ("" === field.value.trim()) {
				data[field.name] = "";
			} else if (iso) {
				data[field.name] = iso;
			}
		});
		return data;
	}

	function jalaliDatesValid(form) {
		var valid = true;
		form.querySelectorAll("[data-vtd-jalali-date]").forEach(function (field) {
			var empty = "" === field.value.trim();
			var parsed = window.VTDJalali ? window.VTDJalali.parse(field.value) : null;
			var fieldValid = empty ? !field.required : !!parsed;
			field.setAttribute("aria-invalid", fieldValid ? "false" : "true");
			valid = valid && fieldValid;
		});
		return valid;
	}

	function initTheme() {
		var app = document.querySelector(".vtd-app");
		if (!app) {
			return;
		}
		var stored = localStorage.getItem("vtd-theme");
		if (stored) {
			app.setAttribute("data-theme", stored);
			document.documentElement.classList.toggle("is-dark", stored === "dark");
		}
		var toggle = document.querySelector("[data-vtd-theme-toggle]");
		if (toggle) {
			toggle.addEventListener("click", function () {
				var current = app.getAttribute("data-theme");
				var next = current === "dark" ? "light" : "dark";
				app.setAttribute("data-theme", next);
				localStorage.setItem("vtd-theme", next);
				document.documentElement.classList.toggle("is-dark", next === "dark");
			});
		}
	}

	function initDrawer() {
		var sidebar = document.getElementById("vtd-sidebar");
		var backdrop = document.querySelector(".vtd-backdrop");
		if (!sidebar) {
			return;
		}
		document.querySelectorAll("[data-vtd-drawer-open]").forEach(function (btn) {
			btn.addEventListener("click", function () {
				sidebar.classList.add("is-open");
				if (backdrop) {
					backdrop.classList.add("is-open");
				}
			});
		});
		function close() {
			sidebar.classList.remove("is-open");
			if (backdrop) {
				backdrop.classList.remove("is-open");
			}
		}
		document.querySelectorAll("[data-vtd-drawer-close]").forEach(function (el) {
			el.addEventListener("click", close);
		});
	}

	function initTabs() {
		document.querySelectorAll("[data-vtd-tabs]").forEach(function (tabs) {
			var buttons = tabs.querySelectorAll("[data-vtd-tab]");
			buttons.forEach(function (button) {
				button.addEventListener("click", function () {
					var target = button.getAttribute("data-vtd-tab");
					buttons.forEach(function (b) {
						b.classList.toggle("is-active", b === button);
					});
					document.querySelectorAll("[data-vtd-panel]").forEach(function (panel) {
						panel.hidden = panel.getAttribute("data-vtd-panel") !== target;
					});
				});
			});
		});
	}

	function renderNotifications(items, list) {
		if (!list) {
			return;
		}
		if (!items.length) {
			list.innerHTML = '<div class="vtd-notify-loading">' + (i18n.empty || "اعلانی وجود ندارد.") + "</div>";
			return;
		}
		list.innerHTML = items.map(function (item) {
			var link = item.link ? '<a class="vtd-link" href="' + item.link + '">' + (i18n.view || "مشاهده") + "</a>" : "";
			return '<div class="vtd-notify-item ' + (item.read ? "" : "is-unread") + '">' +
				'<div><h4>' + item.title + '</h4><div class="vtd-notify-body">' + item.content + "</div>" +
				'<div class="vtd-notify-meta"><span>' + item.ago + "</span>" + link + "</div></div></div>";
		}).join("");
	}

	function initNotifications() {
		var root = document.querySelector("[data-vtd-notify]");
		if (!root) {
			return;
		}
		var toggle = root.querySelector("[data-vtd-notify-toggle]");
		var panel = root.querySelector("[data-vtd-notify-panel]");
		var list = root.querySelector("[data-vtd-notify-list]");
		var count = root.querySelector("[data-vtd-notify-count]");
		var readAll = root.querySelector("[data-vtd-notify-readall]");

		toggle && toggle.addEventListener("click", function () {
			if (!panel.classList.contains("is-open")) {
				panel.classList.add("is-open");
				request("notifications", { method: "GET" }).then(function (response) {
					renderNotifications((response && response.items) || [], list);
					if (count) {
						count.hidden = true;
					}
				});
			} else {
				panel.classList.remove("is-open");
			}
		});

		readAll && readAll.addEventListener("click", function () {
			request("notifications/read", { data: {} }).then(function () {
				document.querySelectorAll(".vtd-notify-item.is-unread").forEach(function (el) {
					el.classList.remove("is-unread");
				});
			});
		});
	}

	function initForms() {
		document.querySelectorAll("[data-vtd-profile-form]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				if (!jalaliDatesValid(form)) {
					setMessage(msg, "تاریخ را به‌صورت شمسی و با قالب سال/ماه/روز وارد کنید.", false);
					return;
				}
				setMessage(msg, i18n.loading || "در حال بارگذاری…", true);
				request("profile", { data: formData(form) }).then(function (response) {
					setMessage(msg, response.message || i18n.saved, !!response.success);
				});
			});
		});

		document.querySelectorAll("[data-vtd-password-form]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				request("profile/password", { data: formData(form) }).then(function (response) {
					setMessage(msg, response.message, !!response.success);
					if (response.success) {
						form.reset();
					}
				});
			});
		});

		var avatarInput = document.querySelector("[data-vtd-avatar-input]");
		avatarInput && avatarInput.addEventListener("change", function () {
			if (!avatarInput.files.length) {
				return;
			}
			var data = new FormData();
			data.append("avatar", avatarInput.files[0]);
			request("profile/avatar", { body: data }).then(function (response) {
				if (response.success && response.url) {
					document.querySelectorAll("[data-vtd-avatar]").forEach(function (img) {
						img.src = response.url;
					});
				}
			});
		});

		document.querySelectorAll("[data-vtd-ticket-reply]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				var ticketId = document.querySelector("[data-vtd-ticket]").getAttribute("data-vtd-ticket");
				var data = formData(form);
				data.ticket_id = ticketId;
				request("tickets/reply", { data: data }).then(function (response) {
					setMessage(msg, response.message, !!response.success);
					if (response.success) {
						window.location.reload();
					}
				});
			});
		});

		var closeBtn = document.querySelector("[data-vtd-ticket-close]");
		closeBtn && closeBtn.addEventListener("click", function () {
			if (!window.confirm(i18n.confirm || "آیا مطمئن هستید؟")) {
				return;
			}
			var ticketId = document.querySelector("[data-vtd-ticket]").getAttribute("data-vtd-ticket");
			request("tickets/close", { data: { ticket_id: ticketId } }).then(function () {
				window.location.reload();
			});
		});

		var starBtn = document.querySelector("[data-vtd-ticket-star]");
		starBtn && starBtn.addEventListener("click", function () {
			var ticketId = document.querySelector("[data-vtd-ticket]").getAttribute("data-vtd-ticket");
			request("tickets/star", { data: { ticket_id: ticketId } }).then(function () {
				starBtn.classList.toggle("is-active");
			});
		});

		var rateForm = document.querySelector("[data-vtd-ticket-rate]");
		rateForm && rateForm.addEventListener("submit", function (event) {
			event.preventDefault();
			var ticketId = document.querySelector("[data-vtd-ticket]").getAttribute("data-vtd-ticket");
			var data = formData(rateForm);
			data.ticket_id = ticketId;
			request("tickets/rate", { data: data }).then(function () {
				rateForm.innerHTML = '<span class="vtd-chip is-ok">' + (i18n.saved || "ثبت شد") + "</span>";
			});
		});

		document.querySelectorAll("[data-vtd-stars]").forEach(function (widget) {
			var input = widget.querySelector("input[name=score]");
			widget.querySelectorAll("button").forEach(function (button) {
				button.addEventListener("click", function () {
					var value = button.getAttribute("data-value");
					input.value = value;
					widget.querySelectorAll("button").forEach(function (b) {
						b.classList.toggle("is-active", parseInt(b.getAttribute("data-value"), 10) <= parseInt(value, 10));
					});
				});
			});
		});

		document.querySelectorAll("[data-vtd-poll]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				var data = formData(form);
				data.poll_id = form.getAttribute("data-vtd-poll");
				request("polls/vote", { data: data }).then(function (response) {
					setMessage(msg, response.message, !!response.success);
					if (response.success) {
						window.location.reload();
					}
				});
			});
		});

		document.querySelectorAll("[data-vtd-card-form]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				request("banking/cards", { data: formData(form) }).then(function (response) {
					setMessage(msg, response.message, !!response.success);
					if (response.success) {
						window.location.reload();
					}
				});
			});
		});

		document.querySelectorAll("[data-vtd-card-delete]").forEach(function (button) {
			button.addEventListener("click", function () {
			if (!window.confirm(i18n.confirm || "آیا مطمئن هستید؟")) {
					return;
				}
				request("banking/cards/delete", { data: { card_id: button.getAttribute("data-vtd-card-delete") } }).then(function () {
					window.location.reload();
				});
			});
		});

		document.querySelectorAll("[data-vtd-withdraw-form]").forEach(function (form) {
			var msg = form.querySelector("[data-vtd-form-msg]");
			form.addEventListener("submit", function (event) {
				event.preventDefault();
				request("wallet/withdraw", { data: formData(form) }).then(function (response) {
					setMessage(msg, response.message, !!response.success);
					if (response.success) {
						window.location.reload();
					}
				});
			});
		});
	}

	function initModal() {
		var modal = document.querySelector("[data-vtd-login-modal]");
		if (!modal) {
			return;
		}
		document.querySelectorAll(".vtd-login-modal").forEach(function (trigger) {
			trigger.addEventListener("click", function (event) {
				event.preventDefault();
				modal.hidden = false;
			});
		});
		modal.querySelectorAll("[data-vtd-modal-close]").forEach(function (el) {
			el.addEventListener("click", function () {
				modal.hidden = true;
			});
		});
	}

	function initUserMenu() {
		document.addEventListener("click", function (event) {
			document.querySelectorAll("[data-vtd-user-menu][open]").forEach(function (menu) {
				if (!menu.contains(event.target)) {
					menu.removeAttribute("open");
				}
			});
		});
		document.addEventListener("keydown", function (event) {
			if (event.key === "Escape") {
				document.querySelectorAll("[data-vtd-user-menu][open]").forEach(function (menu) { menu.removeAttribute("open"); });
			}
		});
	}

	function initResend() {
		document.querySelectorAll("[data-vtd-resend]").forEach(function (button) {
			button.addEventListener("click", function () {
				var phone = button.getAttribute("data-phone");
				request("otp/send", { data: { phone: phone, purpose: "login" } }).then(function (response) {
					button.textContent = response.message || (i18n.saved || "ثبت شد");
				});
			});
		});
	}

	document.addEventListener("DOMContentLoaded", function () {
		initTheme();
		initDrawer();
		initTabs();
		initNotifications();
		initForms();
		initResend();
		initModal();
		initUserMenu();
	});
})();
