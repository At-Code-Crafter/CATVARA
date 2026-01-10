/* invoice.js - renders an invoice matching your PDF layout */

(function () {
	const STORAGE_KEY = "pos_invoice_payload"; // set this from pos.js before opening invoice.html

	// update this path to where you store the logo file in your project
	const LOGO_PATH = "assets/images/logo.png";

	function qs(name) {
		const u = new URL(window.location.href);
		return u.searchParams.get(name);
	}

	function safe(n) {
		n = Number(n);
		return isNaN(n) ? 0 : n;
	}

	function money(amount, currencySymbol) {
		const v = safe(amount).toFixed(2);
		return `${currencySymbol}${v}`;
	}

	function formatLongDate(isoDate) {
		// isoDate = "YYYY-MM-DD"
		if (!isoDate) return "";
		const d = new Date(isoDate + "T00:00:00");
		const opts = { year: "numeric", month: "long", day: "numeric" };
		return d.toLocaleDateString("en-GB", opts);
	}

	function nl(linesArr) {
		return (linesArr || []).filter(Boolean).join("\n");
	}

	function attrsToText(attrsObj) {
		if (!attrsObj) return "";
		return Object.keys(attrsObj)
			.map((k) => `${k}: ${attrsObj[k]}`)
			.join(" • ");
	}

	// Build item rows similar to PDF: item row + optional discount row (amount only)
	function buildItemRows(payload, currencySymbol) {
		const vatRate = safe(payload?.charges?.vat_rate ?? 20);

		return (payload.items || [])
			.flatMap((it) => {
				const qty = safe(it.qty);
				const unit = safe(it.unit_price);
				const disc = safe(it.discount_percent);
				const gross = unit * qty;
				const discountAmount = gross * (disc / 100);
				const net = gross - discountAmount;

				const mainRow = `
        <tr>
          <td class="desc">
            ${it.name}
            ${
							attrsToText(it.attrs)
								? `<div style="color:#6b6b6b;font-size:13px;margin-top:4px;">${attrsToText(
										it.attrs
								  )}</div>`
								: ""
						}
          </td>
          <td class="qty">${qty}</td>
          <td class="unit">${money(unit, currencySymbol)}</td>
          <td class="vat">${vatRate}%</td>
          <td class="amount">${money(net, currencySymbol)}</td>
        </tr>
      `;

				// discount line like your PDF: only show negative amount in Amount column
				if (disc > 0 && discountAmount > 0.0001) {
					const discRow = `
          <tr class="discount">
            <td class="desc"></td>
            <td class="qty"></td>
            <td class="unit"></td>
            <td class="vat"></td>
            <td class="amount">-${money(discountAmount, currencySymbol)}</td>
          </tr>
        `;
					return [mainRow, discRow];
				}

				return [mainRow];
			})
			.join("");
	}

	function calcTotals(payload) {
		const vatRate = safe(payload?.charges?.vat_rate ?? 20);

		// Subtotal = sum of net line totals (after discount)
		const sub = (payload.items || []).reduce((acc, it) => {
			const qty = safe(it.qty);
			const unit = safe(it.unit_price);
			const disc = safe(it.discount_percent);
			const gross = unit * qty;
			const discountAmount = gross * (disc / 100);
			const net = gross - discountAmount;
			return acc + net;
		}, 0);

		const shipping = Math.max(0, safe(payload?.charges?.shipping ?? 0));
		const additional = Math.max(0, safe(payload?.charges?.additional ?? 0));

		// Match the PDF breakdown style:
		// VAT shown for items, and shipping VAT separately.
		const vatItems = sub * (vatRate / 100);
		const vatShipping = shipping * (vatRate / 100);
		const vatAdditional = additional * (vatRate / 100);

		const total =
			sub + vatItems + shipping + vatShipping + additional + vatAdditional;

		return {
			vatRate,
			sub,
			shipping,
			additional,
			vatItems,
			vatShipping,
			vatAdditional,
			total,
		};
	}

	function buildHeaderTop(payload, currencySymbol, hasDivider) {
		return `
    <div class="inv-top ${hasDivider ? "has-divider" : ""}">
      <div class="inv-title">INVOICE</div>

      <div class="inv-meta-block">
        <div class="label">Invoice number</div>
        <div class="value">${
					payload.invoice_number || payload.invoice_id || "INV-XXXX"
				}</div>
      </div>

      <div class="inv-meta-block inv-top-right">
        <div class="label">Invoice total</div>
        <div class="value">${money(
					payload.totals?.grand_total ?? payload._computed_total ?? 0,
					currencySymbol
				)}</div>
      </div>
    </div>
  `;
	}

	function buildMid(payload) {
		return `
      <div class="inv-mid">
        <div class="inv-logo-wrap">
          <img class="inv-logo" src="${LOGO_PATH}" alt="Logo" />
        </div>

        <div class="inv-dates">
          <div class="row">
            <div class="label">Date of issue</div>
            <div class="value">${formatLongDate(payload.invoice_date)}</div>
          </div>
          <div class="row">
            <div class="label">Date of supply</div>
            <div class="value">${formatLongDate(
							payload.supply_date || payload.invoice_date
						)}</div>
          </div>
        </div>

        <div class="inv-additional">Additional details</div>
      </div>
    `;
	}

	function buildAddresses(payload) {
		const bill = payload.bill_to || {};
		const ship = payload.ship_to || payload.sell_to || {};
		const merchant = payload.merchant || {};

		return `
      <div class="inv-addresses">
        <div class="addr">
          <h4>Bill to</h4>
          <div class="lines">${nl([
						bill.name,
						...(bill.address_lines || []),
						bill.address,
						bill.country,
					])}</div>
        </div>

        <div class="addr">
          <h4>Ship to</h4>
          <div class="lines">${nl([
						ship.name,
						...(ship.address_lines || []),
						ship.address,
						ship.country,
					])}</div>
        </div>

        <div class="addr right">
          <h4>Merchant</h4>
          <div class="lines">${nl([
						merchant.display_name,
						merchant.legal_name,
						...(merchant.address_lines || []),
						merchant.address,
						merchant.email,
						merchant.vat_id,
					])}</div>
        </div>
      </div>
    `;
	}

	function buildItemsTable(payload, currencySymbol) {
		return `
    <table class="inv-table">
      <colgroup>
        <!-- Total content width = 193mm -->
        <col style="width:105mm" />
        <col style="width:18mm" />
        <col style="width:23mm" />
        <col style="width:17mm" />
        <col style="width:30mm" />
      </colgroup>
      <thead>
        <tr>
          <th class="desc">Description</th>
          <th class="qty">Quantity</th>
          <th class="unit">Unit price</th>
          <th class="vat">VAT rate</th>
          <th class="amount">Amount</th>
        </tr>
      </thead>
      <tbody>
        ${buildItemRows(payload, currencySymbol) || ""}
      </tbody>
    </table>
  `;
	}

	function buildFooter(payload, pageNo, totalPages) {
		// Match PDF footer style (left: Provided by / VAT ID, right: Issued on / Page X of Y for INV)
		const providedBy = payload.merchant?.display_name || "VapeShopDistroUK";
		const vatId = payload.merchant?.vat_id || payload.vat_id || "GBXXXXXXXXX";
		const issued = formatLongDate(payload.invoice_date);

		return `
      <div class="inv-footer">
        <div class="left">Provided by: ${providedBy}\nVAT ID: ${vatId}</div>
        <div class="right">Issued on ${issued}\nPage ${pageNo} of ${totalPages} for ${
			payload.invoice_number || "INV-XXXX"
		}</div>
      </div>
    `;
	}

	function buildPage1(payload, totals, currencySymbol, pageNo, totalPages) {
		return `
    <div class="invoice-page">
      ${buildHeaderTop(payload, currencySymbol, true)}
      ${buildMid(payload)}
      ${buildAddresses(payload)}
      ${buildItemsTable(payload, currencySymbol)}
      ${buildFooter(payload, pageNo, totalPages)}
    </div>
  `;
	}

	function buildTotalsPage(
		payload,
		totals,
		currencySymbol,
		pageNo,
		totalPages
	) {
		const showAdditional = totals.additional > 0.0001;

		return `
    <div class="invoice-page">
      ${buildHeaderTop(payload, currencySymbol, false)}

      <div class="totals-area">
        <div class="totals">
          <div class="trow">
            <div class="lbl">Subtotal</div>
            <div class="val">${money(totals.sub, currencySymbol)}</div>
          </div>
          <div class="trow">
            <div class="lbl">VAT (${totals.vatRate}%)</div>
            <div class="val">${money(totals.vatItems, currencySymbol)}</div>
          </div>
          <div class="trow">
            <div class="lbl">Shipping</div>
            <div class="val">${money(totals.shipping, currencySymbol)}</div>
          </div>
          <div class="trow">
            <div class="lbl">Shipping VAT (${totals.vatRate}%)</div>
            <div class="val">${money(totals.vatShipping, currencySymbol)}</div>
          </div>

          ${
						showAdditional
							? `
            <div class="trow">
              <div class="lbl">Additional</div>
              <div class="val">${money(totals.additional, currencySymbol)}</div>
            </div>
            <div class="trow">
              <div class="lbl">Additional VAT (${totals.vatRate}%)</div>
              <div class="val">${money(
								totals.vatAdditional,
								currencySymbol
							)}</div>
            </div>
          `
							: ``
					}

          <div class="trow total">
            <div class="lbl">Total</div>
            <div class="val">${money(totals.total, currencySymbol)}</div>
          </div>
        </div>
      </div>

      ${buildFooter(payload, pageNo, totalPages)}
    </div>
  `;
	}

	function getPayload() {
		const raw = localStorage.getItem(STORAGE_KEY);
		if (raw) {
			try {
				return JSON.parse(raw);
			} catch (e) {}
		}

		// demo fallback (so invoice.html still works if opened directly)
		return {
			invoice_number: "INV-GB-5",
			invoice_date: "2025-12-19",
			supply_date: "2025-12-19",
			currency: "GBP",
			currency_symbol: "£",
			charges: { shipping: 8, additional: 0, vat_rate: 20 },
			merchant: {
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
			},
			bill_to: {
				name: "Anush Nisa local westhill",
				address_lines: [
					"4 Old Skene Road",
					"Unit 9",
					"Westhill",
					"AB32 6RL",
					"United Kingdom",
				],
			},
			ship_to: {
				name: "Anush Nisa local westhill",
				address_lines: [
					"4 Old Skene Road",
					"Unit 9",
					"Westhill",
					"AB32 6RL",
					"United Kingdom",
				],
			},
			items: [
				{
					name: "IVG PRO Cherry Ice Refill Pack of 5",
					qty: 3,
					unit_price: 14.99,
					discount_percent: 0,
					attrs: {},
				},
				{
					name: "IVG PRO Grape Ice Pack of 5",
					qty: 3,
					unit_price: 21.0,
					discount_percent: 28.62,
					attrs: {},
				},
			],
			totals: { grand_total: 585.18 },
		};
	}

	function normalizeForTemplate(payload) {
		// Ensure address lines arrays exist (invoice wants multi-line like PDF)
		payload.bill_to =
			payload.bill_to || payload.bill_to_customer || payload.bill_to || {};
		payload.ship_to =
			payload.ship_to ||
			payload.ship_to_customer ||
			payload.sell_to ||
			payload.ship_to ||
			{};

		// If POS payload only has "address" string, convert to lines
		["bill_to", "ship_to"].forEach((k) => {
			const obj = payload[k] || {};
			if (!obj.address_lines) {
				if (obj.address && typeof obj.address === "string")
					obj.address_lines = obj.address.split(",").map((s) => s.trim());
				else obj.address_lines = [];
			}
			payload[k] = obj;
		});

		// Merchant defaults (from your PDF)
		payload.merchant = payload.merchant || {
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

		// Currency
		if (!payload.currency_symbol) {
			payload.currency_symbol =
				payload.currency === "GBP"
					? "£"
					: payload.currency === "EUR"
					? "€"
					: payload.currency === "USD"
					? "$"
					: "";
		}

		return payload;
	}

	function render() {
		let payload = normalizeForTemplate(getPayload());
		const currencySymbol = payload.currency_symbol || "£";

		const totals = calcTotals(payload);
		payload._computed_total = totals.total;

		// We always output 2 pages like your PDF: (1) Items (2) Totals
		const totalPages = 2;

		const page1 = buildPage1(payload, totals, currencySymbol, 1, totalPages);
		const page2 = buildTotalsPage(
			payload,
			totals,
			currencySymbol,
			2,
			totalPages
		);

		document.getElementById("invoicePages").innerHTML = page1 + page2;

		// Auto print if requested
		if (qs("autoprint") === "1") {
			setTimeout(() => window.print(), 300);
		}
	}

	render();
})();
