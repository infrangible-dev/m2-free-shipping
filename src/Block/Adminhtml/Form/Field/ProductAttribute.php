<?php

declare(strict_types=1);

namespace Infrangible\FreeShipping\Block\Adminhtml\Form\Field;

use Infrangible\Core\Model\Config\Source\Attribute\Product;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ProductAttribute
    extends Select
{
    /** @var Product */
    protected $sourceProductAttribute;

    /**
     * @param Context $context
     * @param Product $sourceProductAttribute
     * @param array   $data
     */
    public function __construct(Context $context, Product $sourceProductAttribute, array $data = []) {
        parent::__construct($context, $data);

        $this->sourceProductAttribute = $sourceProductAttribute;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInputName(string $value): ProductAttribute
    {
        return $this->setData('name', $value);
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setInputId($value): ProductAttribute
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->sourceProductAttribute->toOptionArray());
        }

        return parent::_toHtml();
    }
}
