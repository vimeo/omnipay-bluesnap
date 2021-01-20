<?php


namespace Omnipay\BlueSnap\Message;


use Omnipay\BlueSnap\Constants;
use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Abstract class for the BlueSnap Reporting API
 *
 * @link https://developers.bluesnap.com/v8976-Tools/docs/bluesnap-reporting-tools
 *
 * @package Omnipay\BlueSnap\Message
 */
abstract class ReportingAbstractRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected static $RESPONSE_CLASS = '\Omnipay\BlueSnap\Message\ReportingResponse';

    /**
     * @return string
     */
    abstract public function getReportName();

    /**
     * Returns the HTTP method to be used for this request
     *
     * @return string
     */
    public function getHttpMethod()
    {
        return Constants::HTTP_METHOD_GET;
    }

    /**
     * Overriding to provide a more precise return type
     *
     * @return ReportingResponse
     */
    public function send()
    {
        /**
         * @var ReportingResponse
         */
        return parent::send();
    }

    /**
     * @return null
     * @throws InvalidRequestException
     */
    public function getData()
    {
        $this->validate('startTime');
        $this->validate('endTime');

        if ($this->getStartTime() > $this->getEndTime()) {
            throw new InvalidRequestException('startTime cannot be greater than endTime');
        }

        return null;
    }

    /**
     * Return the API endpoint.
     *
     * @return string
     */
    public function getEndpoint()
    {
        $startTime = $this->getStartTime();
        $endTime = $this->getEndTime();
        $endpoint = parent::getEndpoint() . '/report/' . $this->getReportName();

        // this should always be true
        if ($startTime && $endTime) {
            $endpoint .= '?period=CUSTOM'
                      . '&from_date=' . urlencode((string) $startTime->format('m/d/Y'))
                      . '&to_date=' . urlencode((string) $endTime->format('m/d/Y'));
        }
        return $endpoint;
    }
}
