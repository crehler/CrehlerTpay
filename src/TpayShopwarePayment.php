<?php declare(strict_types=1);
/**
 * @copyright 2020 Tpay Krajowy Integrator Płatności S.A. <https://tpay.com/>
 *
 * @author    Jakub Medyński <jme@crehler.com>
 * @support   Tpay <pt@tpay.com>
 * @created   23 kwi 2020
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tpay\ShopwarePayment;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Tpay\ShopwarePayment\Util\Lifecycle\ActivateDeactivate;
use Tpay\ShopwarePayment\Util\Lifecycle\InstallUninstall;
use tpayLibs\src\_class_tpay\Utilities\Util;

// SWAG-133666
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

class TpayShopwarePayment extends Plugin
{
    public const ORDER_TRANSACTION_CUSTOM_FIELDS_TPAY_TRANSACTION_ID = 'tpay_shopware_payment_transaction_id';

    public const CUSTOMER_CUSTOM_FIELDS_TPAY_SELECTED_BANK = 'tpay_default_payment_selected_bank';

    /**
     * @var ActivateDeactivate
     */
    private $activateDeactivate;

    /**
     * @Required
     *
     * @param ActivateDeactivate $activateDeactivate
     */
    public function setActivateDeactivate(ActivateDeactivate $activateDeactivate): void
    {
        $this->activateDeactivate = $activateDeactivate;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/DependencyInjection/'));
        $loader->load('util.xml');
        $loader->load('payment.xml');
        $loader->load('config.xml');
        $loader->load('subscriber.xml');
        $loader->load('component.xml');
        $loader->load('webhook.xml');
        $loader->load('entity.xml');
    }

    public function install(InstallContext $installContext): void
    {
        (new InstallUninstall(
            $this->container->get(Connection::class),
            $this->container->get('payment_method.repository'),
            $this->container->get('rule.repository'),
            $this->container->get('currency.repository'),
            $this->container->get('language.repository'),
            $this->container->get(PluginIdProvider::class),
            \get_class($this)))->install($installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        (new InstallUninstall(
            $this->container->get(Connection::class),
            $this->container->get('payment_method.repository'),
            $this->container->get('rule.repository'),
            $this->container->get('currency.repository'),
            $this->container->get('language.repository'),
            $this->container->get(PluginIdProvider::class),
            \get_class($this)))->uninstall($uninstallContext);
    }

    public function activate(ActivateContext $activateContext): void
    {
        $this->activateDeactivate->activate($activateContext->getContext());
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        $this->activateDeactivate->deactivate($deactivateContext->getContext());
    }
}
