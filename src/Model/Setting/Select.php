<?php

namespace Retailcrm\Retailcrm\Model\Setting;

class Select implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => true, 'label' => __('Yes')],
            ['value' => false, 'label' => __('No')]
        ];
    }
}
