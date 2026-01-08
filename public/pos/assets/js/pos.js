/* pos.js (POS page logic) */

(function () {
	const CUSTOMERS_URL = "assets/data/customers.json";
	const PRODUCTS_URL = "assets/data/products.json";
	const VARIANTS_URL = "assets/data/variants.json";
	const PAYMENT_TERMS_URL = "assets/data/payment_terms.json";

	let customers = [];
	let products = [];
	let variantsMap = {}; // { product_id: [variants...] }
	let paymentTerms = []; // [{id,name,due_days},...]

	let sellToId = "";
	let billToId = "";

	// Product paging
	let page = 0;
	const PAGE_SIZE = 12;

	// Variant modal state
	let activeProduct = null;
	let selectedVariant = null;

	// Cart: key = variant_id
	const cart = new Map();

	// Invoice meta
	let invoiceDate = new Date(); // today
	let dueDate = new Date(); // computed

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
		return Object.keys(attrsObj)
			.map((k) => `${k}: ${attrsObj[k]}`)
			.join(" • ");
	}

	function findCustomerById(id) {
		return customers.find((c) => c.id === id) || null;
	}

	function findPaymentTermById(id) {
		return paymentTerms.find((t) => t.id === id) || null;
	}

	function setCustomerBar() {
		const sell = findCustomerById(sellToId);
		const bill = findCustomerById(billToId);

		$("#customerBar").text(
			`Sell To: ${sell ? sell.name : "-"} | Bill To: ${bill ? bill.name : "-"}`
		);
	}

	function setInvoiceDatesUI() {
		$("#invoiceDateText").text(formatDate(invoiceDate));
		$("#dueDateText").text(formatDate(dueDate));
	}

	function setPaymentTermsUI(defaultTermId) {
		const options = paymentTerms
			.map(
				(t) =>
					`<option value="${t.id}" data-days="${t.due_days}">${t.name}</option>`
			)
			.join("");

		$("#paymentTermSelect").html(
			`<option value="">Select Payment Term</option>${options}`
		);

		if (defaultTermId) {
			$("#paymentTermSelect").val(defaultTermId);
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
			const okQ =
				!q ||
				p.name.toLowerCase().includes(q) ||
				p.id.toLowerCase().includes(q);
			const okC = !category || p.category === category;
			const okB = !brand || p.brand === brand;
			return okQ && okC && okB;
		});
	}

	function renderProductsChunk() {
		const list = filteredProducts();
		const start = page * PAGE_SIZE;
		const chunk = list.slice(start, start + PAGE_SIZE);

		for (const p of chunk) {
			const v = variantsMap[p.id] || [];
			const prices = v.map((x) => x.price);
			const minPrice = prices.length ? Math.min(...prices) : 0;
			const maxPrice = prices.length ? Math.max(...prices) : 0;
			const priceText = prices.length
				? minPrice === maxPrice
					? money(minPrice)
					: `${money(minPrice)} - ${money(maxPrice)}`
				: "No variants";

			$("#productsGrid").append(`
        <div class="col-6 col-md-4 col-xl-3 mb-3">
          <div class="product-card" data-product-id="${p.id}">
            <div class="product-thumb">${p.category}</div>
            <div class="product-body">
              <div class="product-title">${p.name}</div>
              <div class="product-meta">
                <span class="text-muted">${p.brand}</span>
                <span class="badge-soft">${priceText}</span>
              </div>
            </div>
          </div>
        </div>
      `);
		}

		$("#loadMoreIndicator").addClass("d-none");

		if (page === 0 && chunk.length === 0) {
			$("#productsGrid").html(`
        <div class="col-12">
          <div class="alert alert-light border mb-0">No products found. Try changing search/filters.</div>
        </div>
      `);
		}
	}

	function resetAndRenderProducts() {
		page = 0;
		$("#productsGrid").empty();
		renderProductsChunk();
	}

	function canLoadMore() {
		const count = filteredProducts().length;
		return (page + 1) * PAGE_SIZE < count;
	}

	function loadMoreIfPossible() {
		if (!canLoadMore()) return;
		$("#loadMoreIndicator").removeClass("d-none");
		setTimeout(() => {
			page++;
			renderProductsChunk();
		}, 300);
	}

	function populateProductFilters() {
		const categories = [...new Set(products.map((p) => p.category))].sort();
		const brands = [...new Set(products.map((p) => p.brand))].sort();

		$("#categoryFilter").append(
			categories.map((c) => `<option value="${c}">${c}</option>`).join("")
		);
		$("#brandFilter").append(
			brands.map((b) => `<option value="${b}">${b}</option>`).join("")
		);
	}

	// Variant modal
	function openVariantModal(productId) {
		activeProduct = products.find((p) => p.id === productId) || null;
		selectedVariant = null;
		if (!activeProduct) return;

		const list = variantsMap[activeProduct.id] || [];
		$("#variantModalSubtitle").text(
			`${activeProduct.name} • ${activeProduct.brand}`
		);
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
              <span>Stock: ${v.stock}</span>
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
		selectedVariant = list.find((v) => v.id === variantId) || null;

		$(".variant-card").removeClass("active");
		$(`.variant-card[data-variant-id="${variantId}"]`).addClass("active");

		if (selectedVariant) {
			$("#variantSelectionHint").text(
				`${attrsToText(selectedVariant.attrs)} • ${money(
					selectedVariant.price
				)} • Stock: ${selectedVariant.stock}`
			);
			$("#addVariantBtn").prop("disabled", selectedVariant.stock <= 0);
		}
	}

	// Cart
	function upsertCartItem(product, variant) {
		const key = variant.id;
		if (cart.has(key)) {
			const item = cart.get(key);
			item.qty += 1;
			cart.set(key, item);
		} else {
			cart.set(key, {
				variantId: variant.id,
				productId: product.id,
				name: product.name,
				brand: product.brand,
				attrs: variant.attrs,
				unitPrice: variant.price,
				stock: variant.stock,
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
            <div class="small text-muted">Stock: ${item.stock}</div>
          </td>
          <td class="text-right"><div class="font-weight-bold">${money(
						item.unitPrice
					)}</div></td>
          <td class="text-center">
            <input type="number" class="form-control input-xs qty-input" value="${
							item.qty
						}" min="1" max="${Math.max(1, item.stock)}" step="1" />
          </td>
          <td class="text-center">
            <input type="number" class="form-control input-xs disc-input" value="${
							item.discountPercent
						}" min="0" max="100" step="0.5" />
          </td>
          <td class="text-right"><div class="font-weight-bold line-total">${money(
						calcLineTotal(item)
					)}</div></td>
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
			const item = cart.get(vid);
			if (!item) return;
			$(this)
				.find(".line-total")
				.text(money(calcLineTotal(item)));
			$(this)
				.find("td.text-right .font-weight-bold")
				.first()
				.text(money(item.unitPrice));
		});
	}

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

	function renderInvoicePreview(payload) {
		const sell = payload.sell_to;
		const bill = payload.bill_to;
		const termName = payload.payment_term ? payload.payment_term.name : "-";

		const rows = payload.items
			.map((x, idx) => {
				return `
        <tr>
          <td class="text-muted">${idx + 1}</td>
          <td>
            <div style="font-weight:700;">${x.name}</div>
            <div class="text-muted" style="font-size:12px;">${attrsToText(
							x.attrs
						)}</div>
          </td>
          <td class="text-right">${money(x.unit_price)}</td>
          <td class="text-center">${x.qty}</td>
          <td class="text-center">${x.discount_percent}%</td>
          <td class="text-right" style="font-weight:700;">${money(
						x.line_total
					)}</td>
        </tr>
      `;
			})
			.join("");

		$("#invoiceMetaLine").text(
			`Invoice Date: ${payload.invoice_date} • Due Date: ${payload.due_date} • Terms: ${termName}`
		);

		$("#invoicePreviewBody").html(`
      <div class="mb-3">
        <div class="row">
          <div class="col-md-6">
            <div class="text-muted" style="font-size:12px;">Sell To</div>
            <div style="font-weight:700;">${sell ? sell.name : "-"}</div>
            <div class="text-muted" style="font-size:12px;">${
							sell ? sell.address : "-"
						}</div>
            <div class="text-muted" style="font-size:12px;">${
							sell ? `${sell.phone} • ${sell.email}` : ""
						}</div>
          </div>
          <div class="col-md-6 mt-3 mt-md-0">
            <div class="text-muted" style="font-size:12px;">Bill To</div>
            <div style="font-weight:700;">${bill ? bill.name : "-"}</div>
            <div class="text-muted" style="font-size:12px;">${
							bill ? bill.address : "-"
						}</div>
            <div class="text-muted" style="font-size:12px;">${
							bill ? `${bill.phone} • ${bill.email}` : ""
						}</div>
          </div>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th class="text-right">Unit</th>
              <th class="text-center">Qty</th>
              <th class="text-center">Disc</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            ${
							rows ||
							`<tr><td colspan="6" class="text-center text-muted">No items</td></tr>`
						}
          </tbody>
        </table>
      </div>

      <div class="row mt-3">
        <div class="col-md-6">
          <div class="text-muted" style="font-size:12px;">Comments</div>
          <div style="white-space:pre-wrap;">${payload.comments || "-"}</div>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
          <div class="d-flex justify-content-between"><div class="text-muted">Sub Total</div><div style="font-weight:700;">${money(
						payload.totals.sub_total
					)}</div></div>
          <div class="d-flex justify-content-between"><div class="text-muted">Shipping</div><div style="font-weight:700;">${money(
						payload.charges.shipping
					)}</div></div>
          <div class="d-flex justify-content-between"><div class="text-muted">Additional</div><div style="font-weight:700;">${money(
						payload.charges.additional
					)}</div></div>
          <div class="d-flex justify-content-between"><div class="text-muted">VAT</div><div style="font-weight:700;">${money(
						payload.totals.vat_amount
					)}</div></div>
          <div class="d-flex justify-content-between mt-1"><div class="text-muted">Grand Total</div><div style="font-weight:800; font-size:18px;">${money(
						payload.totals.grand_total
					)}</div></div>
        </div>
      </div>
    `);
	}

	function printInvoiceFromModal() {
		const html = document.getElementById("invoicePreviewBody").innerHTML;
		const meta = document.getElementById("invoiceMetaLine").textContent;

		const w = window.open("", "_blank", "width=900,height=650");
		w.document.write(`
      <html>
      <head>
        <title>Invoice</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"/>
      </head>
      <body class="p-4">
        <h5>Invoice</h5>
        <div class="text-muted mb-3" style="font-size:12px;">${meta}</div>
        ${html}
        <script>
          window.onload = function(){ window.print(); };
        </script>
      </body>
      </html>
    `);
		w.document.close();
	}

	function boot() {
		sellToId = getQueryParam("sell_to");
		billToId = getQueryParam("bill_to");

		if (!sellToId || !billToId) {
			$("#missingCustomerModal").modal("show");
			return;
		}

		// Load all JSON
		$.when(
			$.getJSON(CUSTOMERS_URL),
			$.getJSON(PRODUCTS_URL),
			$.getJSON(VARIANTS_URL),
			$.getJSON(PAYMENT_TERMS_URL)
		)
			.done(function (cRes, pRes, vRes, tRes) {
				customers = Array.isArray(cRes[0]) ? cRes[0] : [];
				products = Array.isArray(pRes[0]) ? pRes[0] : [];
				variantsMap = vRes[0] && typeof vRes[0] === "object" ? vRes[0] : {};
				paymentTerms = Array.isArray(tRes[0]) ? tRes[0] : [];

				setCustomerBar();
				populateProductFilters();
				resetAndRenderProducts();

				// Default payment term = BILL TO customer payment_term_id (preferred)
				const bill = findCustomerById(billToId);
				const sell = findCustomerById(sellToId);
				const defaultTermId =
					bill && bill.payment_term_id
						? bill.payment_term_id
						: sell
						? sell.payment_term_id
						: "";

				setPaymentTermsUI(defaultTermId);

				// dates UI
				dueDate = addDays(
					invoiceDate,
					Number(findPaymentTermById(defaultTermId)?.due_days || 0)
				);
				setInvoiceDatesUI();
			})
			.fail(function () {
				$("#productsGrid").html(
					`<div class="col-12"><div class="alert alert-danger">Failed to load JSON data.</div></div>`
				);
			});

		// Terms change => due date
		$("#paymentTermSelect").on("change", function () {
			syncDueDateFromSelectedTerm();
		});

		// Filters
		$("#searchInput").on("input", resetAndRenderProducts);
		$("#categoryFilter, #brandFilter").on("change", resetAndRenderProducts);

		// Scroll load more
		$("#productsScroll").on("scroll", function () {
			const el = this;
			const nearBottom =
				el.scrollTop + el.clientHeight >= el.scrollHeight - 120;
			if (nearBottom) loadMoreIfPossible();
		});

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
			const vid = row.data("variant-id");
			const item = cart.get(vid);
			if (!item) return;

			const qty = Math.max(
				1,
				Math.floor(safeNum(row.find(".qty-input").val()))
			);
			const maxQty = Math.max(1, safeNum(item.stock));
			item.qty = Math.min(qty, maxQty);

			const disc = Math.min(
				100,
				Math.max(0, safeNum(row.find(".disc-input").val()))
			);
			item.discountPercent = disc;

			cart.set(vid, item);
			row.find(".qty-input").val(item.qty);
			row.find(".disc-input").val(item.discountPercent);

			updateTotals();
		});

		// Remove
		$(document).on("click", ".remove-btn", function () {
			const vid = $(this).closest("tr").data("variant-id");
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

		// Print (full page)
		$("#printBtn").on("click", function () {
			const payload = buildInvoicePayload();

			// IMPORTANT: add fields the invoice template expects
			payload.invoice_number =
				payload.invoice_number ||
				"INV-GB-" + Math.floor(Math.random() * 9000 + 1000);
			payload.supply_date = payload.supply_date || payload.invoice_date;

			// optional: if you want shipping/billing as multi-line blocks like PDF
			payload.bill_to =
				payload.bill_to || payload.bill_to_customer || payload.bill_to;
			payload.ship_to =
				payload.ship_to || payload.ship_to_customer || payload.sell_to;

			// merchant details (match your PDF)
			payload.merchant = {
				display_name: "VapeShopDistroUK",
				legal_name: "Midland Sports Supplements LTD 111-113",
				address_lines: [
					"Great Bridge Street",
					"West Bromwich",
					"B70 0DA",
					"United Kingdom",
				],
				email: "manveer25012000@gmail.com",
				vat_id: "GB348681169",
			};

			// if you are using GBP
			if (!payload.currency) payload.currency = "GBP";
			payload.currency_symbol = "£";

			localStorage.setItem("pos_invoice_payload", JSON.stringify(payload));

			// open invoice and auto-print; user can "Save as PDF"
			window.open("invoice.html?autoprint=1", "_blank");
		});

		// View Invoice
		$("#viewInvoiceBtn").on("click", function () {
			const payload = buildInvoicePayload();
			payload.invoice_number =
				payload.invoice_number ||
				"INV-GB-" + Math.floor(Math.random() * 9000 + 1000);
			payload.currency = payload.currency || "GBP";
			payload.currency_symbol = "£";
			payload.merchant = {
				display_name: "VapeShopDistroUK",
				legal_name: "Midland Sports Supplements LTD 111-113",
				address_lines: [
					"Great Bridge Street",
					"West Bromwich",
					"B70 0DA",
					"United Kingdom",
				],
				email: "manveer25012000@gmail.com",
				vat_id: "GB348681169",
			};

			localStorage.setItem("pos_invoice_payload", JSON.stringify(payload));
			window.open("invoice.html", "_blank");
		});

		// Print invoice from preview modal
		$("#invoicePrintBtn").on("click", function () {
			printInvoiceFromModal();
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
