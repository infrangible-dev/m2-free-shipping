<?php

declare(strict_types=1);

namespace Infrangible\FreeShipping\Plugin\Shipping\Model;

use Infrangible\Core\Helper\Stores;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Shipping
{
    /** @var Stores */
    protected $storeHelper;

    /**
     * @param Stores $storeHelper
     */
    public function __construct(Stores $storeHelper)
    {
        $this->storeHelper = $storeHelper;
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function afterCollectRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Magento\Shipping\Model\Shipping $result,
        RateRequest $request
    ): \Magento\Shipping\Model\Shipping {
        if ($this->storeHelper->getStoreConfigFlag('carriers/freeshipping/active')) {
            if ($this->storeHelper->getStoreConfigFlag('carriers/freeshipping/disable_other')) {
                $rateResult = $result->getResult();

                $hasFreeShipping = false;
                $freeShippingResultMethod = null;

                foreach ($rateResult->getAllRates() as $rateResultMethod) {
                    if ($rateResultMethod->getData('method') === 'freeshipping') {
                        $hasFreeShipping = true;
                        $freeShippingResultMethod = $rateResultMethod;

                        break;
                    }
                }

                if ($hasFreeShipping && $freeShippingResultMethod) {
                    $result->resetResult();

                    $rateResult = $result->getResult();

                    if ($freeShippingResultMethod instanceof Result) {
                        $rateResult->appendResult($freeShippingResultMethod, true);
                    } else {
                        $rateResult->append($freeShippingResultMethod);
                    }
                }
            }
        }

        return $result;
    }
}
