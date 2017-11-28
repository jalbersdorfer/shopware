<?php declare(strict_types=1);

namespace Shopware\Product\Factory;

use Doctrine\DBAL\Connection;
use Shopware\Api\Read\ExtensionRegistryInterface;
use Shopware\Api\Search\QueryBuilder;
use Shopware\Api\Search\QuerySelection;
use Shopware\Category\Factory\CategoryBasicFactory;
use Shopware\Context\Struct\TranslationContext;
use Shopware\CustomerGroup\Factory\CustomerGroupBasicFactory;
use Shopware\PriceGroup\Factory\PriceGroupBasicFactory;
use Shopware\Product\Struct\ProductBasicStruct;
use Shopware\Product\Struct\ProductDetailStruct;
use Shopware\ProductListingPrice\Factory\ProductListingPriceBasicFactory;
use Shopware\ProductManufacturer\Factory\ProductManufacturerBasicFactory;
use Shopware\ProductMedia\Factory\ProductMediaBasicFactory;
use Shopware\ProductPrice\Factory\ProductPriceBasicFactory;
use Shopware\ProductVote\Factory\ProductVoteBasicFactory;
use Shopware\ProductVoteAverage\Factory\ProductVoteAverageBasicFactory;
use Shopware\SeoUrl\Factory\SeoUrlBasicFactory;
use Shopware\Tax\Factory\TaxBasicFactory;
use Shopware\Unit\Factory\UnitBasicFactory;

class ProductDetailFactory extends ProductBasicFactory
{
    /**
     * @var ProductMediaBasicFactory
     */
    protected $productMediaFactory;

    /**
     * @var CategoryBasicFactory
     */
    protected $categoryFactory;

    /**
     * @var ProductVoteBasicFactory
     */
    protected $productVoteFactory;

    /**
     * @var ProductVoteAverageBasicFactory
     */
    protected $productVoteAverageFactory;

    public function __construct(
        Connection $connection,
        ExtensionRegistryInterface $registry,
        ProductMediaBasicFactory $productMediaFactory,
        CategoryBasicFactory $categoryFactory,
        ProductVoteBasicFactory $productVoteFactory,
        ProductVoteAverageBasicFactory $productVoteAverageFactory,
        UnitBasicFactory $unitFactory,
        ProductPriceBasicFactory $productPriceFactory,
        ProductManufacturerBasicFactory $productManufacturerFactory,
        TaxBasicFactory $taxFactory,
        SeoUrlBasicFactory $seoUrlFactory,
        PriceGroupBasicFactory $priceGroupFactory,
        CustomerGroupBasicFactory $customerGroupFactory,
        ProductListingPriceBasicFactory $productListingPriceFactory
    ) {
        parent::__construct($connection, $registry, $unitFactory, $productPriceFactory, $productManufacturerFactory, $taxFactory, $seoUrlFactory, $priceGroupFactory, $customerGroupFactory, $productListingPriceFactory);
        $this->productMediaFactory = $productMediaFactory;
        $this->categoryFactory = $categoryFactory;
        $this->productVoteFactory = $productVoteFactory;
        $this->productVoteAverageFactory = $productVoteAverageFactory;
    }

    public function getFields(): array
    {
        $fields = array_merge(parent::getFields(), $this->getExtensionFields());
        $fields['_sub_select_category_uuids'] = '_sub_select_category_uuids';
        $fields['_sub_select_categoryTree_uuids'] = '_sub_select_categoryTree_uuids';

        return $fields;
    }

    public function hydrate(
        array $data,
        ProductBasicStruct $product,
        QuerySelection $selection,
        TranslationContext $context
    ): ProductBasicStruct {
        /** @var ProductDetailStruct $product */
        $product = parent::hydrate($data, $product, $selection, $context);
        if ($selection->hasField('_sub_select_category_uuids')) {
            $uuids = explode('|', (string) $data[$selection->getField('_sub_select_category_uuids')]);
            $product->setCategoryUuids(array_values(array_filter($uuids)));
        }

        if ($selection->hasField('_sub_select_categoryTree_uuids')) {
            $uuids = explode('|', (string) $data[$selection->getField('_sub_select_categoryTree_uuids')]);
            $product->setCategoryTreeUuids(array_values(array_filter($uuids)));
        }

        return $product;
    }

    public function joinDependencies(QuerySelection $selection, QueryBuilder $query, TranslationContext $context): void
    {
        parent::joinDependencies($selection, $query, $context);

        $this->joinMedia($selection, $query, $context);
        $this->joinCategories($selection, $query, $context);
        $this->joinCategoryTree($selection, $query, $context);
        $this->joinVotes($selection, $query, $context);
        $this->joinVoteAverages($selection, $query, $context);
    }

