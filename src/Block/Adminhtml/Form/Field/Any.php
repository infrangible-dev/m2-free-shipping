<?php

declare(strict_types=1);

namespace Infrangible\FreeShipping\Block\Adminhtml\Form\Field;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Any
    extends Select
{
    /** @var Yesno */
    protected $sourceYesno;

    /**
     * @param Context $context
     * @param Yesno   $sourceYesno
     * @param array   $data
     */
    public function __construct(Context $context, Yesno $sourceYesno, array $data = [])
    {
        parent::__construct($context, $data);

        $this->sourceYesno = $sourceYesno;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setInputName(string $value): Select
    {
        return $this->setData('name', $value);
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function setInputId($value): Select
    {
        return $this->setId($value);
    }

    /**
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->sourceYesno->toOptionArray());
        }

        return parent::_toHtml();
    }
}
