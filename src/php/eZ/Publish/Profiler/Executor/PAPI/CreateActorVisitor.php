<?php
namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\API\Repository\LanguageService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\ContentService;

use eZ\Publish\Profiler\ContentType;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;

class CreateActorVisitor
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
        $this->types = array();
    }

    /**
     * Get language
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
     * Return a contenttype group in case none exists yet one will be created
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private function getContentTypeGroup()
    {
        $identifier = 'profiler-content-type-group';
        try {
            return $this->contentTypeService->loadContentTypeGroupByIdentifier( $identifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
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
    private function getContentType( ContentType $type, $language )
    {
        $identifier = 'profiler-' . $type->name;
        try {
            return $this->contentTypeService->loadContentTypeByIdentifier( $identifier );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // Just continue creating the type
        }

        $contentTypeCreate = $this->contentTypeService->newContentTypeCreateStruct( $identifier );

        $contentTypeCreate->mainLanguageCode = $language->languageCode;
        $contentTypeCreate->names = array(
            $language->languageCode => $type->name
        );
        $contentTypeCreate->creationDate = new \DateTime();
        $contentTypeCreate->remoteId = sha1(microtime());
        $contentTypeCreate->isContainer = true;
        $contentTypeCreate->creatorId = 14;
        $contentTypeCreate->nameSchema = "<name>";
        $contentTypeCreate->urlAliasSchema = "<name>";

        $fieldPosition = 1;
        foreach ( $type->fields as $name => $field )
        {
            switch ( true )
            {
                case $field instanceof Field\TextLine:
                    $contentTypeCreate->addFieldDefinition(
                        $this->createFieldDefinition( $name, 'ezstring', $language, $fieldPosition )
                    );
                    break;
                case $field instanceof Field\XmlText:
                    $contentTypeCreate->addFieldDefinition(
                        $this->createFieldDefinition( $name, 'ezxmltext', $language, $fieldPosition )
                    );
                    break;

                case $field instanceof Field\Author:
                    $contentTypeCreate->addFieldDefinition(
                        $this->createFieldDefinition( $name, 'ezauthor', $language, $fieldPosition, false )
                    );
                    break;

                case $field instanceof Field\TextBlock:
                    $contentTypeCreate->addFieldDefinition(
                        $this->createFieldDefinition( $name, 'eztext', $language, $fieldPosition )
                    );
                    break;
                default:
                    throw new \RuntimeException(
                        "No field handler available for: " . get_class( $field )
                    );
            }
            $fieldPosition += 1;
        }

        try {
            $contentTypeDraft = $this->contentTypeService->createContentType(
                $contentTypeCreate,
                array(
                    $this->getContentTypeGroup()
                )
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
    private function createFieldDefinition($name, $ezType, $language, $position, $translatable = true)
    {
        $notSearchable = array('eztext');
        $fieldDefinitionCreate = $this->contentTypeService->newFieldDefinitionCreateStruct(
            $name,
            $ezType
        );

        $fieldDefinitionCreate->names = array(
            $language->languageCode => $name
        );
        $fieldDefinitionCreate->isTranslatable = true;
        $fieldDefinitionCreate->fieldGroup = "main";
        $fieldDefinitionCreate->position = $position;
        $fieldDefinitionCreate->isTranslatable = $translatable;
        $fieldDefinitionCreate->isRequired = false;
        $fieldDefinitionCreate->isInfoCollector = false;
        $fieldDefinitionCreate->isSearchable = !in_array($ezType, $notSearchable);

        return $fieldDefinitionCreate;
    }

    public function visit(Actor\Create $actor)
    {
        $language = $this->getLanguage('eng-US', 'English (US)');
        $type = $this->getContentType($actor->type, $language);

        $contentCreate = $this->contentService->newContentCreateStruct(
            $type,
            $language->languageCode
        );

        $contentCreate->sectionId = 1;
        $contentCreate->ownerId = 14;
        $contentCreate->remoteId = sha1(microtime());
        $contentCreate->alwaysAvailable = true;

        foreach( $actor->type->fields as $identifier => $field ) {
            /** @var Field $field */
            $data = $field->dataProvider->get();
            $contentCreate->setField( $identifier, $data );
        }

        $location = new \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct(
            array(
                "remoteId" => sha1(microtime()),
                "parentLocationId" => $actor->parentLocationId
            )
        );

        $contentDraft = $this->contentService->createContent( $contentCreate, array( $location ) );

        $content = $this->contentService->publishVersion(
            $contentDraft->versionInfo
        );

        // Remember created content objects
        $actor->storage->store( $content );

        if ( $actor->subActor !== null )
        {
            $actor->subActor->parentLocationId = $content->versionInfo->contentInfo->mainLocationId;
        }
    }
}
