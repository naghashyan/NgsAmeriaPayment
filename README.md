# NgsAmeriaPayment

This plugin integrates the Ameria PSP payment gateway for Shopware 6.

## Payment Bindings

The plugin now provides a `PaymentBinding` entity allowing customers to store encrypted payment tokens returned by the PSP. Bindings can be created from payment responses and queried or managed via the `BindingService`.

### Security

Tokens are stored encrypted using AES-256-GCM with the Shopware `APP_SECRET`.

## Configuration

The following options can be adjusted in the plugin system configuration:

- `enableBindings` – master switch for the feature (default: true)
- `showSaveCardCheckbox` – show a checkbox at checkout to store the card
- `allowMultipleBindings` – allow more than one saved binding per customer
- `bindingPerSalesChannel` – restrict bindings to their sales channel
- `defaultBindingBehavior` – optionally preselect the first or most recent binding

## API Endpoints

The plugin exposes simple Store API endpoints under `/store-api/ngs/payment-binding` for listing, creating and deleting bindings, as well as selecting a binding for checkout. Administrators can list and delete customer bindings through `/api/_action/ngs/payment-binding/*`.
