<?php declare(strict_types=1);

namespace Shopware\Framework\Api2\Resource;

use Shopware\Framework\Api2\ApiFlag\Required;
use Shopware\Framework\Api2\Field\FkField;
use Shopware\Framework\Api2\Field\IntField;
use Shopware\Framework\Api2\Field\ReferenceField;
use Shopware\Framework\Api2\Field\StringField;
use Shopware\Framework\Api2\Field\BoolField;
use Shopware\Framework\Api2\Field\DateField;
use Shopware\Framework\Api2\Field\SubresourceField;
use Shopware\Framework\Api2\Field\LongTextField;
use Shopware\Framework\Api2\Field\LongTextWithHtmlField;
use Shopware\Framework\Api2\Field\FloatField;
use Shopware\Framework\Api2\Field\TranslatedField;
use Shopware\Framework\Api2\Field\UuidField;
use Shopware\Framework\Api2\Resource\ApiResource;

class CoreTemplatesConfigElementsResource extends ApiResource
{
    public function __construct()
    {
        parent::__construct('s_core_templates_config_elements');
        
        $this->fields['templateId'] = (new IntField('template_id'))->setFlags(new Required());
        $this->fields['type'] = (new StringField('type'))->setFlags(new Required());
        $this->fields['name'] = (new StringField('name'))->setFlags(new Required());
        $this->fields['position'] = new IntField('position');
        $this->fields['defaultValue'] = new LongTextField('default_value');
        $this->fields['selection'] = new LongTextField('selection');
        $this->fields['fieldLabel'] = new StringField('field_label');
        $this->fields['supportText'] = new StringField('support_text');
        $this->fields['allowBlank'] = new IntField('allow_blank');
        $this->fields['containerId'] = (new IntField('container_id'))->setFlags(new Required());
        $this->fields['attributes'] = new LongTextField('attributes');
        $this->fields['lessCompatible'] = new IntField('less_compatible');
    }
    
    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Api2\Resource\CoreTemplatesConfigElementsResource::class
        ];
    }
}
