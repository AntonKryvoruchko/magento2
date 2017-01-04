<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Category\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\UrlRewrite\Model\StorageInterface;
use Magento\CatalogUrlRewrite\Model\Category\Plugin\Storage as CategoryStoragePlugin;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\CatalogUrlRewrite\Model\Category\Product;
use Magento\CatalogUrlRewrite\Model\ResourceModel\Category\Product as ProductResourceModel;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CategoryStoragePlugin
     */
    private $plugin;

    /**
     * @var UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlFinder;

    /**
     * @var StorageInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $product;

    /**
     * @var ProductResourceModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productResourceModel;

    /**
     * @var UrlRewrite|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlRewrite;

    protected function setUp()
    {
        $this->storage = $this->getMockBuilder(StorageInterface::class)
            ->getMockForAbstractClass();
        $this->urlFinder = $this->getMockBuilder(UrlFinderInterface::class)
            ->getMockForAbstractClass();
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productResourceModel = $this->getMockBuilder(ProductResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlRewrite = $this->getMockBuilder(UrlRewrite::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMetadata', 'getEntityType', 'getIsAutogenerated', 'getUrlRewriteId', 'getEntityId'])
            ->getMock();

        $this->plugin = (new ObjectManager($this))->getObject(
            CategoryStoragePlugin::class,
            [
                'urlFinder' => $this->urlFinder,
                'productResource' => $this->productResourceModel
            ]
        );
    }

    /**
     * test AfterReplace method
     */
    public function testAfterReplace()
    {
        $this->urlRewrite->expects(static::any())->method('getMetadata')->willReturn(['category_id' => '5']);
        $this->urlRewrite->expects(static::once())->method('getEntityTYpe')->willReturn('product');
        $this->urlRewrite->expects(static::once())->method('getIsAutogenerated')->willReturn(1);
        $this->urlRewrite->expects(static::once())->method('getUrlRewriteId')->willReturn('4');
        $this->urlRewrite->expects(static::once())->method('getEntityId')->willReturn('2');
        $this->urlRewrite->setData('request_path', 'test');
        $this->urlRewrite->setData('store_id', '1');
        $productUrls = ['targetPath' => $this->urlRewrite];

        $this->urlFinder->expects(static::once())->method('findAllByData')->willReturn([$this->urlRewrite]);

        $this->productResourceModel->expects(static::once())->method('saveMultiple')->willReturnSelf();

        $this->plugin->afterReplace($this->storage, null, $productUrls);
    }

    /**
     * test BeforeDeleteByData method
     */
    public function testBeforeDeleteByData()
    {
        $data = [1, 2, 3];
        $this->productResourceModel->expects(static::once())
            ->method('removeMultipleByProductCategory')
            ->with($data)->willReturnSelf();
        $this->plugin->beforeDeleteByData($this->storage, $data);
    }
}
