<?php

declare(strict_types=1);

namespace App\Modules\Databank\Import\Services;

use App\Modules\Core\Common\Models\Attachment;
use App\Modules\Databank\Common\Enums\AttachmentGroup;
use App\Modules\Databank\Common\Enums\Status;
use App\Modules\Databank\Import\DTOs\Appearance;
use App\Modules\Databank\Import\Exceptions\EmptyImageUrlException;
use App\Modules\Databank\Import\Exceptions\GetImageContentException;
use App\Modules\Databank\Import\Exceptions\NoPhysicalImageException;
use App\Modules\Databank\Import\Exceptions\TempImageRealPathException;
use App\Modules\Databank\Import\Exceptions\TempImageWriteException;
use App\Modules\Databank\Import\Exceptions\UnknownImageMimeTypeException;
use App\Modules\Faction\Common\Models\Faction;
use App\Modules\Faction\Common\Repositories\FactionRepository;
use App\Modules\Handbook\Common\Repositories\HandbookRepository;
use App\Modules\Handbook\Common\Repositories\HandbookValueRepository;
use App\Modules\Manufacturer\Common\Models\Manufacturer;
use App\Modules\Manufacturer\Common\Repositories\ManufacturerRepository;
use App\Modules\Media\Common\Enums\MediaType;
use App\Modules\Media\Common\Models\Media;
use App\Modules\Media\Common\Repositories\MediaRepository;
use Carbon\Exceptions\InvalidFormatException;
use finfo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Flysystem\FilesystemException;
use Orchid\Attachment\Engines\Generator as AttachmentGenerator;
use Orchid\Attachment\File;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

abstract class EntityService
{
    /** @var array<string, Faction> */
    private array $factionsCache = [];

    /** @var array<string, Media> */
    private array $mediaCache = [];

    /** @var array<string, Manufacturer> */
    private array $manufacturersCache = [];

    public function __construct(
        protected LoggerInterface $logger,
        protected FactionRepository $factionRepository,
        protected ManufacturerRepository $manufacturerRepository,
        protected MediaRepository $mediaRepository,
        protected HandbookRepository $handbookRepository,
        protected HandbookValueRepository $handbookValueRepository
    ) {
    }

    protected function resolveMediaType(string $type): ?MediaType
    {
        return match (mb_strtolower($type)) {
            'movie' => MediaType::MOVIE,
            'series' => MediaType::SERIES,
            'game' => MediaType::GAME,
            default => null,
        };
    }

    protected function getOrCreateMedia(Appearance $dto): Media
    {
        if (isset($this->mediaCache[$dto->getName()])) {
            return $this->mediaCache[$dto->getName()];
        }

        $media = $this->mediaRepository->findOneByName($dto->getName(), true);
        if ($media) {
            $this->mediaCache[$dto->getName()] = $media;
            return $media;
        }

        $media = $this->mediaRepository->newModel();

        $media->name = $dto->getName();
        $media->status = Status::PUBLISHED;
        $media->type = $dto->getTypeName()
            ? $this->resolveMediaType($dto->getTypeName())
            : null;

        try {
            $media->release_date = $dto->getReleaseDate()
                ? Carbon::createFromFormat('F j, Y', $dto->getReleaseDate())
                : null;
        } catch (InvalidFormatException $e) {
            $this->logger->error('Unable to create Carbon instance for date "' . $dto->getReleaseDate() . '":'
                . $e->getMessage());
        }

        if ($dto->getImageUrl()) {
            try {
                $media->poster_id = $this->getOrCreateAttachment(
                    $dto->getName(),
                    $dto->getImageUrl(),
                    AttachmentGroup::MEDIA_POSTER
                )->id;
            } catch (Throwable $e) {
                $this->logger->error('Unable to save image: ' . $e->getMessage()
                    . ' (see: ' . $dto->getImageUrl() . ')');
            }
        }

        if (!$this->mediaRepository->save($media)) {
            throw new RuntimeException('Unable to save Media model');
        }

        $this->logger->info('Added new Media: #' . $media->id . ' "' . $media->name . '"');

        $this->mediaCache[$dto->getName()] = $media;

        return $media;
    }

