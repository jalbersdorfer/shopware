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

class PremiumDispatchCategoriesResource extends ApiResource
{
    public function __construct()
    {
        parent::__construct('s_premium_dispatch_categories');
        
        $this->primaryKeyFields['dispatchID'] = (new IntField('dispatchID'))->setFlags(new Required());
        $this->primaryKeyFields['categoryID'] = (new IntField('categoryID'))->setFlags(new Required());
    }
    
    public function getWriteOrder(): array
    {
        return [
            \Shopware\Framework\Api2\Resource\PremiumDispatchCategoriesResource::class
        ];
    }
}
