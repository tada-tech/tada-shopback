<?php
declare(strict_types=1);

namespace Tada\Shopback\Model\Config\Source\Shopback;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const UNDEFINED_OPTION_LABEL = '-- Please Select --';

    /**
     * @var string[]
     */
    protected $_stateStatuses = [
        'approved' => 'Approved',
        'rejected' => 'Rejected'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $statuses = $this->_stateStatuses;
        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        foreach ($statuses as $code => $label) {
            $options[] = ['value' => $code, 'label' => __($label)];
        }
        return $options;
    }
}
