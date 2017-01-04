<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\CatalogUrlRewrite\Model\Map\UrlRewriteMap;
use Magento\CatalogUrlRewrite\Model\Map\DataMapPoolInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\Map\DataProductUrlRewriteMap;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryUrlRewriteMap;

/**
 * Class DataProductUrlRewriteMapTest
 */
class UrlRewriteMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var DataMapPoolInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $dataMapPoolMock;

    /** @var UrlRewriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewriteFactoryMock;

    /** @var UrlRewrite|\PHPUnit_Framework_MockObject_MockObject */
    private $urlRewritePrototypeMock;

    /** @var UrlFinderInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $urlFinderMock;

    /** @var UrlRewriteMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->dataMapPoolMock = $this->getMock(DataMapPoolInterface::class);
        $this->urlFinderMock = $this->getMock(UrlFinderInterface::class);
        $this->urlRewriteFactoryMock = $this->getMock(UrlRewriteFactory::class, ['create'], [], '', false);
        $this->urlRewritePrototypeMock = new UrlRewrite();

        $this->urlRewriteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->urlRewritePrototypeMock);

        $this->model = (new ObjectManager($this))->getObject(
            UrlRewriteMap::class,
            [
                'dataMapPool' => $this->dataMapPoolMock,
                'urlFinder' => $this->urlFinderMock,
                'urlRewriteFactory' => $this->urlRewriteFactoryMock
            ]
        );
    }

    /**
     * test getByIdentifiers using findAllByData
     */
    public function testGetByIdentifiersFallback()
    {
        $expected = [1, 2, 3];
        $this->dataMapPoolMock->expects($this->never())
            ->method('getDataMap');

        $this->urlFinderMock->expects($this->exactly(7))
            ->method('findAllByData')
            ->willReturn($expected);

        $this->assertEquals($expected, $this->model->getByIdentifiers(1, 1, UrlRewriteMap::ENTITY_TYPE_CATEGORY));
        $this->assertEquals($expected, $this->model->getByIdentifiers(1, 1, UrlRewriteMap::ENTITY_TYPE_PRODUCT));
        $this->assertEquals($expected, $this->model->getByIdentifiers('a', 1, UrlRewriteMap::ENTITY_TYPE_PRODUCT), 1);
        $this->assertEquals($expected, $this->model->getByIdentifiers('a', 'a', UrlRewriteMap::ENTITY_TYPE_PRODUCT), 1);
        $this->assertEquals($expected, $this->model->getByIdentifiers(1, 'a', UrlRewriteMap::ENTITY_TYPE_PRODUCT), 1);
        $this->assertEquals($expected, $this->model->getByIdentifiers(1, 1, 'cms', 1));
        $this->assertEquals($expected, $this->model->getByIdentifiers(1, 1, 'cms'));
    }

    /**
     * test getByIdentifiers Product URL rewrites
     */
    public function testGetByIdentifiersProduct()
    {
        $data =[
            [
                'url_rewrite_id' => '1',
                'entity_type' => 'product',
                'entity_id' => '3',
                'request_path' => 'request_path',
                'target_path' => 'target_path',
                'redirect_type' => 'redirect_type',
                'store_id' => '4',
                'description' => 'description',
                'is_autogenerated' => '1',
                'metadata' => '{}'
            ]
        ];

        $dataProductMapMock = $this->getMock(DataProductUrlRewriteMap::class, [], [], '', false);
        $this->dataMapPoolMock->expects($this->once())
            ->method('getDataMap')
            ->with(DataProductUrlRewriteMap::class, 1)
            ->willReturn($dataProductMapMock);

        $this->urlFinderMock->expects($this->never())
            ->method('findAllByData')
            ->willReturn([]);

        $dataProductMapMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $urlRewriteResultArray = $this->model->getByIdentifiers(1, 1, UrlRewriteMap::ENTITY_TYPE_PRODUCT, 1);
        $this->assertEquals($data[0], $urlRewriteResultArray[0]->toArray());
    }

    /**
     * test getByIdentifiers Category URL rewrites
     */
    public function testGetByIdentifiersCategory()
    {
        $data =[
            [
                'url_rewrite_id' => '1',
                'entity_type' => 'category',
                'entity_id' => '3',
                'request_path' => 'request_path',
                'target_path' => 'target_path',
                'redirect_type' => 'redirect_type',
                'store_id' => '4',
                'description' => 'description',
                'is_autogenerated' => '1',
                'metadata' => '{}'
            ]
        ];

        $dataCategoryMapMock = $this->getMock(DataCategoryUrlRewriteMap::class, [], [], '', false);
        $this->dataMapPoolMock->expects($this->once())
            ->method('getDataMap')
            ->with(DataCategoryUrlRewriteMap::class, 1)
            ->willReturn($dataCategoryMapMock);

        $this->urlFinderMock->expects($this->never())
            ->method('findAllByData')
            ->willReturn([]);

        $dataCategoryMapMock->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $urlRewriteResultArray = $this->model->getByIdentifiers(1, 1, UrlRewriteMap::ENTITY_TYPE_CATEGORY, 1);
        $this->assertEquals($data[0], $urlRewriteResultArray[0]->toArray());
    }
}
