<?php


namespace Omnipay\BlueSnap;


class Types
{
    // These values are returned in the Extended API response which is in xml
    const REVERSAL_CHARGEBACK = 'CHARGEBACK';
    const REVERSAL_REFUND = 'REFUND';

    // Although similar to the above, these values are returned in the standard (reporting) API.
    const TRANSACTION_CHARGEBACK = 'Chargeback';
    const TRANSACTION_SALE = 'Sale';
    const TRANSACTION_REFUND = 'Refund';
}
