<?php

namespace Retailcrm\Retailcrm\Block\Adminhtml\System\Config\Form\Fieldset;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Site extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Dummy element
     *
     * @var \Magento\Framework\DataObject
     */
    protected $_dummyElement;

    /**
     * Field renderer
     *
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    private $objectFactory;
    private $client;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Retailcrm\Retailcrm\Helper\Proxy $client,
        \Magento\Framework\DataObjectFactory $objectFactory,
        array $data = []
    ) {
        $this->client = $client;
        $this->objectFactory = $objectFactory;

        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    /**
     * Get field renderer
     *
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->getLayout()->getBlockSingleton(
                \Magento\Config\Block\System\Config\Form\Field::class
            );
        }

        return $this->_fieldRenderer;
    }

    /**
     * Get dummy element
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = $this->objectFactory->create(['showInDefault' => 1, 'showInWebsite' => 0]);
        }

        return $this->_dummyElement;
    }

    /**
     * Render element
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '';
        $htmlError = sprintf(
            '<div style="margin-left: 15px;"><b><i>%s</i></b></div>',
            __('Enter API of your URL and API key')
        );
        $html .= $this->_getHeaderHtml($element);

        if ($this->client->isConfigured()) {
            $html .= $this->_getFieldHtml($element);
        } else {
            $html .= $htmlError;
        }

        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * Get options values
     *
     * @return array
     */
    private function getValues()
    {
        $defaultValues = [
            [
                'value' => '',
                'label' => ''
            ]
        ];

        $values = [];

        $response = $this->client->sitesList();

        if ($response === false) {
            return $defaultValues;
        }

        if ($response->isSuccessful()) {
            $sites = $response['sites'];
        } else {
            return $defaultValues;
        }

        foreach ($sites as $site) {
            $values[] = [
                'label' => $site['name'],
                'value' => $site['code']
            ];
        }

        return $values;
    }

    /**
     * Get field html
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $fieldset
     * @param \Magento\Shipping\Model\Carrier\AbstractCarrier $shipping
     *
     * @return string
     */
    protected function _getFieldHtml($fieldset)
    {
        $configData = $this->getConfigData();
        $path = 'retailcrm/' . $fieldset->getId() . '/default';
        $data = isset($configData[$path]) ? $configData[$path] : [];
        $e = $this->_getDummyElement();

        $field = $fieldset->addField(
            'default',
            'select',
            [
                'name' => 'groups[' . $fieldset->getId() . '][fields][default][value]',
                'label' => __('Default site'),
                'value' => isset($data) ? $data : '',
                'values' => $this->getValues(),
                'inherit' => isset($data['inherit']) ? $data['inherit'] : '',
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e)
            ]
        )->setRenderer(
            $this->_getFieldRenderer()
        );

        return $field->toHtml();
    }
}
