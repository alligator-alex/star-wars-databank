<?php

declare(strict_types=1);

namespace App\Modules\Databank\Console\Commands;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Core\Common\Repositories\AttachmentRepository;
use App\Modules\Droid\Common\Models\Droid;
use App\Modules\Droid\Common\Repositories\DroidRepository;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use App\Modules\Vehicle\Common\Models\Vehicle;
use App\Modules\Vehicle\Common\Repositories\VehicleRepository;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Number;

class CleanupAttachments extends Command
{
    protected $signature = 'databank:cleanup-attachments';

    public function __construct(
        private readonly AttachmentRepository $attachmentRepository,
        private readonly MediaRepository $mediaRepository,
        private readonly VehicleRepository $vehicleRepository,
        private readonly DroidRepository $droidRepository,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $deletedCount = 0;
        $deletedSize = 0;

        $this->attachmentRepository->queryBuilder()->lazyById()->each(
            function (Attachment $attachment) use (&$deletedCount, &$deletedSize): void {
                $this->line('Processing Attachment #' . $attachment->id . ' (' . $attachment->relative_url . ')');

                /** @var Media|null $media */
                /** @phpstan-ignore-next-line */
                $media = $this->mediaRepository->queryBuilder()
                    ->withDrafts()
                    ->where('poster_id', '=', $attachment->id)
                    ->select(['id', 'name'])
                    ->first();

                if ($media) {
                    $this->info('- Linked with Media #' . $media->id . ' "' . $media->name . '", skipping');
                    return;
                }

                /** @var Vehicle|null $vehicle */
                /** @phpstan-ignore-next-line */
                $vehicle = $this->vehicleRepository->queryBuilder()
                    ->withDrafts()
                    ->where('image_id', '=', $attachment->id)
                    ->select(['id', 'name'])
                    ->first();

                if ($vehicle) {
                    $this->info('- Linked with Vehicle #' . $vehicle->id . ' "' . $vehicle->name . '", skipping');
                    return;
                }

                /** @var Droid|null $droid */
                /** @phpstan-ignore-next-line */
                $droid = $this->droidRepository->queryBuilder()
                    ->withDrafts()
                    ->where('image_id', '=', $attachment->id)
                    ->select(['id', 'name'])
                    ->first();

                if ($droid) {
                    $this->info('- Linked with Droid #' . $droid->id . ' "' . $droid->name . '", skipping');
                    return;
                }

                $this->warn('- Not linked with anything, deleting');

                try {
                    $attachment->delete();

                    $deletedCount++;
                    $deletedSize += $attachment->size;
                } catch (Exception $e) {
                    $this->error('- Unable to delete: ' . $e->getMessage());
                }
            }
        );

        $this->comment('Deleted ' . $deletedCount . ' attachments');
        $this->comment(Number::fileSize($deletedSize) . ' of space freed up');
    }
}
