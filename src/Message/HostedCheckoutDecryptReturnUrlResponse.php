<?php


namespace Omnipay\BlueSnap\Message;


use SimpleXMLElement;

class HostedCheckoutDecryptReturnUrlResponse extends AbstractResponse
{
    /**
     * Get the decrypted parameters from the return url after a HostedCheckoutDecryptReturnUrlRequest
     * Returns an array of paramName => paramValue
     *
     * @return array|null
     * @psalm-suppress MixedPropertyFetch
     */
    public function getDecryptedParameters()
    {
        if ($this->data instanceof SimpleXMLElement && isset($this->data->{'decrypted-token'})) {
            parse_str((string) $this->data->{'decrypted-token'}, $result);
            return $result ?: null;
        }
        return null;
    }
}
