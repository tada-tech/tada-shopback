<?php
declare(strict_types=1);

namespace Tada\Shopback\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Class AbstractRenderer
 */
abstract class AbstractRenderer extends AbstractFieldArray
{
    /**
     * Returns renderer block for a given type
     *
     * @param string $type
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getRenderer(string $type): BlockInterface
    {
        return $this->getLayout()->createBlock(
            $type,
            '',
            ['data' => ['is_render_to_js_template' => true]]
        );
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @param array $renderers
     * @return void
     */
    protected function prepareArrayRow(DataObject $row, array $renderers): void
    {
        $attrs = [];

        foreach ($renderers as $key => $renderer) {
            $hash = $renderer->calcOptionHash($row->getData($key));

            $attrs['option_' . $hash] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $attrs);
    }
}
