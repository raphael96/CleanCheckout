<?php

namespace Rubic\CleanCheckout\Plugin;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ModifyCheckoutLayoutPlugin
{
    const CONFIG_DISABLE_LOGIN_PATH = 'clean_checkout/general/disable_login_popup';
    const CONFIG_DISABLE_FIELD_PATH = 'clean_checkout/general/disable_%s';
    const CONFIG_MOVE_CART_ITEMS    = 'clean_checkout/general/move_cart_items';

    /**
     * Shipping address fields that can be disabled by backend configuration.
     *
     * @var array
     */
    const DISABLE_FIELDS = [
        'telephone',
        'company'
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Disables authentication modal.
     *
     * @param $jsLayout
     * @return array
     */
    private function disableAuthentication($jsLayout)
    {
        if ($this->scopeConfig->getValue(self::CONFIG_DISABLE_LOGIN_PATH)) {
            unset($jsLayout['components']['checkout']['children']['authentication']);
        }
        return $jsLayout;
    }

    /**
     * Changes cart items to be above totals in the cart summary.
     *
     * @param $jsLayout
     * @return array
     */
    private function changeCartItemsSortOrder($jsLayout)
    {
        if ($this->scopeConfig->getValue(self::CONFIG_MOVE_CART_ITEMS)) {
            $jsLayout['components']['checkout']
            ['children']
            ['sidebar']
            ['children']
            ['summary']
            ['children']
            ['cart_items']
            ['sortOrder'] = 0;
        }
        return $jsLayout;
    }

    /**
     * Disables specific input fields in shipping address fieldset.
     *
     * @param $jsLayout
     * @return array
     */
    private function disableFields($jsLayout)
    {
        foreach (self::DISABLE_FIELDS as $field) {
            $configPath = sprintf(self::CONFIG_DISABLE_FIELD_PATH, $field);
            if ($this->scopeConfig->getValue($configPath)) {
                unset(
                    $jsLayout['components']['checkout']
                    ['children']
                    ['steps']
                    ['children']
                    ['shipping-step']
                    ['children']
                    ['shippingAddress']
                    ['children']
                    ['shipping-address-fieldset']
                    ['children']
                    [$field]
                );
            }
        }
        return $jsLayout;
    }

    /**
     * @param LayoutProcessor $layoutProcessor
     * @param callable $proceed
     * @param array $args
     * @return array
     */
    public function aroundProcess(LayoutProcessor $layoutProcessor, callable $proceed, ...$args)
    {
        $jsLayout = $proceed(...$args);

        $jsLayout = $this->disableAuthentication($jsLayout);
        $jsLayout = $this->disableFields($jsLayout);
        $jsLayout = $this->changeCartItemsSortOrder($jsLayout);

        return $jsLayout;
    }
}