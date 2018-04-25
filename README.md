# Omnipay: BlueSnap

**BlueSnap driver for the Omnipay PHP payment processing library**

_NOTE: At this time, this driver only provides full support for BlueSnap's [BuyNow Hosted Checkout](https://home.bluesnap.com/features-tools/flexible-integration-options/hosted-checkout/) product. But you can add support for more parts of the API!_

[![Build Status](https://travis-ci.org/vimeo/omnipay-bluesnap.png?branch=master)](https://travis-ci.org/vimeo/omnipay-bluesnap)
[![Latest Stable Version](https://poser.pugx.org/vimeo/omnipay-bluesnap/version.png)](https://packagist.org/packages/vimeo/omnipay-bluesnap)
[![Total Downloads](https://poser.pugx.org/vimeo/omnipay-bluesnap/d/total.png)](https://packagist.org/packages/vimeo/omnipay-bluesnap)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment processing library for PHP 5.3+. This package implements BlueSnap support for Omnipay.

[BlueSnap](https://bluesnap.com/) is a payment services provider based in Waltham, Massachusetts. In addition to traditional payment gateway services, they provide hosted solutions and a range of global payment methods.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it to your `composer.json` file:

```json
{
    "require": {
        "vimeo/omnipay-bluesnap": "2.0.*"
    }
}
```

And run composer to update your dependencies:

```
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update
```

_(Note that we recommend pinning the minor version. While `v2.*` will remain fully compatible with Omnipay 2, features added in addition to the base Omnipay features, such as subscriptions, may have breaking changes in a minor version bump.)_

## Basic Usage

The following gateways are provided by this package:

* BlueSnap_HostedCheckout, for [BlueSnap's BuyNow Hosted Checkout](https://support.bluesnap.com/v2.2.7/docs/intro-extended-api) page.

Some features of the following gateways are provided, but not enough to use them on their own. Feel free to contribute!

* BlueSnap, for BlueSnap's traditional [Payment API](https://developers.bluesnap.com/v8976-JSON/docs). This gateway is almost entirely unimplemented at this time, except for parts of the [Reporting API](https://developers.bluesnap.com/v8976-Tools/docs).
* BlueSnap\_Extended, for the BlueSnap [Extended Payment API](https://support.bluesnap.com/v2.2.7/docs/intro-extended-api). This is the API you should use if you're hosting your product catalog with BlueSnap. It powers the BuyNow Hosted Checkout, as well as other products. Enough of this API is implemented in this driver to power the BlueSnap\_HostedCheckout gateway.

### Simple Example

```php
// Set up the gateway
$gateway = \Omnipay\Omnipay::create('BlueSnap_HostedCheckout');
$gateway->setUsername('your_username');
$gateway->setPassword('y0ur_p4ssw0rd');
$gateway->setTestMode(false);

// Start the purchase process
$purchaseResponse = $gateway->purchase(array(
    'storeReference' => '12345',
    'planReference' => '1234567',
    'currency' => 'USD'
))->send();

if ($purchaseResponse->isSuccessful()) {
    $purchaseResponse->redirect();
} else {
    // error handling
}

// Now the user is filling out info on BlueSnap's hosted checkout page. Then they get
// redirected back to your site. If you set parameters in the return/callback URL, you
// can access those here.

// Once the transaction has been captured, you'll receive an IPN callback, which you can
// handle like so:

$ipnCallback = $gateway->parseIPNCallback($_SERVER['REQUEST_URI']);
if ($ipnCallback->isCharge()) {
    echo 'Transaction reference: ' . $ipnCallback->getTransactionReference() . PHP_EOL;
    echo 'Amount: ' . $ipnCallback->getAmount() . PHP_EOL;
    echo 'Currency: ' . $ipnCallback->getCurrency() . PHP_EOL;
} elseif ($ipnCallback->isCancellation()) {
    // etc.
}
```

More documentation and examples are provided in the Gateway source files.

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay) repository.

## Test Mode

BlueSnap accounts have a separate username and password for test mode. There is also a separate test mode endpoint, which this library will use when set to test mode.

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/Vimeo/omnipay-bluesnap/issues), or better yet, fork the library and submit a pull request.

If you are having general issues with Omnipay, we suggest posting on [Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release announcements, discuss ideas for the project, or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which you can subscribe to.
