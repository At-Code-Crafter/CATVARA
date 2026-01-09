/* customers.js (Customer page logic) */

(function () {
 
  let customers = [];
  let sellTo = null;
  let billTo = null;

  function qs(name) {
    const url = new URL(window.location.href);
    return url.searchParams.get(name);
  }

  function setPageQueryParams() {
    const url = new URL(window.location.href);

    if (sellTo && sellTo.uuid) url.searchParams.set("sell_to", sellTo.uuid);
    else url.searchParams.delete("sell_to");

    const same = $("#sameAsSellTo").is(":checked");
    const billId = same ? (sellTo ? sellTo.uuid : "") : (billTo ? billTo.uuid : "");

    if (billId) url.searchParams.set("bill_to", billId);
    else url.searchParams.delete("bill_to");

    history.replaceState({}, "", url.toString());
  }

  function getFilters() {
    return {
      q: ($("#customerSearch").val() || "").trim().toLowerCase(),
      type: $("#typeFilter").val() || "",
      company: $("#companyFilter").val() || "",
    };
  }

  function filterCustomers() {
    const { q, type, company } = getFilters();
    return customers.filter((c) => {
      const okQ = !q || (c.name + " " + c.email + " " + c.phone).toLowerCase().includes(q);
      const okType = !type || c.customerType === type;
      const okCompany =
        !company ||
        (company === "company" ? c.isCompany : !c.isCompany);
      return okQ && okType && okCompany;
    });
  }

  function cardHtml(c, role, selectedId) {
    const tax = c.isCompany
      ? `<span class="pill mr-2">${c.customerType}</span><span class="pill">Tax: ${c.taxNumber}</span>`
      : `<span class="pill">${c.customerType}</span>`;

    const active = selectedId === c.uuid ? "active" : "";

    return `
      <div class="mb-2">
        <div class="customer-card ${active}" data-role="${role}" data-id="${c.uuid}">
          <div class="avatar">${c.initial}</div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
              <div class="c-title">${c.name}</div>
              <div>${tax}</div>
            </div>
            <p class="c-line mb-1">${c.address}</p>
            <p class="c-meta mb-0">${c.phone} • ${c.email}</p>
          </div>
        </div>
      </div>
    `;
  }

  function renderLists() {
    const list = filterCustomers();

    $("#sellToList").html(
      list.map((c) => cardHtml(c, "sell", sellTo ? sellTo.uuid : null)).join("") ||
        `<div class="p-3 text-muted">No customers found.</div>`
    );

    $("#billToList").html(
      list.map((c) => cardHtml(c, "bill", billTo ? billTo.uuid : null)).join("") ||
        `<div class="p-3 text-muted">No customers found.</div>`
    );
  }

  function renderSummary() {
    const same = $("#sameAsSellTo").is(":checked");

    if (sellTo) {
      $("#sellToBadge").text(sellTo.isCompany ? `${sellTo.customerType} • ${sellTo.initial}` : sellTo.initial);
      $("#sellToSummary").html(`
        <div><strong>${sellTo.name}</strong></div>
        <div>${sellTo.address}</div>
        <div class="text-muted">${sellTo.phone} • ${sellTo.email}</div>
        ${sellTo.isCompany ? `<div class="text-muted">Tax: ${sellTo.taxNumber} • ${sellTo.customerType}</div>` : ``}
      `);
    } else {
      $("#sellToBadge").text("Not selected");
      $("#sellToSummary").text("Not selected.");
    }

    if (same) {
      $("#billToBadge").text("Same as Sell To");
      $("#billToSummary").text("Same as Sell To.");
    } else if (billTo) {
      $("#billToBadge").text(billTo.isCompany ? `${billTo.customerType} • ${billTo.initial}` : billTo.initial);
      $("#billToSummary").html(`
        <div><strong>${billTo.name}</strong></div>
        <div>${billTo.address}</div>
        <div class="text-muted">${billTo.phone} • ${billTo.email}</div>
        ${billTo.isCompany ? `<div class="text-muted">Tax: ${billTo.taxNumber} • ${billTo.customerType}</div>` : ``}
      `);
    } else {
      $("#billToBadge").text("Not selected");
      $("#billToSummary").text("Not selected.");
    }

    const okSell = !!sellTo;
    const okBill = same ? true : !!billTo;
    $("#continueBtn").prop("disabled", !(okSell && okBill));

    // keep billing default to sell-to when same
    if (same && sellTo) {
      billTo = sellTo;
    }

    setPageQueryParams();
  }

  function findCustomerById(uuid) {
    return customers.find((c) => c.uuid === uuid) || null;
  }

  function hydrateFromUrlIfPresent() {
    const sellId = qs("sell_to");
    const billId = qs("bill_to");

    if (sellId) sellTo = findCustomerById(sellId);

    // if bill_to exists and differs, auto uncheck
    if (billId && sellTo && billId !== sellTo.uuid) {
      $("#sameAsSellTo").prop("checked", false);
      $("#billToWrap").removeClass("d-none");
      billTo = findCustomerById(billId);
    } else {
      $("#sameAsSellTo").prop("checked", true);
      $("#billToWrap").addClass("d-none");
      billTo = sellTo;
    }
  }

  function goToPos() {
    const same = $("#sameAsSellTo").is(":checked");
    const sellId = sellTo ? sellTo.uuid : "";
    const billId = same ? sellId : (billTo ? billTo.uuid : "");

    // Prepare payload
    const payload = {
      sell_to: sellId,
      bill_to: billId,
      _token: $('meta[name="csrf-token"]').attr('content') 
    };

    const btn = $("#continueBtn");
    const originalText = btn.text();
    btn.prop("disabled", true).text("Creating Draft...");

    $.post(STORE_ORDER_URL, payload)
      .done(function(res) {
        if (res.success && res.redirect_url) {
          window.location.href = res.redirect_url;
        } else {
          alert("Error creating draft order.");
          btn.prop("disabled", false).text(originalText);
        }
      })
      .fail(function(xhr) {
        alert("Failed to create draft order: " + (xhr.responseJSON?.message || "Unknown error"));
        btn.prop("disabled", false).text(originalText);
      });
  }

  $(function () {
    $.getJSON(DATA_URL)
      .done(function (data) {
        customers = Array.isArray(data) ? data : [];

        hydrateFromUrlIfPresent();
        renderLists();
        renderSummary();
      })
      .fail(function () {
        $("#sellToList").html(`<div class="p-3 text-danger">Failed to load customers.json</div>`);
      });

    $("#customerSearch").on("input", renderLists);
    $("#typeFilter, #companyFilter").on("change", renderLists);

    $(document).on("click", '.customer-card[data-role="sell"]', function () {
      const id = $(this).data("id");
      sellTo = findCustomerById(id);

      if ($("#sameAsSellTo").is(":checked")) {
        billTo = sellTo;
      } else {
        // keep billTo default as sellTo until user changes
        billTo = sellTo;
      }

      renderLists();
      renderSummary();
    });

    $("#sameAsSellTo").on("change", function () {
      const same = $(this).is(":checked");
      if (same) {
        billTo = sellTo;
        $("#billToWrap").addClass("d-none");
      } else {
        billTo = sellTo; // default
        $("#billToWrap").removeClass("d-none");
      }
      renderLists();
      renderSummary();
    });

    $(document).on("click", '.customer-card[data-role="bill"]', function () {
      if ($("#sameAsSellTo").is(":checked")) return;
      const id = $(this).data("id");
      billTo = findCustomerById(id);
      renderLists();
      renderSummary();
    });

    $("#continueBtn").on("click", function () {
      if ($(this).prop("disabled")) return;
      goToPos();
    });
  });
})();
