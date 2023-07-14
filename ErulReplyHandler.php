<?php

declare(strict_types=1);

namespace Rpn\Services\Reestr\Smev;

use App\Enums\SmevRequestStatus;
use App\Models\RpnServiceSmevRequest;
use Rpn\Services\LicenseActivityWaste\Models\LicenseRequestIssue;
use Rpn\Services\LicenseActivityWaste\Models\Issue\Process\Result\NumberAssignment;
use Rpn\Services\Smev3\Log\Logger;
use Rpn\Services\Smev3\Handlers\ReplyingMessageHandler;
use Rpn\Services\Smev3\Messages\Common\Incoming\Error;
use Rpn\Services\Smev3\Messages\Common\Incoming\Reject;
use Rpn\Services\Smev3\Messages\Common\Incoming\Status;
use Rpn\Services\Smev3\Messages\Common\Incoming\Metadata;
use Rpn\Services\Smev3\Messages\Erul\LicenseActivityWaste\LicenseNumberResponse;

/**
 * Обработчик ответа на запрос в ЕРУЛ
 */
class ErulReplyHandler implements ReplyingMessageHandler
{
    public function __construct(
        private readonly Logger $logger,
    ) {
    }

    /**
     * Ответ от ЕРУЛ
     *
     * @param Metadata $metadata
     * @param RpnServiceSmevRequest $smevRequest
     * @param LicenseNumberResponse $numberResponse
     * @return void
     * @throws \App\Exceptions\AfterSetStatusException
     */
    public function handle(Metadata $metadata, RpnServiceSmevRequest $smevRequest, LicenseNumberResponse $numberResponse): void
    {
        $smevRequest->update([
            'status' => SmevRequestStatus::DELIVERED,
        ]);

        if ($numberResponse->hasError()) {
            $error = $numberResponse->error;

            $smevRequest->update([
                'status' => SmevRequestStatus::ERROR,
                'error'  => \implode(': ', [$error->type, $error->description]),
            ]);

            $smevRequest->service_request->setStatus(statusCode: $metadata->params['faultStatus']);

            return;
        }

        /** @var LicenseRequestIssue $licenseRequest */
        $licenseRequest = $smevRequest->service_request;

        /** @var NumberAssignment|null $form */
        $form = $licenseRequest->process?->result?->number_assignment;

        if ($form) {
            $form->number = $numberResponse->license->number;
            $form->issued_at = $numberResponse->license->issueDate;

            $form->save();
        }

        $smevRequest->service_request->setStatus(statusCode: $metadata->params['successStatus']);
    }

    /**
     * Запрос отклонён ИС контрагента
     *
     * @param Metadata $metadata
     * @param RpnServiceSmevRequest $smevRequest
     * @param Reject $reject
     * @return void
     * @throws \App\Exceptions\AfterSetStatusException
     */
    public function reject(Metadata $metadata, RpnServiceSmevRequest $smevRequest, Reject $reject): void
    {
        $smevRequest->update([
            'status' => SmevRequestStatus::ERROR,
            'error'  => \implode(': ', [$reject->code, $reject->description]),
        ]);

        $smevRequest->service_request->setStatus(statusCode: $metadata->params['faultStatus']);
    }

    /**
     * Произошла ошибка в СМЭВ при работе с исходным запросом
     *
     * @param Metadata $metadata
     * @param RpnServiceSmevRequest $smevRequest
     * @param Error $error
     * @return void
     * @throws \App\Exceptions\AfterSetStatusException
     */
    public function error(Metadata $metadata, RpnServiceSmevRequest $smevRequest, Error $error): void
    {
        $smevRequest->update([
            'status' => SmevRequestStatus::ERROR,
            'error'  => \implode(': ', [$error->fault->code, $error->fault->description]),
        ]);

        $smevRequest->service_request->setStatus(statusCode: $metadata->params['faultStatus']);
    }

    /**
     * Изменился бизнес-статус в ИС контрагента
     *
     * @param  Metadata  $metadata
     * @param  Status    $status
     * @return void
     */
    public function status(Metadata $metadata, Status $status): void
    {
        // просто логируем
        $this->logger->info("В ЕРУЛ изменился статус: {$status->description} ({$status->code})", $metadata->clientId);
    }
}
