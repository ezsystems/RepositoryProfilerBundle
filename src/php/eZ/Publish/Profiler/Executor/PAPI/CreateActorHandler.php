<?php

namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Profiler\ContentType;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;
use eZ\Publish\Profiler\Actor\Handler;
use eZ\Publish\Profiler\GaussDistributor;

class CreateActorHandler extends Handler
{
    /**
     * @var \eZ\Publish\Core\Repository\LanguageService
     */
    private $languageService;

    /**
     * @var \eZ\Publish\Core\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\Core\Repository\ContentService
     */
    private $contentService;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    private $contentTypeGroup;

    /**
     * @param \eZ\Publish\Core\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\Repository\ContentService $contentService
     */
    public function __construct(LanguageService $languageService, ContentTypeService $contentTypeService, ContentService $contentService)
    {
        $this->languageService = $languageService;
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;

        $this->contentTypeGroup = null;
        $this->types = [];
    }

    /**
     * Can handle.
     *
     * @param Actor $actor
     * @return bool
     */
    public function canHandle(Actor $actor)
    {
        return $actor instanceof Actor\Create;
    }

    /**
     * Handle.
     *
     * @param Actor $actor
     */
    public function handle(Actor $actor)
    {
        $languages = [];
        foreach ($actor->type->languageCodes as $languageCode) {
            $languages[$languageCode] = $this->getLanguage($languageCode, "Test ($languageCode)");
        }

        $type = $this->getContentType($actor->type, $languages);

        $mainLanguage = reset($languages);
        $contentCreate = $this->contentService->newContentCreateStruct(
            $type,
            $mainLanguage->languageCode
        );

        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = sha1(microtime());
        $contentCreate->alwaysAvailable = true;

        $contentCreate = $this->createFields($contentCreate, $actor->type->fields, $languages);

        $location = new \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct(
            [
                'remoteId' => sha1(microtime()),
                'parentLocationId' => $actor->parentLocationId,
            ]
        );

        $contentDraft = $this->contentService->createContent($contentCreate, [$location]);
        $content = $this->contentService->publishVersion($contentDraft->versionInfo);
        $content = $this->ageContent($actor->type, $content);

        // Remember created content objects
        $actor->storage->store($content);

        if ($actor->subActor !== null) {
            $actor->subActor->parentLocationId = $content->versionInfo->contentInfo->mainLocationId;
        }
    }

    /**
     * Age content.
     *
     * @param ContentType $type
     * @param Content $content
     * @return Content
     */
    protected function ageContent(ContentType $type, $content)
    {
        $versionCount = GaussDistributor::getNumber($type->versionCount) - 1;
        if (!$versionCount) {
            return $content;
        }

        $draft = null;
        for ($i = 0; $i < $versionCount; ++$i) {
            $draft = $this->contentService->createContentDraft($content->versionInfo->contentInfo, $content->versionInfo);
        }

        if ($draft) {
            $content = $this->contentService->publishVersion($draft->versionInfo);
        }

        return $content;
    }

    /**
     * createFields.
     *
     * @param ContentCreate $contentCreate
     * @param Field[] $fields
     * @return ContentCreate
     */
    protected function createFields($contentCreate, array $fields, array $languages)
    {
        $mainLanguage = reset($languages);
        foreach ($fields as $identifier => $field) {
            $contentCreate->setField(
                $identifier,
                $field->dataProvider->get($mainLanguage->languageCode)
            );

            if ($field->translatable) {
                foreach ($languages as $language) {
                    if ($language == $mainLanguage) {
                        continue;
                    }

                    $contentCreate->setField(
                        $identifier,
                        $field->dataProvider->get($language->languageCode),
                        $language->languageCode
                    );
                }
            }
        }

        return $contentCreate;
    }

    /**
     * Get language.
     *
     * @param string $languageCode
     * @return Language
     */
    private function getLanguage($languageCode, $name)
    {
        try {
            return $this->languageService->loadLanguage($languageCode);
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            // Just create language…
        }

        $createStruct = $this->languageService->newLanguageCreateStruct();
        $createStruct->languageCode = $languageCode;
        $createStruct->name = $name;

        return $this->languageService->createLanguage($createStruct);
    }

    /**
     * Return a contenttype group in case none exists yet one will be created.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private function getContentTypeGroup()
    {
        $identifier = 'profiler-content-type-group';
        try {
            return $this->contentTypeService->loadContentTypeGroupByIdentifier($identifier);
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            // Just continue creating the type
        }

        $groupCreateStruct = $this->contentTypeService->newContentTypeGroupCreateStruct(
            'profiler-content-type-group'
        );

        $groupCreateStruct->creatorId = 14;
        $groupCreateStruct->creationDate = new \DateTime();

        return $this->contentTypeService->createContentTypeGroup(
            $groupCreateStruct
        );
    }

    /**
     * @param \eZ\Publish\Profiler\ContentType $type
     * @throws \RuntimeException
     * @return \eZ\Publish\Core\Repository\Values\ContentType\ContentType
     */
    private function getContentType(ContentType $type, $languages)
    {
        $identifier = 'profiler-' . $type->name;
        try {
            return $this->contentTypeService->loadContentTypeByIdentifier($identifier);
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            // Just continue creating the type
        }

        $contentTypeCreate = $this->contentTypeService->newContentTypeCreateStruct($identifier);

        $mainLanguage = reset($languages);
        $contentTypeCreate->mainLanguageCode = $mainLanguage->languageCode;
        $contentTypeCreate->names = array_fill_keys(array_keys($languages), ucfirst($type->name));
        $contentTypeCreate->creationDate = new \DateTime();
        $contentTypeCreate->remoteId = sha1(microtime());
        $contentTypeCreate->isContainer = true;
        $contentTypeCreate->creatorId = 14;
        $contentTypeCreate->nameSchema = '<name>';
        $contentTypeCreate->urlAliasSchema = '<name>';

        $fieldPosition = 1;
        foreach ($type->fields as $name => $field) {
            $contentTypeCreate->addFieldDefinition(
                $this->createFieldDefinition($name, $field, $languages, $fieldPosition)
            );
            $fieldPosition += 1;
        }

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $contentTypeCreate,
                [
                    $this->getContentTypeGroup(),
                ]
            );
        } catch (\eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException $e) {
            var_dump($e->getFieldErrors());
            throw $e;
        }

        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        return $this->contentTypeService->loadContentType($contentTypeDraft->id);
    }

    /**
     * @param string $name
     * @param string $ezType
     * @param int $position
     * @param bool $translatable
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    private function createFieldDefinition($name, Field $field, array $languages, $position)
    {
        $fieldDefinitionCreate = $this->contentTypeService->newFieldDefinitionCreateStruct(
            $name,
            $field->getTypeIdentifier()
        );

        $fieldDefinitionCreate->names = array_fill_keys(array_keys($languages), ucfirst($name));
        $fieldDefinitionCreate->fieldGroup = 'main';
        $fieldDefinitionCreate->position = $position;
        $fieldDefinitionCreate->isTranslatable = $field->translatable;
        $fieldDefinitionCreate->isRequired = false;
        $fieldDefinitionCreate->isInfoCollector = false;
        $fieldDefinitionCreate->isSearchable = $field->searchable;

        return $fieldDefinitionCreate;
    }
}
