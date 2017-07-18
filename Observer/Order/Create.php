<?php

namespace Shopwoo\MagentoWebhooks\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Shopwoo\MagentoWebhooks\Observer\WebhookAbstract;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;
/**
 * Class Customer
 */
class Create extends WebhookAbstract
{
    private $order;

    public function __construct(
        LoggerInterface $logger,
        Curl $curlAdapter,
        Data $jsonHelper,
        Order $order
    ) {
        $this->_logger = $logger;
        $this->_curlAdapter = $curlAdapter;
        $this->_jsonHelper = $jsonHelper;
        $this->order = $order;
    }

    protected function _getWebhookEvent()
    {
        return 'order/place';
    }

    protected function _getWebhookData(Observer $observer)
    {
        $order = $observer->getEvent()->getOrderIds();
        $order->getAllVisibleItems();

        /**
         * TODO: Add some type of serialization which filters the
         * actual fields that get returned from the object. Returning
         * this raw data is dangerous and can expose sensitive data.
         *
         * Ideally this representation of the object will match that
         * of the json rest api. Maybe we can tap into that serializer?
         */
        return [
            'order' => $order->getData()
        ];
    }
}
