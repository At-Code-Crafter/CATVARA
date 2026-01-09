/* pos.js (POS page logic) - UPDATED (Jan 2026)
	 Fixes / Additions:
	 - Product card thumbnail shows IMAGE centered (no category text)
	 - Product title shows category as small grey pill next to name
	 - Variant card click auto-adds to cart + closes modal (Add button stays fallback)
	 - Edit mode supported via INITIAL_STATE (hydrates cart + charges + notes + payment term + currency)
	 - Save Draft sends ONE hit (header + items) to UPDATE_URL and uses server totals response to update UI
*/

(function () {
	/* ===================== STATE ===================== */
	let customers = [];
	let products = [];
	let variantsMap = {}; // { product_id: [variants...] }
	let paymentTerms = []; // [{id,name,due_days},...]

	let activeProduct = null;
	let selectedVariant = null;

	const cart = new Map();

	let invoiceDate = new Date();
	let dueDate = new Date();

	/* ===================== HELPERS ===================== */
	function pad2(n) {
		return String(n).padStart(2, "0");
	}

	function formatDate(d) {
		return `${d.getFullYear()}-${pad2(d.getMonth() + 1)}-${pad2(d.getDate())}`;
	}

	function addDays(dateObj, days) {
		const d = new Date(dateObj.getTime());
		d.setDate(d.getDate() + Number(days || 0));
		return d;
	}

	function safeNum(val) {
		const n = Number(val);
		return isNaN(n) ? 0 : n;
	}

	function escapeHtml(str) {
		return String(str ?? "")
			.replaceAll("&", "&amp;")
			.replaceAll("<", "&lt;")
			.replaceAll(">", "&gt;")
			.replaceAll('"', "&quot;")
			.replaceAll("'", "&#039;");
	}

	function money(n) {
		const currency = $("#currencySelect").val() || "AED";
		const val = (Number(n) || 0).toFixed(2);
		return `${val} ${currency}`;
	}

	function attrsToText(attrsObj) {
		if (!attrsObj || typeof attrsObj !== "object") return "";
		return Object.keys(attrsObj)
			.map((k) => `${k}: ${attrsObj[k]}`)
			.join(" • ");
	}

	function findPaymentTermById(id) {
		return paymentTerms.find((t) => String(t.id) === String(id)) || null;
	}

	function paymentTermExists(id) {
		return !!findPaymentTermById(id);
	}

	// resolve product image from common possible keys
	function getProductImage(p) {
		return (
			p?.image ||
			p?.thumbnail ||
			p?.image_url ||
			p?.thumb ||
			p?.photo ||
			p?.featured_image ||
			""
		);
	}

	function csrfToken() {
		return $('meta[name="csrf-token"]').attr("content") || "";
	}

	/* ===================== UI ===================== */
	function setInvoiceDatesUI() {
		$("#invoiceDateText").text(formatDate(invoiceDate));
		$("#dueDateText").text(formatDate(dueDate));
	}

	function setPaymentTermsUI(selectedId) {
		const options = paymentTerms
			.map(
				(t) =>
					`<option value="${t.id}" data-days="${t.due_days}">${escapeHtml(
						t.name
					)}</option>`
			)
			.join("");

		$("#paymentTermSelect").html(
			`<option value="">Select Payment Term</option>${options}`
		);

		if (selectedId && paymentTermExists(selectedId)) {
			$("#paymentTermSelect").val(String(selectedId));
		} else {
			$("#paymentTermSelect").val("");
		}

		syncDueDateFromSelectedTerm();
	}

	function syncDueDateFromSelectedTerm() {
		const termId = $("#paymentTermSelect").val() || "";
		const term = findPaymentTermById(termId);
		const days = term ? Number(term.due_days || 0) : 0;

		$("#paymentTermDays").val(days);
		dueDate = addDays(invoiceDate, days);
		setInvoiceDatesUI();
	}

	/* ===================== PRODUCTS LIST ===================== */
	function getProductFilters() {
		return {
			q: ($("#searchInput").val() || "").trim().toLowerCase(),
			category: $("#categoryFilter").val() || "",
			brand: $("#brandFilter").val() || "",
		};
	}

	function filteredProducts() {
		const { q, category, brand } = getProductFilters();

		return products.filter((p) => {
			const name = (p.name || "").toLowerCase();
			const okQ = !q || name.includes(q);
			const okC = !category || p.category === category;
			const okB = !brand || p.brand === brand;
			return okQ && okC && okB;
		});
	}

	function populateProductFilters() {
		const categories = [
			...new Set(products.map((p) => p.category).filter(Boolean)),
		].sort();

		const brands = [...new Set(products.map((p) => p.brand).filter(Boolean))].sort();

		$("#categoryFilter").html(
			`<option value="">All Categories</option>` +
			categories
				.map((c) => `<option value="${escapeHtml(c)}">${escapeHtml(c)}</option>`)
				.join("")
		);

		$("#brandFilter").html(
			`<option value="">All Brands</option>` +
			brands
				.map((b) => `<option value="${escapeHtml(b)}">${escapeHtml(b)}</option>`)
				.join("")
		);
	}

	function renderAllProducts() {
		const list = filteredProducts();
		const grid = $("#productsGrid");
		grid.empty();

		if (!list.length) {
			grid.html(`
        <div class="col-12">
          <div class="alert alert-light border mb-0">No products found. Try changing search/filters.</div>
        </div>
      `);
			return;
		}

		for (const p of list) {
			const v = variantsMap[p.id] || [];
			const prices = v.map((x) => safeNum(x.price));
			const minPrice = prices.length ? Math.min(...prices) : 0;
			const maxPrice = prices.length ? Math.max(...prices) : 0;

			const priceText = prices.length
				? minPrice === maxPrice
					? money(minPrice)
					: `${money(minPrice)} - ${money(maxPrice)}`
				: "No variants";

			const img = getProductImage(p);
			const imgHtml = img
				? `<img class="product-img" src="${escapeHtml(
					img
				)}" alt="${escapeHtml(p.name || "Product")}" loading="lazy">`
				: `<div class="product-img-placeholder">No Image</div>`;

			grid.append(`
        <div class="col-6 col-md-4 col-xl-4 mb-3">
          <div class="product-card" data-product-id="${p.id}">
            <div class="product-thumb">${imgHtml}</div>
            <div class="product-body">
              <div class="product-title">
                <span class="product-name">${escapeHtml(p.name || "")}</span>
                ${p.category
					? `<span class="product-category">${escapeHtml(p.category)}</span>`
					: ""
				}
              </div>
              <div class="product-meta">
                <span class="text-muted">${escapeHtml(p.brand || "")}</span>
                <span class="badge-soft">${escapeHtml(priceText)}</span>
              </div>
            </div>
          </div>
        </div>
      `);
		}
	}

	/* ===================== VARIANT MODAL ===================== */
	function openVariantModal(productId) {
		activeProduct =
			products.find((p) => String(p.id) === String(productId)) || null;

		selectedVariant = null;
		if (!activeProduct) return;

		const list = variantsMap[activeProduct.id] || [];

		$("#variantModalSubtitle").text(
			`${activeProduct.name || ""} • ${activeProduct.brand || ""}`
		);

		$("#variantSelectionHint").text("Click a variant to add it to cart.");
		$("#addVariantBtn").prop("disabled", true);

		if (!list.length) {
			$("#variantGrid").html(
				`<div class="col-12"><div class="alert alert-light border mb-0">No variants for this product.</div></div>`
			);
			$("#variantModal").modal("show");
			return;
		}

		$("#variantGrid").html(
			list
				.map(
					(v) => `
        <div class="col-md-6 mb-3">
          <div class="variant-card" data-variant-id="${v.id}">
            <div class="variant-attrs">${escapeHtml(attrsToText(v.attrs))}</div>
            <div class="variant-meta">
              <span><strong>${money(v.price)}</strong></span>
              <span>Stock: ${safeNum(v.stock)}</span>
            </div>
          </div>
        </div>
      `
				)
				.join("")
		);

		$("#variantModal").modal("show");
	}

	// supports autoAdd
	function selectVariant(variantId, autoAdd = false) {
		if (!activeProduct) return;

		const list = variantsMap[activeProduct.id] || [];
		selectedVariant =
			list.find((v) => String(v.id) === String(variantId)) || null;

		$(".variant-card").removeClass("active");
		$(`.variant-card[data-variant-id="${variantId}"]`).addClass("active");

		if (!selectedVariant) return;

		const stock = safeNum(selectedVariant.stock);

		$("#variantSelectionHint").text(
			`${attrsToText(selectedVariant.attrs)} • ${money(
				selectedVariant.price
			)} • Stock: ${stock}`
		);

		$("#addVariantBtn").prop("disabled", stock <= 0);

		if (autoAdd && stock > 0) {
			upsertCartItem(activeProduct, selectedVariant);
			$("#variantModal").modal("hide");
		}
	}

	/* ===================== CART ===================== */
	function upsertCartItem(product, variant) {
		const key = String(variant.id);
		const stock = safeNum(variant.stock);

		if (cart.has(key)) {
			const item = cart.get(key);
			item.qty = Math.min(item.qty + 1, Math.max(1, stock || 1));
			cart.set(key, item);
		} else {
			cart.set(key, {
				variantId: String(variant.id),
				productId: String(product.id),
				name: product.name || "",
				brand: product.brand || "",
				attrs: variant.attrs || {},
				unitPrice: safeNum(variant.price),
				stock: stock,
				qty: 1,
				discountPercent: 0,
			});
		}

		renderCart();
	}

	// UI total (gross - discount). VAT computed globally on totals section.
	function calcLineNet(item) {
		const qty = safeNum(item.qty);
		const unit = safeNum(item.unitPrice);
		const disc = Math.min(100, Math.max(0, safeNum(item.discountPercent)));
		const gross = unit * qty;
		return Math.max(0, gross - gross * (disc / 100));
	}

	function renderCart() {
		const tbody = $("#cartBody");
		tbody.empty();

		if (cart.size === 0) {
			tbody.append($("#emptyCartRow"));
			$("#emptyCartRow").show();
			updateTotalsUI();
			return;
		}

		$("#emptyCartRow").hide();

		for (const item of cart.values()) {
			tbody.append(`
        <tr data-variant-id="${item.variantId}">
          <td>
            <div class="cart-item-name">${escapeHtml(item.name)}</div>
            <div class="cart-item-variant">${escapeHtml(attrsToText(item.attrs))}</div>
            <div class="small text-muted">Stock: ${safeNum(item.stock)}</div>
          </td>
          <td class="text-right"><div class="font-weight-bold">${money(item.unitPrice)}</div></td>
          <td class="text-center">
            <input type="number" class="form-control input-xs qty-input" value="${item.qty}" min="1" max="${Math.max(
				1,
				safeNum(item.stock)
			)}" step="1" />
          </td>
          <td class="text-center">
            <input type="number" class="form-control input-xs disc-input" value="${item.discountPercent}" min="0" max="100" step="0.5" />
          </td>
          <td class="text-right"><div class="font-weight-bold line-total">${money(calcLineNet(item))}</div></td>
          <td class="text-center">
            <button class="btn btn-outline-secondary btn-icon remove-btn" title="Remove">&times;</button>
          </td>
        </tr>
      `);
		}

		updateTotalsUI();
	}

	function updateTotalsUI() {
		let subTotalNet = 0;
		for (const item of cart.values()) subTotalNet += calcLineNet(item);

		const shipping = Math.max(0, safeNum($("#shippingInput").val()));
		const additional = Math.max(0, safeNum($("#additionalInput").val()));
		const vatRate = Math.max(0, safeNum($("#vatRateInput").val()));

		const taxable = subTotalNet + shipping + additional;
		const vat = taxable * (vatRate / 100);
		const grand = taxable + vat;

		$("#subTotalText").text(money(subTotalNet));
		$("#vatText").text(money(vat));
		$("#grandTotalText").text(money(grand));

		// update each row net
		$("#cartBody tr").each(function () {
			const vid = $(this).data("variant-id");
			if (!vid) return;
			const item = cart.get(String(vid));
			if (!item) return;

			$(this).find(".line-total").text(money(calcLineNet(item)));
			$(this)
				.find("td.text-right .font-weight-bold")
				.first()
				.text(money(item.unitPrice));
		});
	}

	/* ===================== HYDRATE (EDIT MODE) ===================== */
	function hydrateStateAfterLoad() {
		if (typeof INITIAL_STATE === "undefined" || !INITIAL_STATE) return;

		// currency (code)
		if (INITIAL_STATE.currency) {
			$("#currencySelect").val(String(INITIAL_STATE.currency)).trigger("change");
		}

		// charges + notes
		$("#shippingInput").val(safeNum(INITIAL_STATE.shipping || 0));
		$("#additionalInput").val(safeNum(INITIAL_STATE.additional || 0));
		$("#vatRateInput").val(safeNum(INITIAL_STATE.vat_rate || 5));
		$("#commentsInput").val(INITIAL_STATE.notes || "");

		// payment term: set AFTER payment terms dropdown built
		if (INITIAL_STATE.payment_term_id && paymentTermExists(INITIAL_STATE.payment_term_id)) {
			$("#paymentTermSelect").val(String(INITIAL_STATE.payment_term_id));
			syncDueDateFromSelectedTerm();
		}

		// items
		if (Array.isArray(INITIAL_STATE.items)) {
			for (const savedItem of INITIAL_STATE.items) {
				const variantId = String(savedItem.variantId || savedItem.variant_id || "");

				if (!variantId) continue;

				let foundProduct = null;
				let foundVariant = null;

				for (const p of products) {
					const variants = variantsMap[p.id] || [];
					const v = variants.find((x) => String(x.id) === variantId);
					if (v) {
						foundProduct = p;
						foundVariant = v;
						break;
					}
				}




				if (foundProduct && foundVariant) {
					cart.set(String(foundVariant.id), {
						variantId: String(foundVariant.id),
						productId: String(foundProduct.id),
						name: foundProduct.name || "",
						brand: foundProduct.brand || "",
						attrs: foundVariant.attrs || {},
						unitPrice: safeNum(savedItem.unitPrice ?? savedItem.unit_price ?? foundVariant.price),
						stock: safeNum(foundVariant.stock),
						qty: Math.max(1, safeNum(savedItem.qty ?? savedItem.quantity ?? 1)),
						discountPercent: safeNum(savedItem.discountPercent ?? savedItem.discount_percent ?? 0),
					});
				}
			}
		}

		renderCart();
	}

	/* ===================== PAYLOAD + SAVE ===================== */
	function buildUpdatePayload() {
		const vatRate = Math.max(0, safeNum($("#vatRateInput").val()));

		return {
			payment_term_id: $("#paymentTermSelect").val() || null,
			due_date: formatDate(dueDate),
			notes: ($("#commentsInput").val() || "").trim(),

			shipping: safeNum($("#shippingInput").val()),
			additional: safeNum($("#additionalInput").val()),
			vat_rate: vatRate,

			currency: $("#currencySelect").val() || "AED",

			items: Array.from(cart.values()).map((x) => ({
				variant_id: x.variantId,
				qty: Number(x.qty),
				unit_price: Number(x.unitPrice),
				discount_percent: Number(x.discountPercent || 0),
				tax_rate: Number(vatRate), // send global vat_rate as item tax_rate (controller accepts both)
			})),

			_token: csrfToken(),
			_method: "PUT",
		};
	}

	function applyServerTotals(totals) {
		if (!totals) return;

		// totals are numeric; we display using current currency code
		$("#subTotalText").text(money(totals.subtotal ?? 0));
		$("#vatText").text(money(totals.tax_total ?? 0));
		$("#grandTotalText").text(money(totals.grand_total ?? 0));
	}

	// UPDATE: Toggle visibility based on status
	function updateButtonVisibility(isConfirmed) {
		if (isConfirmed) {
			$("#generateOrderContainer").addClass("d-none");
			$("#postGenerateContainer").removeClass("d-none");
			$("#saveDraftBtn").text("Update Order");
		} else {
			$("#generateOrderContainer").removeClass("d-none");
			$("#postGenerateContainer").addClass("d-none");
			$("#saveDraftBtn").text("Save Draft");
		}
	}

	/* ===================== BOOT ===================== */
	function boot() {
		const bladeSelectedTermId =
			typeof SELECTED_PAYMENT_TERM_ID !== "undefined"
				? String(SELECTED_PAYMENT_TERM_ID || "")
				: "";

		$.when($.getJSON(CUSTOMERS_URL), $.getJSON(LOAD_PRODUCTS_URL), $.getJSON(PAYMENT_TERMS_URL))
			.done(function (cRes, pRes, tRes) {
				customers = Array.isArray(cRes[0]) ? cRes[0] : [];
				products = Array.isArray(pRes[0]) ? pRes[0] : [];
				paymentTerms = Array.isArray(tRes[0]) ? tRes[0] : [];

				variantsMap = {};
				for (const p of products) {
					variantsMap[p.id] = Array.isArray(p.variants) ? p.variants : [];
				}

				populateProductFilters();
				renderAllProducts();

				// Choose term:
				let termToSelect = "";

				if (typeof INITIAL_STATE !== "undefined" && INITIAL_STATE && INITIAL_STATE.payment_term_id) {
					termToSelect = String(INITIAL_STATE.payment_term_id);
				} else if (bladeSelectedTermId && paymentTermExists(bladeSelectedTermId)) {
					termToSelect = bladeSelectedTermId;
				}

				setPaymentTermsUI(termToSelect);
				setInvoiceDatesUI();

				// hydrate after products + terms loaded
				hydrateStateAfterLoad();

				// Check status for button visibility
				const isConfirmed = typeof INITIAL_STATE !== "undefined" && INITIAL_STATE.status === "CONFIRMED";
				updateButtonVisibility(isConfirmed);
			})
			.fail(function () {
				$("#productsGrid").html(
					`<div class="col-12"><div class="alert alert-danger">Failed to load JSON data.</div></div>`
				);
			});

		// payment terms -> due date
		$("#paymentTermSelect").on("change", syncDueDateFromSelectedTerm);

		// filters
		$("#searchInput").on("input", renderAllProducts);
		$("#categoryFilter, #brandFilter").on("change", renderAllProducts);

		// product click -> modal
		$(document).on("click", ".product-card", function () {
			openVariantModal($(this).data("product-id"));
		});

		// variant click -> select + auto add
		$(document).on("click", ".variant-card", function () {
			selectVariant($(this).data("variant-id"), true);
		});

		// fallback add button
		$("#addVariantBtn").on("click", function () {
			if (!activeProduct || !selectedVariant) return;
			if (safeNum(selectedVariant.stock) <= 0) return;
			upsertCartItem(activeProduct, selectedVariant);
			$("#variantModal").modal("hide");
		});

		// cart edits
		$(document).on("input", ".qty-input, .disc-input", function () {
			const row = $(this).closest("tr");
			const vid = String(row.data("variant-id") || "");
			const item = cart.get(vid);
			if (!item) return;

			const qty = Math.max(1, Math.floor(safeNum(row.find(".qty-input").val())));
			const maxQty = Math.max(1, safeNum(item.stock));
			item.qty = Math.min(qty, maxQty);

			const disc = Math.min(100, Math.max(0, safeNum(row.find(".disc-input").val())));
			item.discountPercent = disc;

			cart.set(vid, item);
			row.find(".qty-input").val(item.qty);
			row.find(".disc-input").val(item.discountPercent);

			updateTotalsUI();
		});

		// remove
		$(document).on("click", ".remove-btn", function () {
			const vid = String($(this).closest("tr").data("variant-id") || "");
			cart.delete(vid);
			renderCart();
		});

		// totals inputs
		$("#shippingInput, #additionalInput, #vatRateInput, #currencySelect").on(
			"input change",
			updateTotalsUI
		);

		$("#clearCartBtn").on("click", function () {
			cart.clear();
			renderCart();
		});

		// Save Draft
		$("#saveDraftBtn").on("click", function () {
			saveOrder(false);
		});

		// Generate Order
		$("#generateOrderBtn").on("click", function () {
			if (confirm("Are you sure you want to generate this order? This will confirm the status.")) {
				saveOrder(true);
			}
		});

		function saveOrder(isGenerating) {
			if (typeof UPDATE_URL === "undefined" || !UPDATE_URL) {
				alert("UPDATE_URL not configured in Blade.");
				return;
			}

			const payload = buildUpdatePayload();
			if (isGenerating) {
				payload.action = "generate";
			}

			const btn = isGenerating ? $("#generateOrderBtn") : $("#saveDraftBtn");
			const originalText = btn.text();

			btn.prop("disabled", true).text("Saving...");

			$.ajax({
				url: UPDATE_URL,
				type: "POST",
				data: payload,
				success: function (res) {
					if (res && res.success) {
						applyServerTotals(res.totals);
						if (isGenerating) {
							updateButtonVisibility(true);
							alert("Order generated and confirmed successfully.");
						} else {
							alert("Order updated successfully.");
						}
					} else {
						alert(res?.message || "Error saving order.");
					}
				},
				error: function (xhr) {
					alert("Failed to save order: " + (xhr.responseJSON?.message || "Unknown error"));
				},
				complete: function () {
					btn.prop("disabled", false).text(originalText);
				},
			});
		}

		// Download PDF / Print
		$("#downloadPdfBtn").on("click", function (e) {
			e.preventDefault();
			if (typeof PRINT_URL !== "undefined" && PRINT_URL) {
				window.open(PRINT_URL, "_blank");
			} else {
				alert("Print URL not available.");
			}
		});

		// Generate Invoice Stub
		$("#createInvoiceBtn").on("click", function (e) {
			e.preventDefault();
			alert("Generate Invoice functionality will be implemented soon.");
		});

		$("#variantModal").on("hidden.bs.modal", function () {
			activeProduct = null;
			selectedVariant = null;
			$("#variantGrid").empty();
			$("#addVariantBtn").prop("disabled", true);
		});
	}

	$(boot);
})();