    protected function getOrCreateManufacturer(string $name): Manufacturer
    {
        if (isset($this->manufacturersCache[$name])) {
            return $this->manufacturersCache[$name];
        }

        $manufacturer = $this->manufacturerRepository->findOneByName($name, true);
        if ($manufacturer) {
            $this->manufacturersCache[$name] = $manufacturer;
            return $manufacturer;
        }

        $manufacturer = $this->manufacturerRepository->newModel();

        $manufacturer->name = $name;
        $manufacturer->status = Status::PUBLISHED;

        if (!$this->manufacturerRepository->save($manufacturer)) {
            throw new RuntimeException('Unable to save Manufacturer model');
        }

        $this->logger->info('Added new Manufacturer: #' . $manufacturer->id . ' "' . $manufacturer->name . '"');

        $this->manufacturersCache[$name] = $manufacturer;

        return $manufacturer;
    }

    protected function getOrCreateFaction(string $name): Faction
    {
        if (isset($this->factionsCache[$name])) {
            return $this->factionsCache[$name];
        }

        $faction = $this->factionRepository->findOneByName($name, true);
        if ($faction) {
            $this->factionsCache[$name] = $faction;
            return $faction;
        }

        $faction = $this->factionRepository->newModel();

        $faction->name = $name;
        $faction->status = Status::PUBLISHED;

        if (!$this->factionRepository->save($faction)) {
            throw new RuntimeException('Unable to save Faction model');
        }

        $this->logger->info('Added new Faction: #' . $faction->id . ' "' . $faction->name . '"');

        $this->factionsCache[$name] = $faction;

        return $faction;
    }

    /**
     * @throws EmptyImageUrlException
     * @throws FilesystemException
     * @throws NoPhysicalImageException
     * @throws TempImageRealPathException
     * @throws TempImageWriteException
     * @throws UnknownImageMimeTypeException
     * @throws GetImageContentException
     */
    protected function getOrCreateAttachment(string $name, string $url, AttachmentGroup $group): Attachment
    {
        if ($url === '') {
            throw new EmptyImageUrlException('Empty url');
        }

        $content = file_get_contents($url);
        if ($content === false) {
            throw new GetImageContentException('Invalid content');
        }

        $tempImage = tmpfile();
        if (!fwrite($tempImage, $content)) {
            fclose($tempImage);
            throw new TempImageWriteException('Unable to write image content to temporary file');
        }

        $mimeType = new finfo(FILEINFO_MIME_TYPE)->buffer($content);
        unset($content);

        $tempImagePath = stream_get_meta_data($tempImage)['uri'];
        if (!$tempImagePath) {
            fclose($tempImage);
            throw new TempImageRealPathException('Unable to get temporary image path');
        }

        $extension = match ($mimeType) {
            'image/png' => '.png',
            'image/jpeg' => '.jpg',
            'image/webp' => '.webp',
            default => null,
        };

        if (!$extension) {
            fclose($tempImage);
            throw new UnknownImageMimeTypeException('Unknown image mime type "' . $mimeType . '"');
        }

        $slug = Str::slug(title: $name, dictionary: ['/' => '-']);
        $uploadedFile = new UploadedFile($tempImagePath, $slug . $extension, $mimeType);

        $disk = config('platform.attachment.disk', 'public');
        if ($attachment = $this->findAttachmentByUploadedFile($uploadedFile, $disk)) {
            unlink($tempImagePath);
            return $attachment;
        }

        $file = new File($uploadedFile, $disk, $group->value);

        /** @var Attachment $attachment */
        $attachment = $file->load();

        unlink($tempImagePath);
        unset($file);

        if (!is_file(Storage::disk($attachment->disk)->path($attachment->physicalPath()))) {
            $attachment->delete();

            throw new NoPhysicalImageException('No physical image file at path'
                . ' "' . public_path($attachment->physicalPath()) . '"');
        }

        return $attachment;
    }

    /**
     * Try to find existing DB entry for image by its content hash.
     * This helps to prevent duplicate entries on subsequent runs.
     *
     * @param UploadedFile $file
     * @param string $disk
     *
     * @return Attachment|null
     *
     * @see File::__construct
     */
    protected function findAttachmentByUploadedFile(UploadedFile $file, string $disk): ?Attachment
    {
        $generator = config('platform.attachment.generator', AttachmentGenerator::class);
        $engine = new $generator($file);

        return Attachment::where('hash', $engine->hash())
            ->where('disk', $disk)
            ->first();
    }
}
