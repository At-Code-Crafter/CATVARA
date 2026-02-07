<?php

return [
    /*
     |----------------------------------------------------------------------
     | Generic activity logging allow/deny
     |----------------------------------------------------------------------
     | Use the generic observer only for low-risk/reference models.
     | Primary/operational models should have custom observers.
     */
    'generic_allow' => [
        // Auth & access pivots
        \App\Models\Auth\CompanyUser::class,
        \App\Models\Auth\CompanyUserRole::class,
        \App\Models\Auth\Role::class,
        \App\Models\Auth\Permission::class,
        \App\Models\Auth\Module::class,

        // Catalog supporting models
        \App\Models\Catalog\AttributeValue::class,
        \App\Models\Catalog\CategoryAttribute::class,

        // Common/shared models
        \App\Models\Common\Address::class,
        \App\Models\Common\Attachment::class,
        \App\Models\Attachment::class,
        \App\Models\Common\Country::class,
        \App\Models\Common\State::class,

        // Company meta
        \App\Models\Company\CompanyStatus::class,
        \App\Models\Company\CompanyDetail::class,

        // Inventory meta
        \App\Models\Inventory\InventoryLocation::class,
        \App\Models\Inventory\InventoryReason::class,
        \App\Models\Inventory\Store::class,
        \App\Models\Inventory\Warehouse::class,

        // Pricing meta
        \App\Models\Pricing\Currency::class,
        \App\Models\Pricing\ExchangeRate::class,
        \App\Models\Pricing\PriceChannel::class,
        \App\Models\Pricing\StoreVariantPrice::class,
        \App\Models\Pricing\VariantPrice::class,
        \App\Models\Accounting\PaymentMethod::class,
        \App\Models\Accounting\PaymentTerm::class,

        // Sales/operations statuses
        \App\Models\Accounting\InvoiceStatus::class,
        \App\Models\Accounting\PaymentStatus::class,
        \App\Models\Inventory\InventoryTransferStatus::class,
        \App\Models\Pos\PosOrderStatus::class,
        \App\Models\Sales\OrderStatus::class,
        \App\Models\Sales\QuoteStatus::class,

        // Taxes
        \App\Models\Tax\TaxGroup::class,
        \App\Models\Tax\TaxRate::class,
    ],

    'generic_deny' => [
        // Prevent recursion
        \App\Models\Common\ActivityLog::class,
    ],
];
