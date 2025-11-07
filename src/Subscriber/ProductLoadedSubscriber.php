<?php declare(strict_types=1);

namespace AfVariantSelection\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductLoadedSubscriber implements EventSubscriberInterface
{
    private SalesChannelRepository $productRepository;
    public function __construct(SalesChannelRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public static function getSubscribedEvents(): array
    {
        // Return the events to listen to as array like this:  <event to listen to> => <method to execute>
        return [
            ProductPageCriteriaEvent::class => ['onProductLoadedCriteria', 500],
            ProductPageLoadedEvent::class => 'onProductPageLoaded'
        ];
    }

    public function onProductLoadedCriteria(ProductPageCriteriaEvent $event)
    {
        //$productId = $event->getProductId();
        //dump($event);
        //$criteria = new Criteria();
        ////$criteria->addFilter(new EqualsFilter('product.id', ))
        //$criteria->addAssociation('options');
        //dump($event);
        //die();
        // Do something
        // E.g. work with the loaded entities: $event->getEntities()
    }
    public function onProductPageLoaded(ProductPageLoadedEvent $event)
    {
        $page = $event->getPage();
        $product = $page->getProduct();
        $productId = $product->getId();
        $parentId = "";
        if($product->getParentId()){
            $parentId = $product->getParentId();
        }
        $productId = $product->getId();
        $criteria = new Criteria();
        $criteria->addAssociation('children');
        $criteria->addFilter(new EqualsFilter('id', $parentId));

        $result = $this->productRepository->search($criteria, $event->getSalesChannelContext())->first();
        $children = $result->getChildren();
        $page->addExtension("af_variant_selection", new ArrayStruct([$children]));
        //dump($children);
    }
}
