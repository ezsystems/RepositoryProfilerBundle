<?php
namespace eZ\Publish\Profiler\Executor\PAPI;

use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\ContentService;

use eZ\Publish\Profiler\ContentType;
use eZ\Publish\Profiler\Field;
use eZ\Publish\Profiler\Actor;

class CreateActorVisitor
{
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
     * @var array
     */
    private $types;

    /**
     * @param \eZ\Publish\Core\Repository\ContentTypeService $contentTypeService
     * @param \eZ\Publish\Core\Repository\ContentService $contentService
     */
    public function __construct(ContentTypeService $contentTypeService, ContentService $contentService)
    {
        $this->contentTypeService = $contentTypeService;
        $this->contentService = $contentService;

        $this->contentTypeGroup = null;
        $this->types = array();
    }

    /**
     * Return a contenttype group in case none exists yet one will be created
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private function getContentTypeGroup()
    {
        if ( $this->contentTypeGroup === null )
        {
            $groupCreateStruct = $this->contentTypeService->newContentTypeGroupCreateStruct(
                'profiler-content-type-group'
            );

            $groupCreateStruct->creatorId = 14;
            $groupCreateStruct->creationDate = new \DateTime();

            $this->contentTypeGroup = $this->contentTypeService->createContentTypeGroup(
                $groupCreateStruct
            );
        }

        return $this->contentTypeGroup;
    }

    /**
     * @param \eZ\Publish\Profiler\ContentType $type
     * @throws \RuntimeException
     * @return \eZ\Publish\Core\Repository\Values\ContentType\ContentType
     */
    private function getType( ContentType $type )
    {
        if ( !isset( $this->types[$type->name] ) ) {
            $contentTypeCreate = $this->contentTypeService->newContentTypeCreateStruct(
                'profiler-type-' . $type->name
            );

            $contentTypeCreate->mainLanguageCode = 'eng-US';
            $contentTypeCreate->names = array(
                'eng-US' => $type->name
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
                            $this->createFieldDefinition( $name, 'ezstring', $fieldPosition )
                        );
                        break;
                    case $field instanceof Field\XmlText:
                        $contentTypeCreate->addFieldDefinition(
                            $this->createFieldDefinition( $name, 'ezxmltext', $fieldPosition )
                        );
                        break;

                    case $field instanceof Field\Author:
                        $contentTypeCreate->addFieldDefinition(
                            $this->createFieldDefinition( $name, 'ezauthor', $fieldPosition, false )
                        );
                        break;

                    case $field instanceof Field\TextBlock:
                        $contentTypeCreate->addFieldDefinition(
                            $this->createFieldDefinition( $name, 'eztext', $fieldPosition )
                        );
                        break;
                    default:
                        throw new \RuntimeException(
                            "No field handler available for: " . get_class( $field )
                        );
                }
                $fieldPosition += 1;
            }

            $contentTypeDraft = $this->contentTypeService->createContentType(
                $contentTypeCreate,
                array(
                    $this->getContentTypeGroup()
                )
            );

            $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
            $this->types[$type->name] = $this->contentTypeService->loadContentType($contentTypeDraft->id);
        }

        return $this->types[$type->name];
    }

    /**
     * @param string $name
     * @param string $ezType
     * @param int $position
     * @param bool $translatable
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    private function createFieldDefinition($name, $ezType, $position, $translatable = true)
    {
        $fieldDefinitionCreate = $this->contentTypeService->newFieldDefinitionCreateStruct(
            $name,
            $ezType
        );

        $fieldDefinitionCreate->names = array(
            'eng-US' => $name
        );
        $fieldDefinitionCreate->isTranslatable = true;
        $fieldDefinitionCreate->fieldGroup = "main";
        $fieldDefinitionCreate->position = $position;
        $fieldDefinitionCreate->isTranslatable = $translatable;
        $fieldDefinitionCreate->isRequired = false;
        $fieldDefinitionCreate->isInfoCollector = false;
        $fieldDefinitionCreate->isSearchable = true;

        return $fieldDefinitionCreate;
    }

    public function visit(Actor\Create $actor)
    {
        $type = $this->getType($actor->type);

        $contentCreate = $this->contentService->newContentCreateStruct(
            $type,
            'eng-US'
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
