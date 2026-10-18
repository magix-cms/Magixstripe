<?php
declare(strict_types=1);

namespace Plugins\Magixstripe\db;

use App\Frontend\Db\BaseDb;
use Magepattern\Component\Database\QueryBuilder;

class StripeFrontDb extends BaseDb
{
    public function getConfig(): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_stripe_config')->limit(1);
        return $this->executeRow($qb);
    }

    public function insertHistory(array $data): bool
    {
        $qb = new QueryBuilder();
        $qb->insert('mc_stripe_history', $data);
        return $this->executeInsert($qb);
    }

    public function updateStatusBySession(string $stripeSessionId, string $status): bool
    {
        $qb = new QueryBuilder();
        $qb->update('mc_stripe_history', ['status' => $status])
            ->where('stripe_session_id = :sess', ['sess' => $stripeSessionId]);
        return $this->executeUpdate($qb);
    }

    public function getHistoryBySession(string $stripeSessionId): array|false
    {
        $qb = new QueryBuilder();
        $qb->select('*')->from('mc_stripe_history')
            ->where('stripe_session_id = :sess', ['sess' => $stripeSessionId]);
        return $this->executeRow($qb);
    }
}