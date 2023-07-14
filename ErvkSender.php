<?php

namespace Rpn\Services\Reestr\Smev;

use Carbon\Carbon;
use Rpn\Services\Reestr\Enums\ErvkErrorType;
use Rpn\Services\Reestr\Enums\ErvkStatus;
use Rpn\Services\Smev3\Enums\SmevItSystem;
use Rpn\Services\Smev3\Messages\SmevMessageBuilder;
use Rpn\Services\Smev3\Payload\OutgoingMessageHandler;
use Rpn\Services\Smev3\Services\SmevService;

class ErvkSender
{
    private $data;
    private \Ramsey\Uuid\UuidInterface $outgoingMessageId;

    /**
     * @param SmevService $smevService
     */
    public function __construct(
        private readonly SmevService $smevService
    ) {
    }

    /**
     * @return bool
     * @throws \Rpn\Services\Smev3\Exceptions\DomainException
     */
    public function send(): bool
    {
        if ($this->data) {
            $messageBuilder = SmevMessageBuilder::asRequest(SmevItSystem::RPRN01, 'reestr::smev.surveillance-register-data')
                ->withData(['data' => $this->data]);

            $this->outgoingMessageId = $this->smevService->send(
                smevMessage: $messageBuilder->build(),
                handlers: new OutgoingMessageHandler(
                    statusHandler: ErvkStastusHandler::class,
                    messageHandler: ErvkReplyHandler::class
                ),
            );
        }

        $this->setErvkStatusSended();

        return true;
    }

    /**
     *
     */
    private function setErvkStatusSended(): void
    {
        foreach ($this->data as $ervkUpdateInfo) {
            $fgnObject = \app($ervkUpdateInfo->service_request->type)
                ->where('id', $ervkUpdateInfo->service_request_id)->first();

            if ($ervkUpdateInfo->ervk_request) {
                $ervkUpdateInfo->update([
                    'sync_status' => ErvkStatus::SENDED,
                    'last_sync_date' => now()->toAtomString(),
                    'outgoing_message_id' => $this->outgoingMessageId->toString(),
                    'tmp_last_fields' => json_encode([
                        'last_object_control_type_id' => $fgnObject->object_information->object_control_type_id ?? null,
                        'last_object_control_species_id' =>
                            $fgnObject->object_information->object_control_species_id ?? null,
                        'last_object_control_subspecies_id' =>
                            $fgnObject->object_information->object_control_subspecies_id ?? null,
                        'last_registry_category_id' => $fgnObject->object_information->registry_category_id ?? null,
                        'last_sync_date' => $ervkUpdateInfo->last_sync_date,
                    ])
                ]);
            } else {
                $ervkUpdateInfo->update([
                    'sync_status' => ErvkStatus::ERROR,
                    'error_type' => ErvkErrorType::ERVK
                ]);
            }

            $ervkUpdateInfo->log(outgoing_message_id:$this->outgoingMessageId->toString());
        }
    }

    /**
     * @param $ervk
     * @return $this
     */
    public function build($ervk): static
    {
        $this->data = $ervk ?? [];

        return $this;
    }
}
