<?php

namespace Shopwoo\MagentoWebhooks\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;


/**
 * Class Customer
 */
abstract class WebhookAbstract implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * Curl Adapter
     */
    protected $_curlAdapter;

    /**
     * Json Helper
     * @var [type]
     */
    protected $_jsonHelper;

    /**
     * Webhook factory
     * @var [type]
     */
    protected $_webhookFactory;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger,
        Curl $curlAdapter,
        Data $jsonHelper
    ) {
        $this->_logger = $logger;
        $this->_curlAdapter = $curlAdapter;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $eventCode = $this->_getWebhookEvent();
        $eventData = $this->_getWebhookData($observer);

        $body = [
            'event' => $eventCode,
            'data'  => $eventData
        ];

        $bodyJson = $this->_jsonHelper->jsonEncode($body);

        $this->_logger->info("Shopwoo event firing");
        $this->_logger->info("Shopwoo event firing", $bodyJson);
        $this->_logger->log(\Psr\Log\LogLevel::DEBUG, "test", $bodyJson);

        $this->_sendWebhook('https://requestb.in/tl29nftl', $body);

    }

    protected function _sendWebhook($url, $body)
    {
        $this->_logger->debug("Sending webhook for event " . $this->_getWebhookEvent() . " to " . $url);

        $bodyJson = $this->_jsonHelper->jsonEncode($body);

        $headers = ["Content-Type: application/json"];
        $this->_curlAdapter->write('POST', $url, '1.1', $headers, $bodyJson);
        $this->_curlAdapter->read();
        $this->_curlAdapter->close();
    }

    abstract protected function _getWebhookEvent();

    abstract protected function _getWebhookData(Observer $observer);
}