    public function getAllFields(): array
    {
        $fields = parent::getAllFields();
        $fields['media'] = $this->productMediaFactory->getAllFields();
        $fields['categories'] = $this->categoryFactory->getAllFields();
        $fields['categoryTree'] = $this->categoryFactory->getAllFields();
        $fields['votes'] = $this->productVoteFactory->getAllFields();
        $fields['voteAverages'] = $this->productVoteAverageFactory->getAllFields();

        return $fields;
    }

    protected function getExtensionFields(): array
    {
        $fields = parent::getExtensionFields();

        foreach ($this->getExtensions() as $extension) {
            $extensionFields = $extension->getDetailFields();
            foreach ($extensionFields as $key => $field) {
                $fields[$key] = $field;
            }
        }

        return $fields;
    }

    private function joinMedia(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($media = $selection->filter('media'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'product_media',
            $media->getRootEscaped(),
            sprintf('%s.uuid = %s.product_uuid', $selection->getRootEscaped(), $media->getRootEscaped())
        );

        $this->productMediaFactory->joinDependencies($media, $query, $context);

        $query->groupBy(sprintf('%s.uuid', $selection->getRootEscaped()));
    }

    private function joinCategories(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if ($selection->hasField('_sub_select_category_uuids')) {
            $query->addSelect('
                (
                    SELECT GROUP_CONCAT(mapping.category_uuid SEPARATOR \'|\')
                    FROM product_category mapping
                    WHERE mapping.product_uuid = ' . $selection->getRootEscaped() . '.uuid
                ) as ' . QuerySelection::escape($selection->getField('_sub_select_category_uuids'))
            );
        }

        if (!($categories = $selection->filter('categories'))) {
            return;
        }

        $mapping = QuerySelection::escape($categories->getRoot() . '.mapping');

        $query->leftJoin(
            $selection->getRootEscaped(),
            'product_category',
            $mapping,
            sprintf('%s.uuid = %s.product_uuid', $selection->getRootEscaped(), $mapping)
        );
        $query->leftJoin(
            $mapping,
            'category',
            $categories->getRootEscaped(),
            sprintf('%s.category_uuid = %s.uuid', $mapping, $categories->getRootEscaped())
        );

        $this->categoryFactory->joinDependencies($categories, $query, $context);

        $query->groupBy(sprintf('%s.uuid', $selection->getRootEscaped()));
    }

    private function joinCategoryTree(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if ($selection->hasField('_sub_select_categoryTree_uuids')) {
            $query->addSelect('
                (
                    SELECT GROUP_CONCAT(mapping.category_uuid SEPARATOR \'|\')
                    FROM product_category_ro mapping
                    WHERE mapping.product_uuid = ' . $selection->getRootEscaped() . '.uuid
                ) as ' . QuerySelection::escape($selection->getField('_sub_select_categoryTree_uuids'))
            );
        }

        if (!($categoryTree = $selection->filter('categoryTree'))) {
            return;
        }

        $mapping = QuerySelection::escape($categoryTree->getRoot() . '.mapping');

        $query->leftJoin(
            $selection->getRootEscaped(),
            'product_category_ro',
            $mapping,
            sprintf('%s.uuid = %s.product_uuid', $selection->getRootEscaped(), $mapping)
        );
        $query->leftJoin(
            $mapping,
            'category',
            $categoryTree->getRootEscaped(),
            sprintf('%s.category_uuid = %s.uuid', $mapping, $categoryTree->getRootEscaped())
        );

        $this->categoryFactory->joinDependencies($categoryTree, $query, $context);

        $query->groupBy(sprintf('%s.uuid', $selection->getRootEscaped()));
    }

    private function joinVotes(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($votes = $selection->filter('votes'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'product_vote',
            $votes->getRootEscaped(),
            sprintf('%s.uuid = %s.product_uuid', $selection->getRootEscaped(), $votes->getRootEscaped())
        );

        $this->productVoteFactory->joinDependencies($votes, $query, $context);

        $query->groupBy(sprintf('%s.uuid', $selection->getRootEscaped()));
    }

    private function joinVoteAverages(
        QuerySelection $selection,
        QueryBuilder $query,
        TranslationContext $context
    ): void {
        if (!($voteAverages = $selection->filter('voteAverages'))) {
            return;
        }
        $query->leftJoin(
            $selection->getRootEscaped(),
            'product_vote_average_ro',
            $voteAverages->getRootEscaped(),
            sprintf('%s.uuid = %s.product_uuid', $selection->getRootEscaped(), $voteAverages->getRootEscaped())
        );

        $this->productVoteAverageFactory->joinDependencies($voteAverages, $query, $context);

        $query->groupBy(sprintf('%s.uuid', $selection->getRootEscaped()));
    }
}