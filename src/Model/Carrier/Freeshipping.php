<?php

declare(strict_types=1);

namespace Infrangible\FreeShipping\Model\Carrier;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Attribute;
use Infrangible\Core\Helper\Database;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Freeshipping
    extends \Magento\OfflineShipping\Model\Carrier\Freeshipping
{
    /** @var Variables */
    protected $variables;

    /** @var Arrays */
    protected $arrays;

    /** @var Json */
    protected $json;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var Database */
    protected $databaseHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Variables $variables,
        Arrays $arrays,
        Json $json,
        Attribute $attributeHelper,
        Database $databaseHelper,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $rateMethodFactory, $data);

        $this->variables = $variables;
        $this->arrays = $arrays;
        $this->json = $json;
        $this->attributeHelper = $attributeHelper;
        $this->databaseHelper = $databaseHelper;
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigData('check_product_attributes')) {
            return parent::collectRates($request);
        }

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->_rateResultFactory->create();

        $this->_updateFreeMethodQuote($request);

        if ($request->getFreeShipping() || $this->isFreeShippingRequired($request)) {
            $method = $this->_rateMethodFactory->create();

            $method->setDataUsingMethod('carrier', 'freeshipping');
            $method->setDataUsingMethod('carrier_title', $this->getConfigData('title'));
            $method->setDataUsingMethod('method', 'freeshipping');
            $method->setDataUsingMethod('method_title', $this->getConfigData('name'));
            $method->setPrice('0.00');
            $method->setDataUsingMethod('cost', '0.00');

            $result->append($method);
        } elseif ($this->getConfigData('showmethod')) {
            $error = $this->_rateErrorFactory->create();

            $error->setDataUsingMethod('carrier', $this->_code);
            $error->setDataUsingMethod('carrier_title', $this->getConfigData('title'));
            $errorMsg = $this->getConfigData('specificerrmsg');
            $error->setDataUsingMethod(
                'error_message',
                $errorMsg ? : __('Sorry, but we can\'t deliver to the destination country with this shipping module.')
            );

            return $error;
        }

        return $result;
    }

    private function isFreeShippingRequired(RateRequest $request): bool
    {
        if ($this->checkProductAttributes($request) === false) {
            return false;
        }

        $minSubtotal = $request->getPackageValueWithDiscount();

        if ($request->getDataUsingMethod('base_subtotal_with_discount_incl_tax')
            && $this->getConfigFlag(
                'tax_including'
            )) {

            $minSubtotal = $request->getDataUsingMethod('base_subtotal_with_discount_incl_tax');
        }

        return $minSubtotal >= $this->getConfigData('free_shipping_subtotal');
    }

    private function checkProductAttributes(RateRequest $request): bool
    {
        $result = true;

        $productAttributes = $this->getConfigData('product_attributes');

        if (!$this->variables->isEmpty($productAttributes)) {
            $dbAdapter = $this->databaseHelper->getDefaultConnection();

            $productAttributes = $this->json->decode($productAttributes);

            foreach ($productAttributes as $productAttributeData) {
                $attributeId = $this->arrays->getValue($productAttributeData, 'product_attribute');
                $value = $this->arrays->getValue($productAttributeData, 'value');
                $any = $this->arrays->getValue($productAttributeData, 'any');

                $attributeResult = !$any;

                if ($attributeId) {
                    try {
                        $attribute = $this->attributeHelper->getAttribute(Product::ENTITY, $attributeId);

                        foreach ($request->getAllItems() as $item) {
                            if ($item instanceof AbstractItem) {
                                $product = $item->getProduct();

                                $productValue = $this->attributeHelper->getAttributeValue(
                                    $dbAdapter,
                                    Product::ENTITY,
                                    $attribute->getAttributeCode(),
                                    intval($product->getId()),
                                    intval($product->getStoreId())
                                );

                                if ($any) {
                                    $attributeResult = $attributeResult || $productValue == $value;
                                } else {
                                    $attributeResult = $attributeResult && $productValue == $value;
                                }
                            }
                        }
                    } catch (\Exception $exception) {
                        $this->_logger->error($exception);
                    }
                }

                $result = $result && $attributeResult;
            }
        }

        return $result;
    }
}
