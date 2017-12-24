# Daloradius documentation

## Billing

With Daloradius, you can issue your invoices in two styles:

- Traditional style, as ever.
- New style.

The new style billing lets you design and configure your invoices in a more deeply way.

## Configuring the new style billing

To use the new style billing instead of the traditional one, you must add two lines to your `daloradius/library/daloradius.conf.php` file:

```
$configValues['CONFIG_INVOICE_TEMPLATE'] = 'invoice_template.html';
$configValues['CONFIG_INVOICE_ITEM_TEMPLATE'] = 'invoice_item_template.html';
```

The first line sets the HTML template for the invoice body.

The second line sets the HTML template for each invoice item.

If you want to use the traditional billing style, simply delete or comment out these two lines.

The templates are stored in the `daloradius/notifications/templates/` folder as ever.

If you are using `Locations` in your Daloradius configuration, you could define different invoice templates for each of them - ie:

```
$configValues['CONFIG_LOCATIONS'] = array(
	"International Hotel" => array(
		"Engine"   => "mysql",
		"Username" => "root",
		"Password" => "",
		"Database" => "radius",
		"Hostname" => "127.0.0.1",
		"CONFIG_INVOICE_TEMPLATE" => "invoice_template_for_international_hotel.html",
		"CONFIG_INVOICE_ITEM_TEMPLATE" => "invoice_item_template_for_international_hotel.html"
	),
	"Happy Family Hotel" => array(
		"Engine"   => "mysql",
		"Username" => "db_usertest",
		"Password" => "db_passtest",
		"Database" => "test_db1",
		"Hostname" => "localhost"
	)
);
```

In this example, the `International Hotel` location has an invoice template configuration of its own. The `Happy Family Hotel` location will use the `Default` settings (configured as traditional or new style).

## Designing and configuring the billing templates

Edit your own or an existing HTML invoice template to match your design preferences and set your company details, currency, etc.

A good start point could be copying, renaming and modifying the default templates:

* `invoice_template.html` for the invoice body.
* `invoice_item_template.html` for the invoice items.

With the new style billing system, you can use a lot of new field names, to improve the result and details of your invoices.

The new fields are for the body or the invoice items.

## New fields for the invoice body template

The field names are self explanatory:

[CustomerId]
[CustomerName]
[CustomerAddress]
[CustomerAddress2]
[CustomerPhone]
[CustomerEmail]
[CustomerContact]

[InvoiceNumber]
[InvoiceDate]
[InvoiceStatus]
[InvoiceTotalAmount]
[InvoiceTotalTax]
[InvoiceTotalBilled]
[InvoicePaid]
[InvoiceDue]
[InvoiceNotes]
[InvoiceItems]

## New fields for the invoice items template

The field names are self explanatory:

[InvoiceItemNumber]
[InvoiceItemPlan]
[InvoiceItemNotes]
[InvoiceItemAmount]
[InvoiceItemTaxAmount]
[InvoiceItemTotalAmount]
