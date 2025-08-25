# NgsAmeriaPayment

This plugin integrates the Ameria PSP payment gateway for Shopware 6.

## Payment Bindings

The plugin now provides a `PaymentBinding` entity allowing customers to store encrypted payment tokens returned by the PSP. Bindings can be created from payment responses and queried or managed via the `BindingService`.

### Security

Tokens are stored encrypted using AES-256-GCM with the Shopware `APP_SECRET`.
