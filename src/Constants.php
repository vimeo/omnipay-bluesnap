<?php

namespace Omnipay\BlueSnap;

/**
 * Constants used across different classes in this library.
 */
class Constants
{
    /**
     * All timestamps returned from BlueSnap are in this time zone.
     * All timestamps sent to BlueSnap must be in this time zone.
     * NOTE: Etc/GMT+8 is actually GMT-8. It is the time zone known as PST.
     * BlueSnap does not observe daylight savings.
     */
    const BLUESNAP_TIME_ZONE = 'Etc/GMT+8';

    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_POST = 'POST';

    // These values are returned in the Extended API response
    const REVERSAL_CHARGEBACK = 'CHARGEBACK';
    const REVERSAL_REFUND = 'REFUND';

    // Although similar to the above, these values are returned in the standard API using the reporting tools.
    const TRANSACTION_TYPE_CHARGEBACK = 'Chargeback';
    const TRANSACTION_TYPE_REFUND = 'Refund';
    const TRANSACTION_TYPE_SALE = 'Sale';
}
