<?php declare(strict_types=1);

namespace Tpay\ShopwarePayment\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1614241149PaymentToken extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1614241149;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            CREATE TABLE IF NOT EXISTS `tpay_payment_tokens` (
            `id` BINARY(16) NOT NULL,
            `token` VARCHAR(1024) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
