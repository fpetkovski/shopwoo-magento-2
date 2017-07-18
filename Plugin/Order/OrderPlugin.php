<?php
namespace Shopwoo\MagentoWebhooks\Plugin\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

use Magento\Framework\HTTP\Adapter\Curl;
use Magento\Framework\Json\Helper\Data;

use Magento\Sales\Model\Service\OrderService;
use Magento\Store\Model\StoreManager;
use Psr\Log\LoggerInterface;

class OrderPlugin
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Curl Adapter
     */
    protected $curlAdapter;

    /**
     * Json Helper
     * @var [type]
     */
    protected $jsonHelper;

    /** @var StoreManager $storeManager */
    protected $storeManager;

    public function __construct(
        LoggerInterface $logger, Curl $curlAdapter, Data $jsonHelper,
        StoreManager $storeManager)
    {
        $this->logger = $logger;
        $this->curlAdapter = $curlAdapter;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
    }

    public function afterPlace(OrderService $orderService, Order $order) {
        $this->sendWebhook('https://requestb.in/tl29nftl', [
            'order_id' => $order->getId(),
            'domain'   => $order->getStore()->getBaseUrl()
        ]);

        return $order;
    }

    protected function sendWebhook($url, $body)
    {
        $this->logger->debug("Sending webhook for event order/placeAfter to " . $url);

        $bodyJson = $this->jsonHelper->jsonEncode($body);

        $headers = ["Content-Type: application/json"];
        $this->curlAdapter->write('POST', $url, '1.1', $headers, $bodyJson);
        $this->curlAdapter->read();
        $this->curlAdapter->close();
    }
}