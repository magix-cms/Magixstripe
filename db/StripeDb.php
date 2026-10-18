<?php
declare(strict_types=1);

namespace Plugins\Magixstripe\db;

use App\Backend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class StripeDb extends BaseDb
{
    public function getConfig(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_stripe_config')->limit(1);
        return $this->executeRow($qb);
    }

    public function saveConfig(array $data): bool
    {
        $existing = $this->getConfig();
        $qb = new QueryBuilder();

        if ($existing && !empty($existing['id_stripe'])) {
            $qb->update('mc_stripe_config', $data)
                ->where('id_stripe = :id', ['id' => (int)$existing['id_stripe']]);
            return $this->executeUpdate($qb);
        } else {
            $qb->insert('mc_stripe_config', $data);
            return $this->executeInsert($qb);
        }
    }
}