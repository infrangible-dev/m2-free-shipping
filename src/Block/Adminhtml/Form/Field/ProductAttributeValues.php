<?php

declare(strict_types=1);

namespace Infrangible\FreeShipping\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ProductAttributeValues
    extends AbstractFieldArray
{
    /** @var ProductAttribute */
    private $productAttributeRenderer;

    /** @var Any */
    private $anyRenderer;

    /**
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'product_attribute',
            ['label' => __('Product Attribute'), 'renderer' => $this->getProductAttributeRenderer()]
        );
        $this->addColumn(
            'value', ['label' => __('Value'), 'style' => 'width:150px;', 'class' => 'required-entry']
        );
        $this->addColumn(
            'any', ['label' => __('Any'), 'renderer' => $this->getAnyRenderer()]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     *
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $productAttributeValue = $row->getData('product_attribute');

        if ($productAttributeValue !== null) {
            $options['option_'.$this->getProductAttributeRenderer()->calcOptionHash($productAttributeValue)] =
                'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return ProductAttribute
     * @throws LocalizedException
     */
    private function getProductAttributeRenderer(): ProductAttribute
    {
        if (!$this->productAttributeRenderer) {
            $this->productAttributeRenderer = $this->getLayout()->createBlock(
                ProductAttribute::class, '',
                ['data' => ['is_render_to_js_template' => true, 'extra_params' => 'style="width:150px;"']]
            );
        }

        return $this->productAttributeRenderer;
    }

    /**
     * @return Any
     * @throws LocalizedException
     */
    private function getAnyRenderer(): Any
    {
        if (!$this->anyRenderer) {
            $this->anyRenderer = $this->getLayout()->createBlock(
                Any::class, '',
                ['data' => ['is_render_to_js_template' => true, 'extra_params' => 'style="width:150px;"']]
            );
        }

        return $this->anyRenderer;
    }
}
