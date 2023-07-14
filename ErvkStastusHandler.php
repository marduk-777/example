<?php

declare(strict_types=1);

namespace Rpn\Services\Reestr\Smev;

use App\Enums\SmevRequestStatus;
use App\Models\RpnServiceSmevRequest;
use Ramsey\Uuid\UuidInterface;
use Rpn\Services\Reestr\Models\ErvkUpdateInfo;
use Rpn\Services\Smev3\Exceptions\DomainException;
use Rpn\Services\Smev3\Handlers\AdapterStatusHandler;

/**
 * Обработчик смены статуса произвольного исходящего сообщения
 */
class ErvkStastusHandler implements AdapterStatusHandler
{
    /**
     * @inerhitDoc
     * @throws DomainException
     */
    public function whenFault(UuidInterface $smevId, array $params): void
    {
        try {
            $smevRequest = $this->getSmevRequest($smevId);

            $smevRequest->update([
                'status' => SmevRequestStatus::ERROR,
                'error'  => 'Ошибка при отправке в СМЭВ',
            ]);

            if ($params['faultStatus'] ?? null) {
                $smevRequest->update(['status' => $params['faultStatus']]);
            }
        } catch (\Throwable $e) {
            throw DomainException::cantSendStatusToSmev($e);
        }
    }

    /**
     * @inerhitDoc
     * @throws DomainException
     */
    public function whenSuccess(UuidInterface $smevId, array $params): void
    {
        try {
            $smevRequest = $this->getSmevRequest($smevId);
            $smevRequest->update(['status' => SmevRequestStatus::DELIVERED]);

            if ($params['successStatus'] ?? null) {
                $smevRequest->update(['status' => $params['successStatus']]);
            }
        } catch (\Throwable $e) {
            throw DomainException::cantSendStatusToSmev($e);
        }
    }

    /**
     * @param UuidInterface $smevId
     * @return RpnServiceSmevRequest
     */
    private function getSmevRequest(UuidInterface $smevId): RpnServiceSmevRequest
    {
        return ErvkUpdateInfo::where('ervk_id', $smevId)->firstOrFail();
    }
}
