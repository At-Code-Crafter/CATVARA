/* pos.js (POS page logic) - UPDATED (single hit: load-products includes variants)
   Change requested:
   - REMOVE infinite load-more-on-scroll
   - SHOW ALL products at once (still supports filters/search)
*/

(function () {
	/* ===================== STATE ===================== */
	let customers = [];
	let products = [];

	// variantsMap: { product_uuid: [variants...] }
	let variantsMap = {};

	let paymentTerms = []; // [{id,name,due_days},...]

	let sellToId = "";
	let billToId = "";

	// Variant modal state
	let activeProduct = null;
	let selectedVariant = null;

	// Cart: key = variant_id
	const cart = new Map();

	// Invoice meta
	let invoiceDate = new Date();
	let dueDate = new Date();

	/* ===================== HELPERS ===================== */
	function getQueryParam(name) {
		const url = new URL(window.location.href);
		return url.searchParams.get(name) || "";
	}

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

	function money(n) {
		const currency = $("#currencySelect").val() || "AED";
		const val = (Number(n) || 0).toFixed(2);
		return `${val} ${currency}`;
	}

	function safeNum(val) {
		const n = Number(val);
		return isNaN(n) ? 0 : n;
	}

	function attrsToText(attrsObj) {
		if (!attrsObj || typeof attrsObj !== "object") return "";
		return Object.keys(attrsObj)
			.map((k) => `${k}: ${attrsObj[k]}`)
			.join(" • ");
	}

	function findCustomerById(uuid) {
		return customers.find((c) => c.uuid === uuid) || null;
	}

	function findPaymentTermById(id) {
		return paymentTerms.find((t) => String(t.id) === String(id)) || null;
	}

	function paymentTermExists(id) {
		return !!findPaymentTermById(id);
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
					`<option value="${t.id}" data-days="${t.due_days}">${t.name}</option>`
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

	/* ===================== PRODUCTS LISTING (NO PAGING) ===================== */
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

			grid.append(`
        <div class="col-6 col-md-4 col-xl-4 mb-3">
          <div class="product-card" data-product-id="${p.id}">
            <div class="product-thumb">${p.category || ""}</div>
            <div class="product-body">
              <div class="product-title">${p.name || ""}</div>
              <div class="product-meta">
                <span class="text-muted">${p.brand || ""}</span>
                <span class="badge-soft">${priceText}</span>
              </div>
            </div>
          </div>
        </div>
      `);
		}
	}

	function populateProductFilters() {
		const categories = [...new Set(products.map((p) => p.category).filter(Boolean))].sort();
		const brands = [...new Set(products.map((p) => p.brand).filter(Boolean))].sort();

		$("#categoryFilter").html(
			`<option value="">All Categories</option>` +
				categories.map((c) => `<option value="${c}">${c}</option>`).join("")
		);

		$("#brandFilter").html(
			`<option value="">All Brands</option>` +
				brands.map((b) => `<option value="${b}">${b}</option>`).join("")
		);
	}

	/* ===================== VARIANT MODAL ===================== */
	function openVariantModal(productId) {
		activeProduct = products.find((p) => String(p.id) === String(productId)) || null;
		selectedVariant = null;
		if (!activeProduct) return;

		const list = variantsMap[activeProduct.id] || [];
		$("#variantModalSubtitle").text(`${activeProduct.name || ""} • ${activeProduct.brand || ""}`);
		$("#variantSelectionHint").text("No variant selected.");
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
            <div class="variant-attrs">${attrsToText(v.attrs)}</div>
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

	function selectVariant(variantId) {
		if (!activeProduct) return;
		const list = variantsMap[activeProduct.id] || [];
		selectedVariant = list.find((v) => String(v.id) === String(variantId)) || null;

		$(".variant-card").removeClass("active");
		$(`.variant-card[data-variant-id="${variantId}"]`).addClass("active");

		if (selectedVariant) {
			$("#variantSelectionHint").text(
				`${attrsToText(selectedVariant.attrs)} • ${money(selectedVariant.price)} • Stock: ${safeNum(
					selectedVariant.stock
				)}`
			);
			$("#addVariantBtn").prop("disabled", safeNum(selectedVariant.stock) <= 0);
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

	function calcLineTotal(item) {
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
			updateTotals();
			return;
		}

		$("#emptyCartRow").hide();

		for (const item of cart.values()) {
			tbody.append(`
        <tr data-variant-id="${item.variantId}">
          <td>
            <div class="cart-item-name">${item.name}</div>
            <div class="cart-item-variant">${attrsToText(item.attrs)}</div>
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
          <td class="text-right"><div class="font-weight-bold line-total">${money(calcLineTotal(item))}</div></td>
          <td class="text-center">
            <button class="btn btn-outline-secondary btn-icon remove-btn" title="Remove">&times;</button>
          </td>
        </tr>
      `);
		}

		updateTotals();
	}

	function updateTotals() {
		let subTotal = 0;
		for (const item of cart.values()) subTotal += calcLineTotal(item);

		const shipping = Math.max(0, safeNum($("#shippingInput").val()));
		const additional = Math.max(0, safeNum($("#additionalInput").val()));
		const vatRate = Math.max(0, safeNum($("#vatRateInput").val()));

		const taxable = subTotal + shipping + additional;
		const vat = taxable * (vatRate / 100);
		const grand = taxable + vat;

		$("#subTotalText").text(money(subTotal));
		$("#vatText").text(money(vat));
		$("#grandTotalText").text(money(grand));

		$("#cartBody tr").each(function () {
			const vid = $(this).data("variant-id");
			if (!vid) return;
			const item = cart.get(String(vid));
			if (!item) return;

			$(this).find(".line-total").text(money(calcLineTotal(item)));
			$(this)
				.find("td.text-right .font-weight-bold")
				.first()
				.text(money(item.unitPrice));
		});
	}

	/* ===================== PAYLOAD / PRINT ===================== */
	function buildInvoicePayload() {
		const sell = findCustomerById(sellToId);
		const bill = findCustomerById(billToId);

		const termId = $("#paymentTermSelect").val() || "";
		const term = findPaymentTermById(termId);

		const items = Array.from(cart.values()).map((i) => ({
			variant_id: i.variantId,
			product_id: i.productId,
			name: i.name,
			attrs: i.attrs,
			unit_price: i.unitPrice,
			qty: i.qty,
			discount_percent: i.discountPercent,
			line_total: calcLineTotal(i),
		}));

		const subTotal = items.reduce((a, x) => a + x.line_total, 0);
		const shipping = Math.max(0, safeNum($("#shippingInput").val()));
		const additional = Math.max(0, safeNum($("#additionalInput").val()));
		const vatRate = Math.max(0, safeNum($("#vatRateInput").val()));
		const taxable = subTotal + shipping + additional;
		const vatAmount = taxable * (vatRate / 100);
		const grandTotal = taxable + vatAmount;

		return {
			sell_to_id: sellToId,
			bill_to_id: billToId,
			sell_to: sell,
			bill_to: bill,
			invoice_date: formatDate(invoiceDate),
			due_date: formatDate(dueDate),
			payment_term_id: termId,
			payment_term: term,
			comments: ($("#commentsInput").val() || "").trim(),
			currency: $("#currencySelect").val() || "AED",
			charges: { shipping, additional, vat_rate: vatRate },
			totals: {
				sub_total: subTotal,
				vat_amount: vatAmount,
				grand_total: grandTotal,
			},
			items,
		};
	}

	/* ===================== BOOT ===================== */
	function boot() {
		sellToId = getQueryParam("sell_to");
		billToId = getQueryParam("bill_to");

		if (!sellToId || !billToId) {
			$("#missingCustomerModal").modal("show");
			return;
		}

		// SELECTED_PAYMENT_TERM_ID comes from Blade
		const bladeSelectedTermId =
			typeof SELECTED_PAYMENT_TERM_ID !== "undefined"
				? String(SELECTED_PAYMENT_TERM_ID || "")
				: "";

		$.when(
			$.getJSON(CUSTOMERS_URL),
			$.getJSON(LOAD_PRODUCTS_URL),
			$.getJSON(PAYMENT_TERMS_URL)
		)
			.done(function (cRes, pRes, tRes) {
				customers = Array.isArray(cRes[0]) ? cRes[0] : [];
				products = Array.isArray(pRes[0]) ? pRes[0] : [];
				paymentTerms = Array.isArray(tRes[0]) ? tRes[0] : [];

				// Build variantsMap from embedded variants
				variantsMap = {};
				for (const p of products) {
					variantsMap[p.id] = Array.isArray(p.variants) ? p.variants : [];
				}

				populateProductFilters();
				renderAllProducts();

				let termToSelect = "";

				if (bladeSelectedTermId && paymentTermExists(bladeSelectedTermId)) {
					termToSelect = bladeSelectedTermId;
				} else {
					const bill = findCustomerById(billToId);
					const sell = findCustomerById(sellToId);

					const billTerm = bill && bill.payment_term_id ? String(bill.payment_term_id) : "";
					const sellTerm = sell && sell.payment_term_id ? String(sell.payment_term_id) : "";

					if (billTerm && paymentTermExists(billTerm)) {
						termToSelect = billTerm;
					} else if (sellTerm && paymentTermExists(sellTerm)) {
						termToSelect = sellTerm;
					}
				}

				setPaymentTermsUI(termToSelect);

				const days = Number(findPaymentTermById(termToSelect)?.due_days || 0);
				dueDate = addDays(invoiceDate, days);
				setInvoiceDatesUI();
			})
			.fail(function () {
				$("#productsGrid").html(
					`<div class="col-12"><div class="alert alert-danger">Failed to load JSON data.</div></div>`
				);
			});

		$("#paymentTermSelect").on("change", function () {
			syncDueDateFromSelectedTerm();
		});

		// Filters (no paging)
		$("#searchInput").on("input", renderAllProducts);
		$("#categoryFilter, #brandFilter").on("change", renderAllProducts);

		// Product click -> variant modal
		$(document).on("click", ".product-card", function () {
			openVariantModal($(this).data("product-id"));
		});

		// Variant select
		$(document).on("click", ".variant-card", function () {
			selectVariant($(this).data("variant-id"));
		});

		// Add variant
		$("#addVariantBtn").on("click", function () {
			if (!activeProduct || !selectedVariant) return;
			upsertCartItem(activeProduct, selectedVariant);
			$("#variantModal").modal("hide");
		});

		// Cart edits
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

			updateTotals();
		});

		// Remove
		$(document).on("click", ".remove-btn", function () {
			const vid = String($(this).closest("tr").data("variant-id") || "");
			cart.delete(vid);
			renderCart();
		});

		// Totals inputs
		$("#shippingInput, #additionalInput, #vatRateInput, #currencySelect").on(
			"input change",
			updateTotals
		);

		// Clear cart
		$("#clearCartBtn").on("click", function () {
			cart.clear();
			renderCart();
		});

		// Save Draft (demo payload)
		$("#saveDraftBtn").on("click", function () {
			const payload = buildInvoicePayload();
			console.log("SAVE DRAFT PAYLOAD:", payload);
			alert("Draft saved (demo). Payload logged in console.");
		});

		// Modal reset
		$("#variantModal").on("hidden.bs.modal", function () {
			activeProduct = null;
			selectedVariant = null;
			$("#variantGrid").empty();
			$("#addVariantBtn").prop("disabled", true);
		});
	}

	$(boot);
})();
